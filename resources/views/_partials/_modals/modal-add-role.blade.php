@foreach ($roles as $rol) 
    <!-- Add Role Modal -->
    <div class="modal fade" id="UpdateRoleModal{{ $rol->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
            <div class="p-3 modal-content p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2 role-title">Editar Rol: {{ $rol->name }}</h3>
                        <p class="text-muted">Establecer permisos del rol</p>
                    </div>
                    <!-- Add role form -->
                    <form id="addRoleForm{{ $rol->id }}" class="row g-3" action="{{route('rol.update')}}" method="POST">
                        @csrf @method('PATCH')
                        <div class="col-12">
                            <h5 class="mb-3">Permisos del Rol</h5>
                            <!-- Permission table -->
                            <div class="table-responsive">
                                <input type="hidden" name="rolid" id="rolid" value="{{$rol->id}}">
                                <table class="table table-flush-spacing">
                                    <thead>
                                        <tr>
                                            <th>Módulo</th>
                                            <th>Permisos Asignados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permissiondata as $indexmodul => $modul)
                                            <tr>
                                                <td class="text-nowrap fw-semibold">
                                                    {{ Str::upper(str_replace(['-', '_'], ' ', $modul->modules)) }}
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        <!-- Ver (index) -->
                                                        <div class="form-check me-3">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{$rol->id}}_{{$modul->modules}}_index" 
                                                                name="{{$modul->modules}}_index"
                                                                @if (@$permissionsbyrol[$rol->name][$modul->modules]['index']=='index' || $rol->name=='Admin') checked @endif />
                                                            <label class="form-check-label" for="{{$rol->id}}_{{$modul->modules}}_index">
                                                                Ver
                                                            </label>
                                                        </div>

                                                        <!-- Crear / Guardar (store) -->
                                                        <div class="form-check me-3">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{$rol->id}}_{{$modul->modules}}_store" 
                                                                name="{{$modul->modules}}_store"
                                                                @if (@$permissionsbyrol[$rol->name][$modul->modules]['store']=='store' || $rol->name=='Admin') checked @endif />
                                                            <label class="form-check-label" for="{{$rol->id}}_{{$modul->modules}}_store">
                                                                Crear
                                                            </label>
                                                        </div>

                                                        <!-- Actualizar (update) -->
                                                        <div class="form-check me-3">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{$rol->id}}_{{$modul->modules}}_update" 
                                                                name="{{$modul->modules}}_update"
                                                                @if (@$permissionsbyrol[$rol->name][$modul->modules]['update']=='update' || $rol->name=='Admin') checked @endif />
                                                            <label class="form-check-label" for="{{$rol->id}}_{{$modul->modules}}_update">
                                                                Actualizar
                                                            </label>
                                                        </div>

                                                        <!-- Eliminar (destroy) -->
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="{{$rol->id}}_{{$modul->modules}}_destroy" 
                                                                name="{{$modul->modules}}_destroy"
                                                                @if (@$permissionsbyrol[$rol->name][$modul->modules]['destroy']=='destroy' || $rol->name=='Admin') checked @endif />
                                                            <label class="form-check-label text-danger" for="{{$rol->id}}_{{$modul->modules}}_destroy">
                                                                Eliminar
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- Permission table -->
                        </div>
                        <div class="mt-4 text-center col-12">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar Cambios</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                                aria-label="Close">Cancelar</button>
                        </div>
                    </form>
                    <!--/ Add role form -->
                </div>
            </div>
        </div>
    </div>
    <!--/ Add Role Modal -->
@endforeach

<!-- Modal Crear Nuevo Rol -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
        <div class="p-3 modal-content p-md-5">
            <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-body">
                <div class="mb-4 text-center">
                    <h3 class="mb-2 role-title">Crear Nuevo Rol</h3>
                    <p class="text-muted">Establecer permisos del nuevo rol</p>
                </div>
                <!-- Add role form -->
                <form id="addRoleForm" class="row g-3" action="{{route('rol.store')}}" method="POST">
                    @csrf @method('POST')
                    <div class="mb-4 col-12">
                        <label class="form-label" for="modalRoleName">Nombre del Rol</label>
                        <input type="text" id="modalRoleName" name="modalRoleName" class="form-control"
                            placeholder="Ej. Supervisor de Ventas" required/>
                    </div>
                    <div class="col-12">
                        <h5 class="mb-3">Permisos del Rol</h5>
                        <!-- Permission table -->
                        <div class="table-responsive">
                            <table class="table table-flush-spacing">
                                <thead>
                                    <tr>
                                        <th>Módulo</th>
                                        <th>Permisos Asignados</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissiondata as $modul)
                                        <tr>
                                            <td class="text-nowrap fw-semibold">
                                                {{ Str::upper(str_replace(['-', '_'], ' ', $modul->modules)) }}
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-3">
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="new_{{$modul->modules}}_index" name="{{$modul->modules}}_index" />
                                                        <label class="form-check-label" for="new_{{$modul->modules}}_index">
                                                            Ver
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="new_{{$modul->modules}}_store" name="{{$modul->modules}}_store" />
                                                        <label class="form-check-label" for="new_{{$modul->modules}}_store">
                                                            Crear
                                                        </label>
                                                    </div>
                                                    <div class="form-check me-3">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="new_{{$modul->modules}}_update" name="{{$modul->modules}}_update" />
                                                        <label class="form-check-label" for="new_{{$modul->modules}}_update">
                                                            Actualizar
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="new_{{$modul->modules}}_destroy" name="{{$modul->modules}}_destroy" />
                                                        <label class="form-check-label text-danger" for="new_{{$modul->modules}}_destroy">
                                                            Eliminar
                                                        </label>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Permission table -->
                    </div>
                    <div class="mt-4 text-center col-12">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar Rol</button>
                        <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal"
                            aria-label="Close">Cancelar</button>
                    </div>
                </form>
                <!--/ Add role form -->
            </div>
        </div>
    </div>
</div>
<!--/ Add Role Modal -->
