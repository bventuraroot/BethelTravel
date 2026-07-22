@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/app-airport-list.js') }}"></script>
@endsection

@section('title', 'Aeropuertos')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Errores de validación:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="mb-0 card-title">Mantenimiento de Aeropuertos / Destinos</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table datatables-airport border-top">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>CÓDIGO IATA</th>
                        <th>CIUDAD</th>
                        <th>PAÍS</th>
                        <th>CONTINENTE</th>
                        <th>SUBREGIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($airports)
                        @forelse($airports as $airport)
                            <tr>
                                <td></td>
                                <td>{{ $airport->id_aeropuerto }}</td>
                                <td><span class="badge bg-label-primary">{{ $airport->iata }}</span></td>
                                <td><strong>{{ $airport->ciudad }}</strong></td>
                                <td>{{ $airport->pais }}</td>
                                <td>{{ $airport->continente }}</td>
                                <td>{{ $airport->subregion }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript: editairport({{ $airport->id_aeropuerto }});" class="dropdown-item">
                                            <i class="ti ti-edit ti-sm me-2"></i>Editar
                                        </a>
                                        <a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="mx-1 ti ti-dots-vertical ti-sm"></i>
                                        </a>
                                        <div class="m-0 dropdown-menu dropdown-menu-end">
                                            <a href="javascript: deleteairport({{ $airport->id_aeropuerto }});" class="dropdown-item">
                                                <i class="ti ti-eraser ti-sm me-2"></i>Eliminar
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                    @endisset
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Airport Modal -->
    <div class="modal fade" id="addAirportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="p-3 modal-content p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2">Crear Nuevo Aeropuerto</h3>
                    </div>
                    <form id="addAirportForm" class="row" action="{{ route('airport.store') }}" method="POST">
                        @csrf
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="iata">Código IATA</label>
                            <input type="text" id="iata" name="iata" class="form-control" placeholder="Ej: MDE" maxlength="5" required/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad" name="ciudad" class="form-control" placeholder="Ej: Medellín" maxlength="100" required/>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="pais">País</label>
                            <input type="text" id="pais" name="pais" class="form-control" placeholder="Ej: Colombia" maxlength="100" required/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="continente">Continente</label>
                            <input type="text" id="continente" name="continente" class="form-control" placeholder="Ej: America" maxlength="100"/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="subregion">Subregión</label>
                            <input type="text" id="subregion" name="subregion" class="form-control" placeholder="Ej: Sudamerica" maxlength="100"/>
                        </div>
                        <div class="text-center col-12 demo-vertical-spacing mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Crear</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Descartar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Airport Modal -->
    <div class="modal fade" id="updateAirportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="p-3 modal-content p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2">Editar Aeropuerto</h3>
                    </div>
                    <form id="editAirportForm" class="row" action="{{ route('airport.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="idedit" id="idedit">
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="iataedit">Código IATA</label>
                            <input type="text" id="iataedit" name="iataedit" class="form-control" placeholder="Ej: MDE" maxlength="5" required/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="ciudadedit">Ciudad</label>
                            <input type="text" id="ciudadedit" name="ciudadedit" class="form-control" placeholder="Ej: Medellín" maxlength="100" required/>
                        </div>
                        <div class="mb-3 col-md-12">
                            <label class="form-label" for="paisedit">País</label>
                            <input type="text" id="paisedit" name="paisedit" class="form-control" placeholder="Ej: Colombia" maxlength="100" required/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="continenteedit">Continente</label>
                            <input type="text" id="continenteedit" name="continenteedit" class="form-control" placeholder="Ej: America" maxlength="100"/>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="subregionedit">Subregión</label>
                            <input type="text" id="subregionedit" name="subregionedit" class="form-control" placeholder="Ej: Sudamerica" maxlength="100"/>
                        </div>
                        <div class="text-center col-12 demo-vertical-spacing mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">Guardar</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">Descartar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
