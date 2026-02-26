<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcome extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Tenant $tenant;
    public string $password;
    public array $onboardingSteps;

    /**
     * Create a new message instance.
     */
    public function __construct(Tenant $tenant, string $password)
    {
        $this->tenant = $tenant;
        $this->password = $password;
        $this->onboardingSteps = $this->getOnboardingSteps($tenant->rubro);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🎉 ¡Bienvenido a InventarioSmart! Tus credenciales de acceso",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-welcome',
            with: [
                'tenant' => $this->tenant,
                'password' => $this->password,
                'url' => $this->tenant->getUrl(),
                'loginUrl' => $this->tenant->getUrl() . '/login',
                'onboardingUrl' => $this->tenant->getUrl() . '/onboarding',
                'steps' => $this->onboardingSteps,
                'trialDays' => now()->diffInDays($this->tenant->trial_ends_at),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Obtener pasos de onboarding según rubro
     */
    private function getOnboardingSteps(string $rubro): array
    {
        $steps = [
            'retail' => [
                ['icon' => '📦', 'title' => 'Carga tus productos', 'desc' => 'Importa desde Excel o escanea códigos de barras'],
                ['icon' => '🏪', 'title' => 'Configura tu tienda', 'desc' => 'Define horarios, impuestos y preferencias'],
                ['icon' => '💰', 'title' => 'Haz tu primera venta', 'desc' => 'Prueba el sistema con una venta de prueba'],
            ],
            'farmacia' => [
                ['icon' => '💊', 'title' => 'Registra medicamentos', 'desc' => 'Con lotes, vencimientos y trazabilidad'],
                ['icon' => '🏥', 'title' => 'Configura obras sociales', 'desc' => 'Agrega las obras sociales que aceptas'],
                ['icon' => '📋', 'title' => 'Prueba una receta', 'desc' => 'Simula una venta con obra social'],
            ],
            'restaurante' => [
                ['icon' => '🍽️', 'title' => 'Crea tu menú', 'desc' => 'Platos, ingredientes y costos automáticos'],
                ['icon' => '📦', 'title' => 'Registra insumos', 'desc' => 'Todo lo que usas para cocinar'],
                ['icon' => '👨‍🍳', 'title' => 'Configura áreas', 'desc' => 'Bar, cocina, parrilla, etc.'],
            ],
            'ferreteria' => [
                ['icon' => '🔧', 'title' => 'Organiza por categorías', 'desc' => 'Eléctrica, plomería, herramientas...'],
                ['icon' => '🔄', 'title' => 'Define equivalentes', 'desc' => 'Productos intercambiables entre marcas'],
                ['icon' => '💵', 'title' => 'Listas de precios', 'desc' => 'Mayorista, minorista, constructoras'],
            ],
            'moda' => [
                ['icon' => '👗', 'title' => 'Carga prendas', 'desc' => 'Con tallas, colores y temporadas'],
                ['icon' => '📊', 'title' => 'Define temporadas', 'desc' => 'Verano 2024, Invierno 2024...'],
                ['icon' => '🏷️', 'title' => 'Etiquetas de precio', 'desc' => 'Diseña e imprime etiquetas'],
            ],
            'distribuidora' => [
                ['icon' => '📦', 'title' => 'Carga catálogo', 'desc' => 'Todos tus productos con fotos'],
                ['icon' => '👥', 'title' => 'Registra clientes', 'desc' => 'Con sus listas de precios especiales'],
                ['icon' => '🚚', 'title' => 'Configura rutas', 'desc' => 'Optimiza tus entregas'],
            ],
            'manufactura' => [
                ['icon' => '🏭', 'title' => 'Define materia prima', 'desc' => 'Todo lo que compras para producir'],
                ['icon' => '⚙️', 'title' => 'Crea recetas (BOM)', 'desc' => 'Fórmulas de producción'],
                ['icon' => '📋', 'title' => 'Órdenes de trabajo', 'desc' => 'Planifica tu producción'],
            ],
        ];

        return $steps[$rubro] ?? $steps['retail'];
    }
}
