@component('mail::message')
# 🎉 ¡Bienvenido a InventarioSmart, {{ $tenant->name }}!

Tu sistema de inventario está listo. Aquí están tus credenciales de acceso:

---

## 🔐 Tus Datos de Acceso

**URL de tu sistema:**  
<a href="{{ $url }}">{{ $url }}</a>

**Email:** {{ $tenant->email }}  
**Contraseña temporal:** `{{ $password }}`

@component('mail::button', ['url' => $loginUrl, 'color' => 'primary'])
Iniciar Sesión Ahora
@endcomponent

> ⚠️ **Importante:** Por seguridad, cambia tu contraseña en tu primer inicio de sesión.

---

## 🚀 Próximos Pasos (Setup en 5 minutos)

@foreach($steps as $step)
### {{ $step['icon'] }} {{ $step['title'] }}
{{ $step['desc'] }}

@endforeach

@component('mail::button', ['url' => $onboardingUrl, 'color' => 'success'])
Comenzar Setup Guiado
@endcomponent

---

## 📅 Tu Período de Prueba

Tienes **{{ $trialDays }} días** para probar todas las funciones sin compromiso.

- ✅ Sin tarjeta de crédito requerida
- ✅ Soporte incluido
- ✅ Cancelas cuando quieras

¿Necesitas ayuda? Responde a este email o escríbenos a soporte@inventariosmart.app

---

¡Gracias por elegirnos!  
**El equipo de InventarioSmart** 🚀

<small>Si no creaste esta cuenta, ignora este email.</small>
@endcomponent
