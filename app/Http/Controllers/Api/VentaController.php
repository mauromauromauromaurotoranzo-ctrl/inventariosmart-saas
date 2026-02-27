<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Venta\VentaRepositoryInterface;
use App\Domain\Producto\ProductoRepositoryInterface;
use App\Domain\Caja\CajaRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class VentaController extends Controller
{
    public function __construct(
        private readonly VentaRepositoryInterface $ventaRepository,
        private readonly ProductoRepositoryInterface $productoRepository,
        private readonly CajaRepositoryInterface $cajaRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->ventaRepository->query()
            ->with(['cliente', 'items.producto']);

        // Filtros
        if ($request->has('cliente_id')) {
            $query->where('cliente_id', $request->get('cliente_id'));
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->get('estado'));
        }

        if ($request->has('metodo_pago')) {
            $query->where('metodo_pago', $request->get('metodo_pago'));
        }

        if ($request->has('desde')) {
            $query->whereDate('created_at', '>=', $request->get('desde'));
        }

        if ($request->has('hasta')) {
            $query->whereDate('created_at', '<=', $request->get('hasta'));
        }

        if ($request->has('hoy') && $request->get('hoy')) {
            $query->whereDate('created_at', today());
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación o límite
        if ($request->has('limit')) {
            $ventas = $query->limit($request->get('limit'))->get();
            return response()->json([
                'data' => $ventas->map(fn($v) => $this->formatVenta($v)),
            ]);
        }

        $perPage = $request->get('per_page', 15);
        $ventas = $query->paginate($perPage);

        return response()->json([
            'data' => $ventas->map(fn($v) => $this->formatVenta($v)),
            'meta' => [
                'current_page' => $ventas->currentPage(),
                'last_page' => $ventas->lastPage(),
                'per_page' => $ventas->perPage(),
                'total' => $ventas->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cliente_id' => 'nullable|exists:clientes,id',
                'items' => 'required|array|min:1',
                'items.*.producto_id' => 'required|exists:productos,id',
                'items.*.cantidad' => 'required|integer|min:1',
                'items.*.precio_unitario' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
                'descuento_monto' => 'nullable|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia,cheque,cuenta_corriente',
                'pago_recibido' => 'nullable|numeric|min:0',
                'cambio' => 'nullable|numeric|min:0',
                'notas' => 'nullable|string',
            ]);

            // Verificar caja abierta
            $cajaAbierta = $this->cajaRepository->findOpenByUser(auth()->id());
            if (!$cajaAbierta && in_array($validated['metodo_pago'], ['efectivo', 'tarjeta', 'transferencia'])) {
                return response()->json([
                    'message' => 'No hay una caja abierta para este usuario',
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Crear la venta
                $venta = $this->ventaRepository->create([
                    'tenant_id' => tenant()->id,
                    'cliente_id' => $validated['cliente_id'] ?? null,
                    'caja_id' => $cajaAbierta?->id,
                    'usuario_id' => auth()->id(),
                    'subtotal' => $validated['subtotal'],
                    'descuento_porcentaje' => $validated['descuento_porcentaje'] ?? 0,
                    'descuento_monto' => $validated['descuento_monto'] ?? 0,
                    'total' => $validated['total'],
                    'metodo_pago' => $validated['metodo_pago'],
                    'pago_recibido' => $validated['pago_recibido'] ?? $validated['total'],
                    'cambio' => $validated['cambio'] ?? 0,
                    'estado' => 'completada',
                    'notas' => $validated['notas'] ?? null,
                ]);

                // Crear items y actualizar stock
                foreach ($validated['items'] as $item) {
                    $producto = $this->productoRepository->findById($item['producto_id']);
                    
                    if (!$producto || $producto->stock < $item['cantidad']) {
                        DB::rollBack();
                        return response()->json([
                            'message' => "Stock insuficiente para {$producto?->nombre}",
                        ], 422);
                    }

                    // Crear item de venta
                    $venta->items()->create([
                        'producto_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                    ]);

                    // Actualizar stock
                    $this->productoRepository->update($item['producto_id'], [
                        'stock' => new \App\Domain\Producto\ValueObjects\StockProducto($producto->stock - $item['cantidad']),
                    ]);
                }

                // Si es cuenta corriente, actualizar saldo del cliente
                if ($validated['metodo_pago'] === 'cuenta_corriente' && $validated['cliente_id']) {
                    $cliente = \App\Models\Cliente::find($validated['cliente_id']);
                    if ($cliente && $cliente->cuentaCorriente) {
                        $cliente->cuentaCorriente->increment('saldo', $validated['total']);
                    }
                }

                DB::commit();

                $venta->load(['cliente', 'items.producto']);

                return response()->json([
                    'message' => 'Venta realizada exitosamente',
                    'data' => $this->formatVenta($venta),
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $venta = $this->ventaRepository->findById($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        $venta->load(['cliente', 'items.producto', 'usuario']);

        return response()->json([
            'data' => $this->formatVenta($venta, true),
        ]);
    }

    public function cancelar(int $id): JsonResponse
    {
        $venta = $this->ventaRepository->findById($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        if ($venta->estado === 'cancelada') {
            return response()->json(['message' => 'La venta ya está cancelada'], 422);
        }

        DB::beginTransaction();

        try {
            // Restaurar stock
            foreach ($venta->items as $item) {
                $producto = $this->productoRepository->findById($item->producto_id);
                if ($producto) {
                    $this->productoRepository->update($item->producto_id, [
                        'stock' => new \App\Domain\Producto\ValueObjects\StockProducto($producto->stock + $item->cantidad),
                    ]);
                }
            }

            // Si era cuenta corriente, revertir saldo
            if ($venta->metodo_pago === 'cuenta_corriente' && $venta->cliente_id) {
                $cliente = \App\Models\Cliente::find($venta->cliente_id);
                if ($cliente && $cliente->cuentaCorriente) {
                    $cliente->cuentaCorriente->decrement('saldo', $venta->total);
                }
            }

            // Cancelar venta
            $this->ventaRepository->update($id, ['estado' => 'cancelada']);

            DB::commit();

            return response()->json([
                'message' => 'Venta cancelada exitosamente',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function stats(): JsonResponse
    {
        $hoy = today();
        $ayer = today()->subDay();

        $stats = [
            'ventas_hoy' => $this->ventaRepository->query()
                ->whereDate('created_at', $hoy)
                ->where('estado', 'completada')
                ->count(),
            'ingresos_hoy' => $this->ventaRepository->query()
                ->whereDate('created_at', $hoy)
                ->where('estado', 'completada')
                ->sum('total'),
            'ventas_ayer' => $this->ventaRepository->query()
                ->whereDate('created_at', $ayer)
                ->where('estado', 'completada')
                ->count(),
            'ingresos_ayer' => $this->ventaRepository->query()
                ->whereDate('created_at', $ayer)
                ->where('estado', 'completada')
                ->sum('total'),
            'ventas_semana' => $this->ventaRepository->query()
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('estado', 'completada')
                ->count(),
            'ventas_mes' => $this->ventaRepository->query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('estado', 'completada')
                ->count(),
        ];

        // Calcular cambios porcentuales
        $stats['cambio_ventas'] = $stats['ventas_ayer'] > 0 
            ? round((($stats['ventas_hoy'] - $stats['ventas_ayer']) / $stats['ventas_ayer']) * 100, 1)
            : 0;
        
        $stats['cambio_ingresos'] = $stats['ingresos_ayer'] > 0
            ? round((($stats['ingresos_hoy'] - $stats['ingresos_ayer']) / $stats['ingresos_ayer']) * 100, 1)
            : 0;

        return response()->json($stats);
    }

    public function topProductos(): JsonResponse
    {
        $topProductos = DB::table('venta_items')
            ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
            ->join('ventas', 'venta_items.venta_id', '=', 'ventas.id')
            ->where('ventas.tenant_id', tenant()->id)
            ->where('ventas.estado', 'completada')
            ->whereBetween('ventas.created_at', [now()->subDays(30), now()])
            ->select(
                'productos.id',
                'productos.nombre',
                DB::raw('SUM(venta_items.cantidad) as cantidad_vendida')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('cantidad_vendida')
            ->limit(5)
            ->get();

        $maxCantidad = $topProductos->max('cantidad_vendida') ?: 1;

        return response()->json(
            $topProductos->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'cantidad' => (int) $p->cantidad_vendida,
                'porcentaje' => round(($p->cantidad_vendida / $maxCantidad) * 100, 1),
            ])
        );
    }

    private function formatVenta($venta, bool $detailed = false): array
    {
        $data = [
            'id' => $venta->id,
            'cliente_id' => $venta->cliente_id,
            'cliente' => $venta->cliente ? [
                'id' => $venta->cliente->id,
                'nombre' => $venta->cliente->nombre,
            ] : null,
            'subtotal' => (float) $venta->subtotal,
