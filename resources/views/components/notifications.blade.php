<!-- Panel de Notificaciones -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                Notificaciones
                @if($unread_count > 0)
                    <span class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                        {{ $unread_count }}
                    </span>
                @endif
            </h3>
            <div class="flex space-x-2">
                <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                    Marcar todas como leídas
                </button>
                <button onclick="refreshNotifications()" class="text-blue-600 hover:text-blue-800 transition-colors">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        <!-- Filtros de notificaciones -->
        <div class="flex space-x-2 mb-4">
            <button onclick="filterNotifications('all')" id="filter-all" 
                    class="notification-filter px-3 py-1 text-sm rounded-md bg-blue-100 text-blue-700 border border-blue-200">
                Todas
            </button>
            <button onclick="filterNotifications('tasks')" id="filter-tasks"
                    class="notification-filter px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200">
                Tareas
            </button>
            <button onclick="filterNotifications('grades')" id="filter-grades"
                    class="notification-filter px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200">
                Calificaciones
            </button>
            <button onclick="filterNotifications('announcements')" id="filter-announcements"
                    class="notification-filter px-3 py-1 text-sm rounded-md bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200">
                Anuncios
            </button>
        </div>

        <!-- Lista de notificaciones -->
        <div class="space-y-3 max-h-96 overflow-y-auto">
            @if(!empty($notifications))
                @foreach($notifications as $notification)
                    <div class="notification-item {{ $notification['read'] ? 'opacity-75' : '' }} 
                              border rounded-lg p-4 hover:shadow-md transition-all cursor-pointer
                              {{ $notification['type'] === 'task' ? 'border-blue-200 bg-blue-50' : '' }}
                              {{ $notification['type'] === 'grade' ? 'border-green-200 bg-green-50' : '' }}
                              {{ $notification['type'] === 'announcement' ? 'border-yellow-200 bg-yellow-50' : '' }}
                              {{ $notification['type'] === 'overdue' ? 'border-red-200 bg-red-50' : '' }}"
                         data-type="{{ $notification['type'] }}"
                         onclick="markAsRead('{{ $notification['id'] }}')">
                        
                        <div class="flex items-start space-x-3">
                            <!-- Icono de notificación -->
                            <div class="flex-shrink-0 mt-1">
                                @if($notification['type'] === 'task')
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-tasks text-blue-600 text-sm"></i>
                                    </div>
                                @elseif($notification['type'] === 'grade')
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-star text-green-600 text-sm"></i>
                                    </div>
                                @elseif($notification['type'] === 'announcement')
                                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-bullhorn text-yellow-600 text-sm"></i>
                                    </div>
                                @elseif($notification['type'] === 'overdue')
                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                    </div>
                                @endif
                            </div>

                            <!-- Contenido de la notificación -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $notification['title'] }}
                                    </p>
                                    @if(!$notification['read'])
                                        <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $notification['message'] }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-xs text-gray-500">{{ $notification['course_name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $notification['time_ago'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8">
                    <i class="fas fa-bell-slash text-gray-300 text-4xl mb-4"></i>
                    <p class="text-gray-500">No tienes notificaciones</p>
                </div>
            @endif
        </div>

        <!-- Configuración de notificaciones -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-700">Notificaciones automáticas</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="autoNotifications" class="sr-only peer" 
                           {{ $settings['auto_notifications'] ?? true ? 'checked' : '' }}
                           onchange="toggleAutoNotifications()">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-sm text-gray-700">Notificaciones de tareas próximas</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="taskReminders" class="sr-only peer"
                           {{ $settings['task_reminders'] ?? true ? 'checked' : '' }}
                           onchange="toggleTaskReminders()">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>
</div>

<script>
function filterNotifications(type) {
    // Remover clases activas de todos los filtros
    document.querySelectorAll('.notification-filter').forEach(filter => {
        filter.classList.remove('bg-blue-100', 'text-blue-700', 'border-blue-200');
        filter.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-200');
    });
    
    // Activar filtro seleccionado
    const activeFilter = document.getElementById('filter-' + type);
    activeFilter.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-200');
    activeFilter.classList.add('bg-blue-100', 'text-blue-700', 'border-blue-200');
    
    // Mostrar/ocultar notificaciones
    document.querySelectorAll('.notification-item').forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function markAsRead(notificationId) {
    fetch('/dashboard/mark-notification-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar visualmente la notificación
            const notificationElement = document.querySelector(`[onclick="markAsRead('${notificationId}')"]`);
            if (notificationElement) {
                notificationElement.classList.add('opacity-75');
                const unreadDot = notificationElement.querySelector('.w-2.h-2.bg-blue-600');
                if (unreadDot) {
                    unreadDot.remove();
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/dashboard/mark-all-notifications-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function refreshNotifications() {
    fetch('/dashboard/refresh-notifications')
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => console.error('Error:', error));
}

function toggleAutoNotifications() {
    const enabled = document.getElementById('autoNotifications').checked;
    updateNotificationSetting('auto_notifications', enabled);
}

function toggleTaskReminders() {
    const enabled = document.getElementById('taskReminders').checked;
    updateNotificationSetting('task_reminders', enabled);
}

function updateNotificationSetting(setting, value) {
    fetch('/dashboard/update-notification-setting', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ setting: setting, value: value })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Configuración actualizada');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Actualizar notificaciones cada 5 minutos
setInterval(function() {
    if (document.getElementById('autoNotifications').checked) {
        refreshNotifications();
    }
}, 300000);
</script>
