<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - InventarioSmart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .progress-bar { transition: width 0.5s ease; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">InventarioSmart</span>
            </div>
            <div class="text-sm text-gray-500">
                Configuración inicial
            </div>
        </div>
    </header>

    <!-- Progress Bar -->
    <div class="bg-white border-b">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700" id="progress-text">Paso 1 de 4</span>
                <span class="text-sm font-medium text-blue-600" id="progress-percentage">25%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="progress-bar bg-blue-600 h-2 rounded-full" style="width: 25%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 py-8">
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-4 text-gray-600">Cargando...</p>
        </div>

        <!-- Step Content -->
        <div id="step-content" class="hidden fade-in">
            <div class="bg-white rounded-xl shadow-sm border p-8">
                <!-- Icon -->
                <div class="flex justify-center mb-6">
                    <div id="step-icon" class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                        <!-- Icono dinámico -->
                    </div>
                </div>

                <!-- Title & Description -->
                <h1 id="step-title" class="text-2xl font-bold text-center text-gray-900 mb-2">
                    Carga tus Productos
                </h1>
                <p id="step-description" class="text-center text-gray-600 mb-8 max-w-lg mx-auto">
                    Agrega tus productos manualmente o importa desde Excel
                </p>

                <!-- Dynamic Form Area -->
                <div id="step-form" class="space-y-6">
                    <!-- El contenido se carga dinámicamente según el paso -->
                </div>

                <!-- Actions -->
                <div class="flex justify-between mt-8 pt-6 border-t">
                    <button id="btn-skip" class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium">
                        Saltar este paso
                    </button>
                    <div class="flex space-x-3">
                        <button id="btn-back" class="hidden px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                            Atrás
                        </button>
                        <button id="btn-continue" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center space-x-2">
                            <span>Continuar</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion State -->
        <div id="completion-state" class="hidden text-center py-12">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Listo!</h2>
            <p class="text-gray-600 mb-8">Tu sistema está configurado y listo para usar.</p>
            <a href="/dashboard" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Ir al Dashboard
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </main>

    <script>
        // Estado global
        let onboardingData = null;
        let currentStepData = null;

        // Icons mapping
        const icons = {
            package: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`,
            pill: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>`,
            utensils: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>`,
            'folder-tree': `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>`,
            'cash-register': `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`,
            'shopping-cart': `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>`,
            truck: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>`,
            'heart-pulse': `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>`,
            carrot: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
            users: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>`,
            palette: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>`,
            calendar: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>`,
            'map-pin': `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`,
            factory: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>`,
            box: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>`,
            scroll: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>`,
            settings: `<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>`
        };

        // Inicializar
        document.addEventListener('DOMContentLoaded', async () => {
            await initOnboarding();
        });

        async function initOnboarding() {
            try {
                // Intentar obtener estado existente
                const statusRes = await fetch('/api/onboarding/status');
                const statusData = await statusRes.json();

                if (statusData.has_started && statusData.is_completed) {
                    showCompletion();
                    return;
                }

                if (statusData.has_started) {
                    onboardingData = statusData;
                    renderStep();
                } else {
                    // Iniciar nuevo onboarding
                    const startRes = await fetch('/api/onboarding/start', { method: 'POST' });
                    const startData = await startRes.json();
                    
                    if (startData.error) {
                        throw new Error(startData.error);
                    }
                    
                    onboardingData = startData;
                    renderStep();
                }
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('loading-state').innerHTML = `
                    <div class="text-red-600">
                        <p>Error al cargar el onboarding.</p>
                        <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg">
                            Reintentar
                        </button>
                    </div>
                `;
            }
        }

        function renderStep() {
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('step-content').classList.remove('hidden');

            const config = onboardingData.step_config;
            currentStepData = config;

            // Actualizar progreso
            updateProgress();

            // Renderizar icono
            document.getElementById('step-icon').innerHTML = icons[config.icon] || icons.package;

            // Título y descripción
            document.getElementById('step-title').textContent = config.title;
            document.getElementById('step-description').textContent = config.description;

            // Renderizar formulario según el paso
            renderStepForm(onboardingData.current_step, config);

            // Configurar botones
            setupButtons(config);
        }

        function updateProgress() {
            const percentage = onboardingData.progress_percentage;
            document.getElementById('progress-bar').style.width = `${percentage}%`;
            document.getElementById('progress-percentage').textContent = `${percentage}%`;
            
            const totalSteps = 4; // Aproximado
            const currentStepNum = Math.ceil((percentage / 100) * totalSteps) || 1;
            document.getElementById('progress-text').textContent = `Paso ${currentStepNum} de ${totalSteps}`;
        }

        function renderStepForm(step, config) {
            const formContainer = document.getElementById('step-form');
            
            // Contenido por defecto según acciones disponibles
            let content = '';
            
            if (config.actions.includes('create')) {
                content += `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button class="p-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition group">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="font-medium text-gray-900">Crear manualmente</h3>
                            <p class="text-sm text-gray-500 mt-1">Agrega uno por uno</p>
                        </button>
                        
                        ${config.actions.includes('import') ? `
                        <button class="p-6 border-2 border-dashed border-gray-300 rounded-xl hover:border-green-500 hover:bg-green-50 transition group">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3 group-hover:bg-green-200">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <h3 class="font-medium text-gray-900">Importar archivo</h3>
                            <p class="text-sm text-gray-500 mt-1">Excel o CSV</p>
                        </button>
                        ` : ''}
                    </div>
                `;
            }
            
            if (config.actions.includes('configure')) {
                content += `
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Moneda principal</label>
                            <select class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="ARS">Peso Argentino (ARS)</option>
                                <option value="USD">Dólar Estadounidense (USD)</option>
                                <option value="MXN">Peso Mexicano (MXN)</option>
                                <option value="CLP">Peso Chileno (CLP)</option>
                                <option value="COP">Peso Colombiano (COP)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Formas de pago aceptadas</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700">Efectivo</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700">Tarjeta de débito</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700">Tarjeta de crédito</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-gray-700">Transferencia</span>
                                </label>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (config.actions.includes('demo')) {
                content += `
                    <div class="text-center py-8">
                        <div class="bg-gray-100 rounded-lg p-8 mb-6 inline-block">
                            <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-600 mb-4">Vamos a simular una venta para que veas cómo funciona el sistema.</p>
                        <button class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            Iniciar Demo de Venta
                        </button>
                    </div>
                `;
            }
            
            if (config.actions.includes('review')) {
                content += `
                    <div class="space-y-4">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-green-900">Productos configurados</h4>
                                    <p class="text-sm text-green-700">Tienes productos en tu catálogo</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-green-900">Caja configurada</h4>
                                    <p class="text-sm text-green-700">Formas de pago listas</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-yellow-900">Pendiente: Proveedores</h4>
                                    <p class="text-sm text-yellow-700">Podés agregarlos más tarde</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            formContainer.innerHTML = content;
        }

        function setupButtons(config) {
            const btnSkip = document.getElementById('btn-skip');
            const btnContinue = document.getElementById('btn-continue');
            
            // Botón saltar
            if (config.actions.includes('skip') && !config.actions.includes('complete')) {
                btnSkip.classList.remove('hidden');
                btnSkip.onclick = () => completeStep(true);
            } else {
                btnSkip.classList.add('hidden');
            }
            
            // Botón continuar
            btnContinue.onclick = () => completeStep(false);
        }

        async function completeStep(skipped = false) {
            const btnContinue = document.getElementById('btn-continue');
            btnContinue.disabled = true;
            btnContinue.innerHTML = `
                <span>Guardando...</span>
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
            `;

            try {
                const response = await fetch('/api/onboarding/complete-step', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        onboarding_id: onboardingData.onboarding_id,
                        step_data: { skipped }
                    })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                if (data.is_completed) {
                    showCompletion();
                } else {
                    onboardingData = {
                        ...onboardingData,
                        current_step: data.current_step,
                        progress_percentage: data.progress_percentage,
                        step_config: data.step_config
                    };
                    renderStep();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al guardar. Por favor intenta de nuevo.');
                btnContinue.disabled = false;
                btnContinue.innerHTML = `
                    <span>Continuar</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                `;
            }
        }

        function showCompletion() {
            document.getElementById('step-content').classList.add('hidden');
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('completion-state').classList.remove('hidden');
            document.querySelector('.bg-white.border-b').style.display = 'none';
        }
    </script>
</body>
</html>
