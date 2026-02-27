<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Categoria\CategoriaRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class CategoriaController extends Controller
{
    public function __construct(
        private readonly CategoriaRepositoryInterface $categoriaRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->categoriaRepository->query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('nombre', 'ilike', "%{$search}%");
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->get('activo'));
        }

        // Ordenamiento
        $query->orderBy('nombre', 'asc');

        // Paginación o lista completa
        if ($request->has('all') && $request->get('all')) {
            $categorias = $query->get();
            return response()->json([
                'data' => $categorias->map(fn($c) => $this->formatCategoria($c)),
            ]);
        }

        $perPage = $request->get('per_page', 15);
        $categorias = $query->paginate($perPage);

        return response()->json([
            'data' => $categorias->map(fn($c) => $this->formatCategoria($c)),
            'meta' => [
                'current_page' => $categorias->currentPage(),
                'last_page' => $categorias->lastPage(),
                'per_page' => $categorias->perPage(),
                'total' => $categorias->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:categorias,nombre',
                'descripcion' => 'nullable|string',
                'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                'activo' => 'boolean',
            ]);

            $categoria = $this->categoriaRepository->create([
                'tenant_id' => tenant()->id,
                'nombre' => $validated['nombre'],
                'descripcion' => $validated['descripcion'] ?? null,
                'color' => $validated['color'] ?? '#3B82F6',
                'activo' => $validated['activo'] ?? true,
            ]);

            return response()->json([
                'message' => 'Categoría creada exitosamente',
                'data' => $this->formatCategoria($categoria),
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
        $categoria = $this->categoriaRepository->findById($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        return response()->json([
            'data' => $this->formatCategoria($categoria, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $categoria = $this->categoriaRepository->findById($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255|unique:categorias,nombre,' . $id,
                'descripcion' => 'nullable|string',
                'color' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
                'activo' => 'boolean',
            ]);

            $updateData = [];

            if (isset($validated['nombre'])) {
                $updateData['nombre'] = $validated['nombre'];
            }
            if (array_key_exists('descripcion', $validated)) {
                $updateData['descripcion'] = $validated['descripcion'];
            }
            if (array_key_exists('color', $validated)) {
                $updateData['color'] = $validated['color'];
            }
            if (isset($validated['activo'])) {
                $updateData['activo'] = $validated['activo'];
            }

            $this->categoriaRepository->update($id, $updateData);

            $categoriaActualizada = $this->categoriaRepository->findById($id);

            return response()->json([
                'message' => 'Categoría actualizada exitosamente',
                'data' => $this->formatCategoria($categoriaActualizada),
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
        $categoria = $this->categoriaRepository->findById($id);

        if (!$categoria) {
            return response()->json(['message' => 'Categoría no encontrada'], 404);
        }

        // Verificar si tiene productos asociados
        if ($categoria->productos()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar la categoría porque tiene productos asociados',
            ], 422);
        }

        $this->categoriaRepository->delete($id);

        return response()->json([
            'message' => 'Categoría eliminada exitosamente',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => $this->categoriaRepository->count(),
            'con_productos' => $this->categoriaRepository->query()
                ->has('productos')
                ->count(),
            'sin_productos' => $this->categoriaRepository->query()
                ->doesntHave('productos')
                ->count(),
        ];

        return response()->json($stats);
    }

    private function formatCategoria($categoria, bool $includeProductos = false): array
    {
        $data = [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre,
            'descripcion' => $categoria->descripcion,
            'color' => $categoria->color,
            'activo' => $categoria->activo,
            'productos_count' => $categoria->productos()->count(),
            'created_at' => $categoria->created_at?->toISOString(),
            'updated_at' => $categoria->updated_at?->toISOString(),
        ];

        if ($includeProductos) {
            $data['productos'] = $categoria->productos()
                ->select('id', 'nombre', 'codigo', 'stock', 'precio_venta')
                ->get();
        }

        return $data;
    }
}
