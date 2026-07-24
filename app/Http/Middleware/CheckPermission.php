<?php

namespace App\Http\Middleware;

use App\Http\Controllers\PermissionController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Verifica que el usuario esté autenticado
        if (!$user) {
            abort(403, 'No tienes permiso para acceder a esta ruta.');
        }

        // Llama a PermissionController para obtener el JSON de permisos
        $permissionController = new PermissionController();
        $verticalMenuJson = $permissionController->getpermissionjson();

        // Inicializa arrays para almacenar permisos asignados y roles
        $assignedPermissions = [];
        $roles = [];

        if (isset($verticalMenuJson->original) && is_iterable($verticalMenuJson->original)) {
            foreach ($verticalMenuJson->original as $permiso) {
                if (!empty($permiso->Permiso)) {
                    $assignedPermissions[] = $permiso->Permiso;
                }
                if (!empty($permiso->Rolid)) {
                    $roles[] = (int) $permiso->Rolid;
                }
            }
        }

        // Si el usuario es un administrador (role_id 1), se le permite el acceso a todas las rutas
        if (in_array(1, $roles)) {
            return $next($request);
        }

        // Extrae el nombre de la ruta actual
        $requestedRouteName = $request->route() ? $request->route()->getName() : null;

        if (!$requestedRouteName) {
            return $next($request);
        }

        // 1. Verificación exacta de nombre de permiso asignado al rol
        if (in_array($requestedRouteName, $assignedPermissions)) {
            return $next($request);
        }

        // 2. Permitir rutas auxiliares, consultas, búsquedas, reportes y endpoints de datos (search, report, filter, get, etc.)
        $reqLower = strtolower($requestedRouteName);
        $routeParts = explode('.', $requestedRouteName);
        $actionLower = strtolower(end($routeParts));

        if (
            str_contains($reqLower, 'manual') ||
            str_contains($reqLower, 'search') ||
            str_contains($reqLower, 'report') ||
            str_contains($reqLower, 'filter') ||
            str_contains($reqLower, 'export') ||
            str_contains($reqLower, 'print') ||
            str_contains($reqLower, 'get') ||
            str_contains($reqLower, 'val') ||
            str_contains($reqLower, 'list') ||
            str_contains($reqLower, 'combo') ||
            str_contains($reqLower, 'select') ||
            str_starts_with($reqLower, 'api.')
        ) {
            return $next($request);
        }

        // 3. Comprobar si el usuario tiene permisos sobre el módulo principal (ej. 'client.index', 'sales.index', 'agro-report')
        $requestedTokens = array_filter(preg_split('/[.-]/', $reqLower));
        $firstRequestedToken = rtrim(reset($requestedTokens), 's');

        foreach ($assignedPermissions as $per) {
            $perLower = strtolower($per);
            $assignedTokens = array_filter(preg_split('/[.-]/', $perLower));
            $firstAssignedToken = rtrim(reset($assignedTokens), 's');

            if (
                $firstAssignedToken === $firstRequestedToken ||
                in_array($firstAssignedToken, $requestedTokens) ||
                str_contains($firstRequestedToken, $firstAssignedToken) ||
                str_contains($firstAssignedToken, $firstRequestedToken)
            ) {
                return $next($request);
            }
        }

        // Si no tiene permiso sobre el módulo ni la ruta, aborta con error 403
        abort(403, 'No tienes permiso para acceder a esta ruta.');
    }



    public function handleother(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Verifica que el usuario esté autenticado
        if (!$user) {
            abort(403, 'No tienes permiso para acceder a esta ruta.');
        }

        // Llama a PermissionController para obtener el JSON de permisos
        $permissionController = new PermissionController();
        $verticalMenuJson = $permissionController->getpermissionjson();
        //$permisos = array_column($array, 'Permiso');
        foreach($verticalMenuJson->original as $permiso){
            $rolvalue = $permiso->Rolid;
            $permisoValue = $permiso->Permiso;
        }
        // Extrae el permiso relacionado con la ruta actual (esto depende de cómo estructures el menú y las rutas)
        $requestedPermission = $request->route()->getName();
        dd($permisoValue);

        // Verifica si el usuario tiene el permiso relacionado con la ruta actual
        if ($permisoValue===$requestedPermission || $rolvalue==1) {

        }else{
            // Si no tiene permiso, aborta con error 403
            abort(403, 'No tienes permiso para acceder a esta ruta.');
        }

        return $next($request);
    }


}
