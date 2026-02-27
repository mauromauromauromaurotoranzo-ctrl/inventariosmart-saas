<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Producto\ProductoRepositoryInterface;
use App\Domain\Producto\ValueObjects\CodigoProducto;
use App\Domain\Producto\ValueObjects\PrecioProducto;
use App\Domain\Producto\ValueObjects\StockProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoRepositoryInterface $productoRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->productoRepository->query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('codigo', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->get('categoria_id'));
        }

        if ($request->has('disponibles') && $request->get('disponibles')) {
            $query->where('activo', true)
                  ->where('stock', '>', 0);
        }

        if ($request->has('stock_bajo') && $request->get('stock_bajo')) {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        if ($request->has('sin_stock') && $request->get('sin_stock')) {
            $query->where('stock', 0);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $productos = $query->paginate($perPage);

        return response()->json([
            'data' => $productos->map(fn($p) => $this->formatProducto($p)),
            'meta' => [
                'current_page' => $productos->currentPage(),
                'last_page' => $productos->lastPage(),
                'per_page' => $productos->perPage(),
                'total' => $productos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:50|unique:productos,codigo',
                'descripcion' => 'nullable|string',
                'precio_costo' => 'required|numeric|min:0',
                'precio_venta' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'stock_minimo' => 'required|integer|min:0',
                'categoria_id' => 'required|exists:categorias,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'ubicacion' => 'nullable|string|max:100',
                'activo' => 'boolean',
            ]);

            $producto = $this->productoRepository->create([
                'tenant_id' => tenant()->id,
                'nombre' => $validated['nombre'],
                'codigo' => new CodigoProducto($validated['codigo']),
                'descripcion' => $validated['descripcion'] ?? null,
                'precio_costo' => new PrecioProducto((float) $validated['precio_costo']),
                'precio_venta' => new PrecioProducto((float) $validated['precio_venta']),
                'stock' => new StockProducto((int) $validated['stock']),
                'stock_minimo' => (int) $validated['stock_minimo'],
                'categoria_id' => $validated['categoria_id'],
                'proveedor_id' => $validated['proveedor_id'] ?? null,
                'ubicacion' => $validated['ubicacion'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return response()->json([
                'message' => 'Producto creado exitosamente',
                'data' => $this->formatProducto($producto),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        $producto = $this->productoRepository->findById($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        return response()->json([
            'data' => $this->formatProducto($producto),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $producto = $this->productoRepository->findById($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'codigo' => 'sometimes|string|max:50|unique:productos,codigo,' . $id,
                'descripcion' => 'nullable|string',
                'precio_costo' => 'sometimes|numeric|min:0',
                'precio_venta' => 'sometimes|numeric|min:0',
                'stock' => 'sometimes|integer|min:0',
                'stock_minimo' => 'sometimes|integer|min:0',
                'categoria_id' => 'sometimes|exists:categorias,id',
                'proveedor_id' => 'nullable|exists:proveedores,id',
                'ubicacion' => 'nullable|string|max:100',
                'activo' => 'boolean',
            ]);

            $updateData = [];

            if (isset($validated['nombre'])) {
                $updateData['nombre'] = $validated['nombre'];
            }
            if (isset($validated['codigo'])) {
                $updateData['codigo'] = new CodigoProducto($validated['codigo']);
            }
            if (isset($validated['descripcion'])) {
                $updateData['descripcion'] = $validated['descripcion'];
            }
            if (isset($validated['precio_costo'])) {
                $updateData['precio_costo'] = new PrecioProducto((float) $validated['precio_costo']);
            }
            if (isset($validated['precio_venta'])) {
                $updateData['precio_venta'] = new PrecioProducto((float) $validated['precio_venta']);
            }
            if (isset($validated['stock'])) {
                $updateData['stock'] = new StockProducto((int) $validated['stock']);
            }
            if (isset($validated['stock_minimo'])) {
                $updateData['stock_minimo'] = $validated['stock_minimo'];
            }
            if (isset($validated['categoria_id'])) {
                $updateData['categoria_id'] = $validated['categoria_id'];
            }
            if (array_key_exists('proveedor_id', $validated)) {
                $updateData['proveedor_id'] = $validated['proveedor_id'];
            }
            if (isset($validated['ubicacion'])) {
                $updateData['ubicacion'] = $validated['ubicacion'];
            }
            if (isset($validated['activo'])) {
                $updateData['activo'] = $validated['activo'];
            }

            $this->productoRepository->update($id, $updateData);

            $productoActualizado = $this->productoRepository->findById($id);

            return response()->json([
                'message' => 'Producto actualizado exitosamente',
                'data' => $this->formatProducto($productoActualizado),
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $producto = $this->productoRepository->findById($id);

        if (!$producto) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $this->productoRepository->delete($id);

        return response()->json([
            'message' => 'Producto eliminado exitosamente',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => $this->productoRepository->count(),
            'activos' => $this->productoRepository->count(['activo' => true]),
            'stock_bajo' => $this->productoRepository->query()
                ->whereColumn('stock', '<=', 'stock_minimo')
                ->count(),
            'sin_stock' => $this->productoRepository->query()
                ->where('stock', 0)
                ->count(),
            'valor_total' => $this->productoRepository->query()
                ->sumRaw('stock * precio_costo'),
        ];

        return response()->json($stats);
    }

    private function formatProducto($producto): array
    {
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'codigo' => (string) $producto->codigo,
            'descripcion' => $producto->descripcion,
            'precio_costo' => (float) $producto->precio_costo,
            'precio_venta' => (float) $producto->precio_venta,
            'stock' => (int) $producto->stock,
            'stock_minimo' => $producto->stock_minimo,
            'categoria_id' => $producto->categoria_id,
            'categoria' => $producto->categoria ? [
                'id' => $producto->categoria->id,
                'nombre' => $producto->categoria->nombre,
            ] : null,
            'proveedor_id' => $producto->proveedor_id,
            'proveedor' => $producto->proveedor ? [
                'id' => $producto->proveedor->id,
                'nombre' => $producto->proveedor->nombre,
            ] : null,
            'ubicacion' => $producto->ubicacion,
            'activo' => $producto->activo,
            'created_at' => $producto->created_at?->toISOString(),
            'updated_at' => $producto->updated_at?->toISOString(),
        ];
    }
}
