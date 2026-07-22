/**
 *  Hotels List Script
 */

"use strict";

$(function () {
    let borderColor, bodyBg, headingColor;

    if (isDarkStyle) {
        borderColor = config.colors_dark.borderColor;
        bodyBg = config.colors_dark.bodyBg;
        headingColor = config.colors_dark.headingColor;
    } else {
        borderColor = config.colors.borderColor;
        bodyBg = config.colors.bodyBg;
        headingColor = config.colors.headingColor;
    }

    var dt_hotel_table = $('.datatables-hotel');

    if (dt_hotel_table.length) {
        var dt_hotel = dt_hotel_table.DataTable({
            columnDefs: [
                {
                    className: 'control',
                    searchable: false,
                    orderable: false,
                    responsivePriority: 0,
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return '';
                    }
                },
                { responsivePriority: 1, targets: 4 }
            ],
            order: [[1, 'desc']],
            dom:
                '<"row me-2"' +
                '<"col-md-2"<"me-3"l>>' +
                '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
                '>t' +
                '<"row mx-2"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>' +
                '>',
            language: {
                sLengthMenu: '_MENU_',
                search: '',
                searchPlaceholder: 'Buscar Hotel'
            },
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-label-secondary dropdown-toggle mx-3',
                    text: '<i class="ti ti-screen-share me-1 ti-xs"></i>Exportar',
                    buttons: [
                        {
                            extend: 'print',
                            text: '<i class="ti ti-printer me-2" ></i>Imprimir',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [1, 2, 3]
                            }
                        },
                        {
                            extend: 'csv',
                            text: '<i class="ti ti-file-text me-2" ></i>CSV',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [1, 2, 3]
                            }
                        },
                        {
                            extend: 'excel',
                            text: '<i class="ti ti-file-spreadsheet me-2"></i>Excel',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [1, 2, 3]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="ti ti-file-code-2 me-2"></i>PDF',
                            className: 'dropdown-item',
                            exportOptions: {
                                columns: [1, 2, 3]
                            }
                        }
                    ]
                },
                {
                    text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Nuevo Hotel</span>',
                    className: 'add-new btn btn-primary',
                    attr: {
                        'data-bs-toggle': 'modal',
                        'data-bs-target': '#addHotelModal'
                    }
                }
            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Detalles del Hotel ' + data[2];
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== ''
                                ? '<tr data-dt-row="' +
                                      col.rowIndex +
                                      '" data-dt-column="' +
                                      col.columnIndex +
                                      '">' +
                                      '<td>' +
                                      col.title +
                                      ':' +
                                      '</td> ' +
                                      '<td>' +
                                      col.data +
                                      '</td>' +
                                      '</tr>'
                                : '';
                        }).join('');

                        return data ? $('<table class="table"/><tbody />').append(data) : false;
                    }
                }
            }
        });
    }

    // Initialize Select2 inside Modals (allowing custom typed destinations as well)
    $('.select2-destino').select2({
        tags: true,
        dropdownParent: $('#addHotelModal')
    });
    
    $('#updateHotelModal').on('shown.bs.modal', function () {
        $('#destinoedit').select2({
            tags: true,
            dropdownParent: $('#updateHotelModal')
        });
    });

    setTimeout(() => {
        $('.dataTables_filter .form-control').removeClass('form-control-sm');
        $('.dataTables_length .form-select').removeClass('form-select-sm');
    }, 300);
});

function edithotel(id) {
    $.ajax({
        url: "gethotelid/" + btoa(id),
        method: "GET",
        success: function (response) {
            if (response && response.length > 0) {
                var value = response[0];
                $('#idedit').val(value.id_hotel);
                $('#nombreedit').val(value.nombre);
                
                // Set and trigger change on Select2 for destination
                var destUpper = value.destino ? value.destino.toUpperCase() : '';
                if ($('#destinoedit option[value="' + destUpper + '"]').length === 0) {
                    var newOption = new Option(value.destino, destUpper, true, true);
                    $('#destinoedit').append(newOption).trigger('change');
                } else {
                    $('#destinoedit').val(destUpper).trigger('change');
                }
                
                $("#updateHotelModal").modal("show");
            }
        }
    });
}

function deletehotel(id) {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success me-3',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: '¿Desea eliminar este hotel?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "destroy/" + btoa(id),
                method: "GET",
                success: function (response) {
                    if (response.res == 1) {
                        Swal.fire({
                            title: 'Eliminado',
                            text: 'El hotel ha sido eliminado con éxito.',
                            icon: 'success',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    } else {
                        swalWithBootstrapButtons.fire(
                            'Error',
                            'No se pudo eliminar el hotel.',
                            'error'
                        );
                    }
                },
                error: function() {
                    swalWithBootstrapButtons.fire(
                        'Error',
                        'Ocurrió un error en el servidor al intentar eliminar el hotel.',
                        'error'
                    );
                }
            });
        }
    });
}
