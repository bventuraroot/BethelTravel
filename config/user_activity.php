<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Máximo de registros de actividad por usuario
    |--------------------------------------------------------------------------
    */
    'max_logs_per_user' => (int) env('USER_ACTIVITY_MAX_PER_USER', 200),

    /*
    |--------------------------------------------------------------------------
    | Rutas que no se registran (nombre de ruta)
    |--------------------------------------------------------------------------
    */
    'excluded_route_names' => [
        'activity.recent',
        'activity.unread-count',
        'activity.mark-seen',
    ],

    /*
    |--------------------------------------------------------------------------
    | Prefijos de path que no se registran
    |--------------------------------------------------------------------------
    */
    'excluded_path_prefixes' => [
        '_ignition',
        'telescope',
        'horizon',
        'livewire',
    ],

    /*
    |--------------------------------------------------------------------------
    | Etiquetas personalizadas por nombre de ruta (opcional)
    |--------------------------------------------------------------------------
    | Si no hay entrada, se genera un texto a partir del método y la ruta.
    |
    */
    'route_labels' => [
        'login' => 'Inicio de sesión',
        'logout' => 'Cierre de sesión',
        'dashboard' => 'Visita al panel principal',
        'profile.update' => 'Perfil actualizado',
        'profile.destroy' => 'Cuenta eliminada',
        'client.update' => 'Cliente actualizado',
        'client.store' => 'Cliente registrado',
        'client.destroy' => 'Cliente eliminado',
    ],
];
