<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Domain\Cliente\ClienteRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class ClienteController extends Controller
{
    public function __construct(
        private readonly ClienteRepositoryInterface $clienteRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = $this->clienteRepository->query();

        // Filtros
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('telefono', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->get('tipo'));
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->get('activo'));
        }

        if ($request->has('con_deuda') && $request->get('con_deuda')) {
            $query->whereHas('cuentaCorriente', function ($q) {
                $q->where('saldo', '>', 0);
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'nombre');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 15);
        $clientes = $query->paginate($perPage);

        return response()->json([
            'data' => $clientes->map(fn($c) => $this->formatCliente($c)),
            'meta' => [
                'current_page' => $clientes->currentPage(),
                'last_page' => $clientes->lastPage(),
                'per_page' => $clientes->perPage(),
                'total' => $clientes->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:clientes,email',
                'telefono' => 'nullable|string|max:50',
                'direccion' => 'nullable|string|max:500',
                'tipo' => 'required|in:consumidor_final,responsable_inscripto,monotributista,exento',
                'cuit_cuil' => 'nullable|string|max:20',
                'notas' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $cliente = $this->clienteRepository->create([
                'tenant_id' => tenant()->id,
                'nombre' => $validated['nombre'],
                'email' => $validated['email'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'tipo' => $validated['tipo'],
                'cuit_cuil' => $validated['cuit_cuil'] ?? null,
                'notas' => $validated['notas'] ?? null,
                'activo' => $validated['activo'] ?? true,
            ]);

            return response()->json([
                'message' => 'Cliente creado exitosamente',
                'data' => $this->formatCliente($cliente),
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
        $cliente = $this->clienteRepository->findById($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json([
            'data' => $this->formatCliente($cliente, true),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $cliente = $this->clienteRepository->findById($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        try {
            $validated = $request->validate([
                'nombre' => 'sometimes|string|max:255',
                'email' => 'nullable|email|max:255|unique:clientes,email,' . $id,
                'telefono' => 'nullable|string|max:50',
                'direccion' => 'nullable|string|max:500',
                'tipo' => 'sometimes|in:consumidor_final,responsable_inscripto,monotributista,exento',
                'cuit_cuil' => 'nullable|string|max:20',
                'notas' => 'nullable|string',
                'activo' => 'boolean',
            ]);

            $updateData = array_filter($validated, fn($v) => $v !== null);

            $this->clienteRepository->update($id, $updateData);

            $clienteActualizado = $this->clienteRepository->findById($id);

            return response()->json([
                'message' => 'Cliente actualizado exitosamente',
                'data' => $this->formatCliente($clienteActualizado),
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
        $cliente = $this->clienteRepository->findById($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        // Verificar si tiene ventas asociadas
        if ($cliente->ventas()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el cliente porque tiene ventas asociadas',
            ], 422);
        }

        $this->clienteRepository->delete($id);

        return response()->json([
            'message' => 'Cliente eliminado exitosamente',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => $this->clienteRepository->count(),
            'activos' => $this->clienteRepository->count(['activo' => true]),
            'con_deuda' => $this->clienteRepository->query()
                ->whereHas('cuentaCorriente', function ($q) {
                    $q->where('saldo', '>', 0);
                })
                ->count(),
            'nuevos_mes' => $this->clienteRepository->query()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }

    private function formatCliente($cliente, bool $includeRelations = false): array
    {
        $data = [
            'id' => $cliente->id,
            'nombre' => $cliente->nombre,
            'email' => $cliente->email,
            'telefono' => $cliente->telefono,
            'direccion' => $cliente->direccion,
            'tipo' => $cliente->tipo,
            'cuit_cuil' => $cliente->cuit_cuil,
            'notas' => $cliente->notas,
            'activo' => $cliente->activo,
            'created_at' => $cliente->created_at?->toISOString(),
            'updated_at' => $cliente->updated_at?->toISOString(),
        ];

        if ($includeRelations) {
            $data['saldo_cc'] = $cliente->cuentaCorriente?->saldo ?? 0;
            $data['ventas_count'] = $cliente->ventas()->count();
            $data['total_comprado'] = $cliente->ventas()->sum('total');
        }

        return $data;
    }
}
