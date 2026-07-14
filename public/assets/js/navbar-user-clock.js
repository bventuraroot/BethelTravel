/**
 * Fecha en la barra superior (junto a notificaciones); zona horaria del navegador.
 * Texto corto en pantalla; fecha completa en el atributo title (tooltip).
 */
(function () {
    var elDate = document.getElementById('navbarUserDate');
    if (!elDate) {
        return;
    }

    var days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    var months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    function tick() {
        var d = new Date();
        var dateStrLong =
            days[d.getDay()] +
            ', ' +
            d.getDate() +
            ' ' +
            months[d.getMonth()] +
            ' ' +
            d.getFullYear();
        var dateStrShort =
            d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        elDate.textContent = dateStrShort;
        elDate.setAttribute('title', dateStrLong);
    }

    tick();
    setInterval(tick, 60000);
})();
