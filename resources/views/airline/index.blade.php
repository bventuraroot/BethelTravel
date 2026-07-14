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
    <script src="{{ asset('assets/js/app-airline-list.js') }}"></script>
@endsection

@section('title', 'Aerolíneas')

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
            <h5 class="mb-0 card-title">Mantenimiento de Aerolíneas</h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table datatables-airline border-top">
                <thead>
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>CÓDIGO IATA</th>
                        <th>NOMBRE</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @isset($airlines)
                        @forelse($airlines as $airline)
                            <tr>
                                <td></td>
                                <td>{{ $airline->id_aerolinea }}</td>
                                <td><span class="badge bg-label-primary">{{ $airline->iata }}</span></td>
                                <td>{{ $airline->nombre }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="javascript: editairline({{ $airline->id_aerolinea }});" class="dropdown-item">
                                            <i class="ti ti-edit ti-sm me-2"></i>Editar
                                        </a>
                                        <a href="javascript:;" class="text-body dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i class="mx-1 ti ti-dots-vertical ti-sm"></i>
                                        </a>
                                        <div class="m-0 dropdown-menu dropdown-menu-end">
                                            <a href="javascript: deleteairline({{ $airline->id_aerolinea }});" class="dropdown-item">
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

    <!-- Add Airline Modal -->
    <div class="modal fade" id="addAirlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="p-3 modal-content p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2">Crear Nueva Aerolínea</h3>
                    </div>
                    <form id="addAirlineForm" class="row" action="{{ route('airline.store') }}" method="POST">
                        @csrf
                        <div class="mb-3 col-12">
                            <label class="form-label" for="iata">Código IATA</label>
                            <input type="text" id="iata" name="iata" class="form-control" placeholder="Ej: AR" maxlength="5" required/>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="nombre">Nombre de la Aerolínea</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ej: Aerolíneas Argentinas" maxlength="60" required/>
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

    <!-- Edit Airline Modal -->
    <div class="modal fade" id="updateAirlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="p-3 modal-content p-md-5">
                <button type="button" class="btn-close btn-pinned" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="mb-4 text-center">
                        <h3 class="mb-2">Editar Aerolínea</h3>
                    </div>
                    <form id="editAirlineForm" class="row" action="{{ route('airline.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="idedit" id="idedit">
                        <div class="mb-3 col-12">
                            <label class="form-label" for="iataedit">Código IATA</label>
                            <input type="text" id="iataedit" name="iataedit" class="form-control" placeholder="Ej: AR" maxlength="5" required/>
                        </div>
                        <div class="mb-3 col-12">
                            <label class="form-label" for="nombreedit">Nombre de la Aerolínea</label>
                            <input type="text" id="nombreedit" name="nombreedit" class="form-control" placeholder="Ej: Aerolíneas Argentinas" maxlength="60" required/>
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
