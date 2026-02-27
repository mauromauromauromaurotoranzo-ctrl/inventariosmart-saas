<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Producto\ProductoRepositoryInterface;
use App\Domain\Venta\VentaRepositoryInterface;
use App\Domain\Cliente\ClienteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly ProductoRepositoryInterface $productoRepository,
        private readonly VentaRepositoryInterface $ventaRepository,
        private readonly ClienteRepositoryInterface $clienteRepository
    ) {}

    public function stats(): JsonResponse
    {
        $hoy = today();
        $ayer = today()->subDay();
        $inicioMes = now()->startOfMonth();

        // Stats de ventas
        $ventasHoy = $this->ventaRepository->query()
            ->whereDate('created_at', $hoy)
            ->where('estado', 'completada')
            ->count();
        
        $ingresosHoy = $this->ventaRepository->query()
            ->whereDate('created_at', $hoy)
            ->where('estado', 'completada')
            ->sum('total') ?? 0;

        $ventasAyer = $this->ventaRepository->query()
            ->whereDate('created_at', $ayer)
            ->where('estado', 'completada')
            ->count();
        
        $ingresosAyer = $this->ventaRepository->query()
            ->whereDate('created_at', $ayer)
            ->where('estado', 'completada')
            ->sum('total') ?? 0;

        // Calcular cambios porcentuales
        $cambioVentas = $ventasAyer > 0 
            ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1)
            : ($ventasHoy > 0 ? 100 : 0);
        
        $cambioIngresos = $ingresosAyer > 0
            ? round((($ingresosHoy - $ingresosAyer) / $ingresosAyer) * 100, 1)
            : ($ingresosHoy > 0 ? 100 : 0);

        // Stock bajo
        $stockBajo = $this->productoRepository->query()
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->where('activo', true)
            ->count();

        // Nuevos clientes este mes
        $nuevosClientes = $this->clienteRepository->query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return response()->json([
            'ventasHoy' => $ventasHoy,
            'ingresosHoy' => (float) $ingresosHoy,
            'cambioVentas' => $cambioVentas,
            'cambioIngresos' => $cambioIngresos,
            'stockBajo' => $stockBajo,
            'nuevosClientes' => $nuevosClientes,
        ]);
    }

    public function ventasChart(): JsonResponse
    {
        $dias = collect(range(6, 0))->map(function ($days) {
            return today()->subDays($days);
        });

        $data = $dias->map(function ($fecha) {
            $total = $this->ventaRepository->query()
                ->whereDate('created_at', $fecha)
                ->where('estado', 'completada')
                ->sum('total') ?? 0;

            return [
                'fecha' => $fecha->format('Y-m-d'),
                'dia' => $fecha->translatedFormat('D'),
                'total' => (float) $total,
            ];
        });

        return response()->json($data);
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

    public function alertas(): JsonResponse
    {
        $alertas = [];

        // Alerta de stock bajo
        $productosStockBajo = $this->productoRepository->query()
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->where('activo', true)
            ->count();

        if ($productosStockBajo > 0) {
            $alertas[] = [
                'id' => 'stock-bajo',
                'tipo' => 'warning',
                'titulo' => 'Stock bajo',
                'mensaje' => "{$productosStockBajo} productos están por debajo del stock mínimo",
                'link' => '/productos?stock=bajo',
            ];
        }

        // Alerta de productos sin stock
        $productosSinStock = $this->productoRepository->query()
            ->where('stock', 0)
            ->where('activo', true)
            ->count();

        if ($productosSinStock > 0) {
            $alertas[] = [
                'id' => 'sin-stock',
                'tipo' => 'error',
                'titulo' => 'Sin stock',
                'mensaje' => "{$productosSinStock} productos se quedaron sin stock",
                'link' => '/productos?stock=agotado',
            ];
        }

        return response()->json($alertas);
    }
}
