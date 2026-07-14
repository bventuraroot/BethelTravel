<?php

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use App\Support\ActivityLogLabel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    private const METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! Auth::check()) {
            return $response;
        }

        if (! self::shouldRecordMethod($request)) {
            return $response;
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            return $response;
        }

        if (! ($response->isSuccessful() || $response->isRedirection())) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();
        if ($routeName && in_array($routeName, config('user_activity.excluded_route_names', []), true)) {
            return $response;
        }

        $path = '/'.ltrim($request->path(), '/');
        foreach (config('user_activity.excluded_path_prefixes', []) as $prefix) {
            if ($prefix !== '' && str_starts_with($path, '/'.$prefix)) {
                return $response;
            }
        }

        try {
            UserActivityLog::create([
                'user_id' => Auth::id(),
                'method' => $request->method(),
                'route_name' => $routeName,
                'path' => Str::limit($path, 500, ''),
                'action_label' => ActivityLogLabel::for($request, $status),
                'status_code' => $status,
                'meta' => null,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }

    private static function shouldRecordMethod(Request $request): bool
    {
        if (in_array($request->method(), self::METHODS, true)) {
            return true;
        }

        if ($request->isMethod('GET')) {
            $name = $request->route()?->getName();

            return (bool) ($name && str_ends_with($name, '.destroy'));
        }

        return false;
    }
}
