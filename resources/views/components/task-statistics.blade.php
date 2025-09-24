<!-- Estadísticas de Tareas -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Estadísticas de Tareas</h3>
            <button onclick="refreshTaskStats()" class="text-blue-600 hover:text-blue-800 transition-colors">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>

        <!-- Gráfico de progreso circular -->
        <div class="flex items-center justify-center mb-6">
            <div class="relative w-32 h-32">
                <canvas id="taskProgressChart" width="128" height="128"></canvas>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">
                            {{ $statistics['completion_percentage'] ?? 0 }}%
                        </div>
                        <div class="text-xs text-gray-500">Completado</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas detalladas -->
        <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $statistics['completed_tasks'] ?? 0 }}</div>
                <div class="text-sm text-green-700">Entregadas</div>
            </div>
            <div class="text-center p-3 bg-red-50 rounded-lg">
                <div class="text-2xl font-bold text-red-600">{{ $statistics['pending_tasks'] ?? 0 }}</div>
                <div class="text-sm text-red-700">Pendientes</div>
            </div>
            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600">{{ $statistics['overdue_tasks'] ?? 0 }}</div>
                <div class="text-sm text-yellow-700">Atrasadas</div>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['total_tasks'] ?? 0 }}</div>
                <div class="text-sm text-blue-700">Total</div>
            </div>
        </div>

        <!-- Progreso por materia -->
        @if(!empty($statistics['subjects_progress']))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Progreso por Materia</h4>
                <div class="space-y-3">
                    @foreach($statistics['subjects_progress'] as $subject)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ $subject['name'] }}</span>
                                <span class="text-gray-900 font-medium">{{ $subject['completed'] }}/{{ $subject['total'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                     style="width: {{ $subject['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function refreshTaskStats() {
    // Implementar actualización de estadísticas
    fetch('/dashboard/refresh-stats')
        .then(response => response.json())
        .then(data => {
            // Actualizar estadísticas
            location.reload();
        })
        .catch(error => console.error('Error:', error));
}

// Crear gráfico circular
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('taskProgressChart');
    const ctx = canvas.getContext('2d');
    const percentage = {{ $statistics['completion_percentage'] ?? 0 }};
    
    // Limpiar canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Configuración del gráfico
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = 50;
    const startAngle = -Math.PI / 2;
    const endAngle = startAngle + (2 * Math.PI * percentage / 100);
    
    // Dibujar círculo de fondo
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 8;
    ctx.stroke();
    
    // Dibujar progreso
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.strokeStyle = '#3b82f6';
    ctx.lineWidth = 8;
    ctx.lineCap = 'round';
    ctx.stroke();
});
</script>
