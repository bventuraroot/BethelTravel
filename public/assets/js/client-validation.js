/**
 * Validaciones de Cliente - BethelTravelSV
 * Previene duplicados de DUI, NIT y NCR
 */

'use strict';

// Configurar AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// Variables globales
let validationTimeout = null;
let isValidationInProgress = false;

/**
 * Validar DUI/NIT/Pasaporte en tiempo real
 */
function validateClientKey(inputElement, tpersona, companyId, clientId = null) {
    const value = inputElement.value.trim();

    // Debug: verificar valores
    console.log('validateClientKey - clientId:', clientId);
    console.log('validateClientKey - tpersona:', tpersona);
    console.log('validateClientKey - companyId:', companyId);
    console.log('validateClientKey - value:', value);

    // Limpiar timeout anterior
    if (validationTimeout) {
        clearTimeout(validationTimeout);
    }

    // Si el campo está vacío, limpiar mensajes
    if (!value) {
        clearValidationMessage(inputElement);
        return;
    }

    // Debounce: esperar 500ms después del último cambio
    validationTimeout = setTimeout(() => {
        performValidation(value, tpersona, companyId, clientId, inputElement);
    }, 500);
}

/**
 * Validar NCR específicamente
 */
function validateNcr(inputElement, companyId, clientId = null) {
    const value = inputElement.value.trim();

    // Limpiar timeout anterior
    if (validationTimeout) {
        clearTimeout(validationTimeout);
    }

    // Si el campo está vacío o es N/A, limpiar mensajes
    if (!value || value === 'N/A') {
        clearValidationMessage(inputElement);
        return;
    }

    // Debounce: esperar 500ms después del último cambio
    validationTimeout = setTimeout(() => {
        performNcrValidation(value, companyId, clientId, inputElement);
    }, 500);
}

/**
 * Realizar validación de DUI/NIT/Pasaporte
 */
function performValidation(value, tpersona, companyId, clientId, inputElement) {
    if (isValidationInProgress) return;

    isValidationInProgress = true;
    showValidationLoading(inputElement);

    $.ajax({
        url: '/client/keyclient',
        method: 'POST',
        data: {
            num: value,
            tpersona: tpersona,
            company_id: companyId,
            client_id: clientId
        },
        success: function(response) {
            isValidationInProgress = false;

            if (response.exists) {
                showValidationError(inputElement, response.message);
            } else {
                showValidationSuccess(inputElement, response.message);
            }
        },
        error: function(xhr) {
            isValidationInProgress = false;
            showValidationError(inputElement, 'Error al validar. Intente nuevamente.');
            console.error('Error en validación:', xhr.responseText);
        }
    });
}

/**
 * Realizar validación de NCR
 */
function performNcrValidation(value, companyId, clientId, inputElement) {
    if (isValidationInProgress) return;

    isValidationInProgress = true;
    showValidationLoading(inputElement);

    $.ajax({
        url: '/client/validate-ncr',
        method: 'POST',
        data: {
            ncr: value,
            company_id: companyId,
            client_id: clientId
        },
        success: function(response) {
            isValidationInProgress = false;

            if (response.exists) {
                showValidationError(inputElement, response.message);
            } else {
                showValidationSuccess(inputElement, response.message);
            }
        },
        error: function(xhr) {
            isValidationInProgress = false;
            showValidationError(inputElement, 'Error al validar NCR. Intente nuevamente.');
            console.error('Error en validación NCR:', xhr.responseText);
        }
    });
}

/**
 * Mostrar estado de carga
 */
function showValidationLoading(inputElement) {
    clearValidationMessage(inputElement);

    const feedbackElement = getOrCreateFeedbackElement(inputElement);
    feedbackElement.html('<i class="ti ti-loader-2 ti-spin me-1"></i>Validando...');
    feedbackElement.removeClass('text-danger text-success').addClass('text-info');
    feedbackElement.show();

    $(inputElement).removeClass('is-valid is-invalid');
}

/**
 * Mostrar error de validación
 */
function showValidationError(inputElement, message) {
    const feedbackElement = getOrCreateFeedbackElement(inputElement);
    feedbackElement.html('<i class="ti ti-alert-circle me-1"></i>' + message);
    feedbackElement.removeClass('text-info text-success').addClass('text-danger');
    feedbackElement.show();

    $(inputElement).removeClass('is-valid').addClass('is-invalid');
}

/**
 * Mostrar éxito de validación
 */
function showValidationSuccess(inputElement, message) {
    const feedbackElement = getOrCreateFeedbackElement(inputElement);
    feedbackElement.html('<i class="ti ti-check-circle me-1"></i>' + message);
    feedbackElement.removeClass('text-info text-danger').addClass('text-success');
    feedbackElement.show();

    $(inputElement).removeClass('is-invalid').addClass('is-valid');
}

/**
 * Limpiar mensaje de validación
 */
function clearValidationMessage(inputElement) {
    const feedbackElement = getOrCreateFeedbackElement(inputElement);
    feedbackElement.hide();
    feedbackElement.removeClass('text-danger text-success text-info');

    $(inputElement).removeClass('is-valid is-invalid');
}

/**
 * Obtener o crear elemento de feedback
 */
function getOrCreateFeedbackElement(inputElement) {
    // Convertir a jQuery si es necesario
    const $inputElement = $(inputElement);
    let feedbackElement = $inputElement.parent().find('.validation-feedback');

    if (feedbackElement.length === 0) {
        feedbackElement = $('<div class="validation-feedback invalid-feedback" style="display: none;"></div>');
        $inputElement.parent().append(feedbackElement);
    }

    return feedbackElement;
}

/**
 * Validar formulario antes del envío
 */
function validateFormBeforeSubmit(formElement) {
    let isValid = true;
    const errors = [];

    // Verificar campos con clase is-invalid
    formElement.find('.is-invalid').each(function() {
        isValid = false;
        const fieldName = $(this).attr('name') || $(this).attr('id') || 'campo';
        errors.push(`El campo ${fieldName} tiene errores de validación.`);
    });

    // Verificar campos requeridos
    formElement.find('[required]').each(function() {
        if (!$(this).val().trim()) {
            isValid = false;
            const fieldName = $(this).attr('name') || $(this).attr('id') || 'campo';
            errors.push(`El campo ${fieldName} es requerido.`);
        }
    });

    if (!isValid) {
        showFormErrors(errors);
        return false;
    }

    return true;
}

/**
 * Mostrar errores del formulario
 */
function showFormErrors(errors) {
    const errorMessage = errors.join('<br>');

    Swal.fire({
        title: 'Errores de Validación',
        html: errorMessage,
        icon: 'error',
        confirmButtonText: 'Entendido'
    });
}

/**
 * Inicializar validaciones cuando el documento esté listo
 */
$(document).ready(function() {
    function getValidationContext(isEdit) {
        let tpersona = isEdit
            ? ($('#tpersonaedit').val() || $('input[name="tpersonaedit"]:checked').val())
            : ($('#tpersona').val() || $('input[name="tpersona"]:checked').val());
        let companyId = isEdit
            ? ($('#companyselectededit').val() || $('#selectcompany').val())
            : ($('#companyselected').val() || $('#selectcompany').val());
        let clientId = isEdit ? ($('#idedit').val() || null) : null;

        return { tpersona: tpersona || 'N', companyId: companyId || '0', clientId: clientId };
    }

    // Validación para DUI/NIT/Pasaporte
    $(document).on('input blur change', 'input[name="nit"], input[name="nitedit"]', function() {
        const isEdit = $(this).attr('name') === 'nitedit';
        const ctx = getValidationContext(isEdit);
        validateClientKey(this, ctx.tpersona, ctx.companyId, ctx.clientId);
    });

    // Validación para NCR
    $(document).on('input blur change', 'input[name="ncr"], input[name="ncredit"]', function() {
        const isEdit = $(this).attr('name') === 'ncredit';
        const ctx = getValidationContext(isEdit);
        validateNcr(this, ctx.companyId, ctx.clientId);
    });

    // Validación para pasaporte
    $(document).on('input blur change', 'input[name="pasaporte"], input[name="pasaporteedit"]', function() {
        const isEdit = $(this).attr('name') === 'pasaporteedit';
        const ctx = getValidationContext(isEdit);
        validateClientKey(this, 'E', ctx.companyId, ctx.clientId);
    });

    // Limpiar validaciones cuando cambie el tipo de persona
    $('#tpersona, #tpersonaedit, input[name="tpersona"], input[name="tpersonaedit"]').on('change', function() {
        $('.validation-feedback').hide();
        $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    });
});

/**
 * Validar pasaporte específicamente para extranjeros
 */
function validatePasaporte(inputElement, companyId, clientId = null) {
    validateClientKey(inputElement, 'E', companyId, clientId);
}

/**
 * Función para validar manualmente (llamada desde HTML)
 */
window.validateClientKey = validateClientKey;
window.validateNcr = validateNcr;
window.validatePasaporte = validatePasaporte;
