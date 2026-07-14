/**
 * Calendario en la navbar (Flatpickr inline).
 * Un solo mes (showMonths: 1): el grid de dos meses + width:100% en CSS rompía el layout.
 */
(function () {
    var mount = document.getElementById('brandCalendarMount');
    if (!mount || typeof flatpickr === 'undefined') {
        return;
    }

    var localeEs = {
        amPM: ['AM', 'PM'],
        weekdays: {
            shorthand: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            longhand: [
                'Domingo',
                'Lunes',
                'Martes',
                'Miércoles',
                'Jueves',
                'Viernes',
                'Sábado',
            ],
        },
        months: {
            shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            longhand: [
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre',
            ],
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        weekAbbreviation: 'Sem',
        scrollTitle: 'Desplazar',
        toggleTitle: 'Alternar',
        time_24hr: true,
    };

    var fp = flatpickr(mount, {
        inline: true,
        locale: localeEs,
        dateFormat: 'd/m/Y',
        defaultDate: new Date(),
        allowInput: false,
        clickOpens: false,
        showMonths: 1,
    });

    var toggle = document.getElementById('brandDateToggle');
    if (toggle) {
        toggle.addEventListener('shown.bs.dropdown', function () {
            if (fp && typeof fp.jumpToDate === 'function') {
                fp.jumpToDate(new Date());
            }
            if (fp && typeof fp.redraw === 'function') {
                fp.redraw();
            }
        });
    }
})();
