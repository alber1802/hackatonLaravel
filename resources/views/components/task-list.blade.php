<!-- Lista de Tareas -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Mis Tareas</h3>
            <div class="flex space-x-2">
                <select id="taskFilter" onchange="filterTasks()" class="text-sm border border-gray-300 rounded-md px-3 py-1">
                    <option value="all">Todas</option>
                    <option value="pending">Pendientes</option>
                    <option value="completed">Entregadas</option>
                    <option value="overdue">Atrasadas</option>
                </select>
                <button onclick="refreshTasks()" class="text-blue-600 hover:text-blue-800 transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-8">
                <button onclick="switchTab('pending')" id="tab-pending" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                    Pendientes ({{ count($tasks['pending'] ?? []) }})
                </button>
                <button onclick="switchTab('completed')" id="tab-completed"
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Entregadas ({{ count($tasks['completed'] ?? []) }})
                </button>
                <button onclick="switchTab('overdue')" id="tab-overdue"
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Atrasadas ({{ count($tasks['overdue'] ?? []) }})
                </button>
            </nav>
        </div>

        <!-- Tareas Pendientes -->
        <div id="pending-tasks" class="task-content">
            @if(!empty($tasks['pending']))
                <div class="space-y-3">
                    @foreach($tasks['pending'] as $task)
                        <div class="border border-yellow-200 bg-yellow-50 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $task['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $task['course_name'] }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Vence: {{ $task['due_date'] }}
                                        </span>
                                        @if($task['points'])
                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-star mr-1"></i>{{ $task['points'] }} puntos
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    @if($task['classroom_link'])
                                        <a href="{{ $task['classroom_link'] }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800 transition-colors">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                    <button onclick="markAsCompleted('{{ $task['id'] }}')" 
                                            class="text-green-600 hover:text-green-800 transition-colors">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </div>
                            </div>
                            @if($task['description'])
                                <p class="text-sm text-gray-700 mt-2">{{ Str::limit($task['description'], 100) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-tasks text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No tienes tareas pendientes</p>
                </div>
            @endif
        </div>

        <!-- Tareas Completadas -->
        <div id="completed-tasks" class="task-content hidden">
            @if(!empty($tasks['completed']))
                <div class="space-y-3">
                    @foreach($tasks['completed'] as $task)
                        <div class="border border-green-200 bg-green-50 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $task['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $task['course_name'] }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Entregada: {{ $task['submitted_date'] }}
                                        </span>
                                        @if($task['grade'])
                                            <span class="text-xs text-gray-700 font-medium">
                                                <i class="fas fa-star mr-1"></i>{{ $task['grade'] }}/{{ $task['points'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($task['classroom_link'])
                                    <a href="{{ $task['classroom_link'] }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-check-circle text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No tienes tareas entregadas</p>
                </div>
            @endif
        </div>

        <!-- Tareas Atrasadas -->
        <div id="overdue-tasks" class="task-content hidden">
            @if(!empty($tasks['overdue']))
                <div class="space-y-3">
                    @foreach($tasks['overdue'] as $task)
                        <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ $task['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $task['course_name'] }}</p>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Atrasada desde: {{ $task['due_date'] }}
                                        </span>
                                        @if($task['points'])
                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-star mr-1"></i>{{ $task['points'] }} puntos
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if($task['classroom_link'])
                                    <a href="{{ $task['classroom_link'] }}" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-calendar-times text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No tienes tareas atrasadas</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Ocultar todos los contenidos
    document.querySelectorAll('.task-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remover clases activas de todos los tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Mostrar contenido seleccionado
    document.getElementById(tabName + '-tasks').classList.remove('hidden');
    
    // Activar tab seleccionado
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

function filterTasks() {
    const filter = document.getElementById('taskFilter').value;
    // Implementar filtrado si es necesario
}

function refreshTasks() {
    fetch('/dashboard/refresh-tasks')
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => console.error('Error:', error));
}

function markAsCompleted(taskId) {
    if (confirm('¿Marcar esta tarea como completada?')) {
        fetch('/dashboard/mark-task-completed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ task_id: taskId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
