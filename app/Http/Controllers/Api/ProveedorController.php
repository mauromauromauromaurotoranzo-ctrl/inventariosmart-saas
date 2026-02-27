<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Proveedor\ProveedorRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ProveedorController extends Controller
{
    public function __construct(
        private readonly ProveedorRepositoryInterface $proveedorRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->proveedorRepository->query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('telefono', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->get('activo'));
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $proveedores = $query->paginate($perPage);

        return response()->json([
            'data' => $proveedores->map(fn($p) => $this->formatProveedor($p)),
            'meta' => [
                'current_page' => $proveedores->currentPage(),
                'last_page' => $proveedores->lastPage(),
                'per_page' => $proveedores->perPage(),
                'total' => $proveedores->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:proveedores,email',
                'telefono' => 'nullable|string|max:50',
                'direccion' => 'nullable|string|max:500',
                'cuit_cuil' => 'nullable|string|max:20',
                'condicion_iva' => 'nullable|in:responsable_inscripto,monotributista,exento,consumidor_final',
                'notas' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $proveedor = $this->proveedorRepository->create([
                'tenant_id' => tenant()->id,
                'nombre' => $validated['nombre'],
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'cuit_cuil' => $validated['cuit_cuil'] ?? null,
                'condicion_iva' => $validated['condicion_iva'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return response()->json([
                'message' => 'Proveedor creado exitosamente',
                'data' => $this->formatProveedor($proveedor),
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
        $proveedor = $this->proveedorRepository->findById($id);

        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }

        return response()->json([
            'data' => $this->formatProveedor($proveedor, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $proveedor = $this->proveedorRepository->findById($id);

        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'email' => 'nullable|email|max:255|unique:proveedores,email,' . $id,
                'telefono' => 'nullable|string|max:50',
                'direccion' => 'nullable|string|max:500',
                'cuit_cuil' => 'nullable|string|max:20',
                'condicion_iva' => 'nullable|in:responsable_inscripto,monotributista,exento,consumidor_final',
                'notas' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $updateData = array_filter($validated, fn($v) => $v !== null);

            $this->proveedorRepository->update($id, $updateData);

            $proveedorActualizado = $this->proveedorRepository->findById($id);

            return response()->json([
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $this->formatProveedor($proveedorActualizado),
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
        $proveedor = $this->proveedorRepository->findById($id);

        if (!$proveedor) {
            return response()->json(['message' => 'Proveedor no encontrado'], 404);
        }

        // Verificar si tiene compras asociadas
        if ($proveedor->compras()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el proveedor porque tiene compras asociadas',
            ], 422);
        }

        $this->proveedorRepository->delete($id);

        return response()->json([
            'message' => 'Proveedor eliminado exitosamente',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => $this->proveedorRepository->count(),
            'activos' => $this->proveedorRepository->count(['activo' => true]),
            'con_deuda' => $this->proveedorRepository->query()
                ->whereHas('cuentaCorriente', function ($q) {
                    $q->where('saldo', '>', 0);
                })
                ->count(),
        ];

        return response()->json($stats);
    }

    private function formatProveedor($proveedor, bool $includeRelations = false): array
    {
        $data = [
            'id' => $proveedor->id,
            'nombre' => $proveedor->nombre,
            'email' => $proveedor->email,
            'telefono' => $proveedor->telefono,
            'direccion' => $proveedor->direccion,
            'cuit_cuil' => $proveedor->cuit_cuil,
            'condicion_iva' => $proveedor->condicion_iva,
            'notas' => $proveedor->notas,
            'activo' => $proveedor->activo,
            'created_at' => $proveedor->created_at?->toISOString(),
            'updated_at' => $proveedor->updated_at?->toISOString(),
        ];

        if ($includeRelations) {
            $data['saldo_cc'] = $proveedor->cuentaCorriente?->saldo ?? 0;
            $data['compras_count'] = $proveedor->compras()->count();
        }

        return $data;
    }
}
