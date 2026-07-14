<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogLabel
{
    public static function for(Request $request, int $statusCode): string
    {
        $route = $request->route();
        $name = $route?->getName();
        $custom = $name ? config('user_activity.route_labels.'.$name) : null;
        if (is_string($custom) && $custom !== '') {
            return $custom;
        }

        $method = $request->method();
        if ($method === 'GET' && $name && str_ends_with($name, '.destroy')) {
            $verb = 'Eliminación';
        } else {
            $verb = match ($method) {
                'POST' => 'Registro',
                'PUT', 'PATCH' => 'Actualización',
                'DELETE' => 'Eliminación',
                default => $method,
            };
        }

        if ($name) {
            $readable = Str::headline(str_replace(['.', '-', '_'], ' ', $name));

            return trim($verb.' · '.$readable);
        }

        return trim($verb.' · '.$request->path());
    }
}
