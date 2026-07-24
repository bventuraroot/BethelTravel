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

        // 1. Verificación exacta de nombre de permiso
        if (in_array($requestedRouteName, $assignedPermissions)) {
            return $next($request);
        }

        // 2. Extrae el prefijo del módulo y la acción solicitada
        $routeParts = explode('.', $requestedRouteName);
        $permissionPrefix = $routeParts[0];
        $action = end($routeParts);

        // Comprobar si el usuario tiene permisos sobre el módulo (ej. 'company.index' o 'company.store')
        $userHasModulePermissions = false;
        foreach ($assignedPermissions as $per) {
            $assignedPrefix = explode('.', $per)[0];
            if ($assignedPrefix === $permissionPrefix || ($permissionPrefix === 'user' && $assignedPrefix === 'users') || ($permissionPrefix === 'users' && $assignedPrefix === 'user')) {
                $userHasModulePermissions = true;
                break;
            }
        }

        // Lista de acciones que requieren restricción estricta por acción
        $restrictedActions = ['destroy', 'delete', 'store', 'create', 'update', 'edit', 'clean', 'download'];

        // Si el usuario tiene acceso al módulo y la ruta solicitada es de consulta/lectura o auxiliar (ej. 'getCompany', 'getusers', 'index', 'view')
        if ($userHasModulePermissions && !in_array($action, $restrictedActions)) {
            return $next($request);
        }

        // Si no tiene permiso, aborta con error 403
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
