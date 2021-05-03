$(document).ready(function () {
    loadQuotaDataListTemplate();
});
var checkboxQuoteIdArray;

function getQuotesCheckboxElement(checkboxElem) {
    var debtPureAmountSelected = 0;
    var debtAmountSelected = 0;
    var debtInterestSelected = 0;
    var checkboxQuoteId;
    checkboxQuoteIdArray = new Array();

    // Si hay elementos seleccionados, habilito el boton de pagar
    if (checkboxElem.length > 0) {
        $('button[data-id="quote-pay-button"]').attr('data-action', 'execute-pay');
        $('button[data-id="quote-pay-button"]').removeAttr('disabled');
    } else {
        $('button[data-id="quote-pay-button"]').attr('data-action', '');
        $('button[data-id="quote-pay-button"]').attr('disabled', 'disabled');
    }

    $.each(checkboxElem, function (i, val) { // Por cada checkbox
        quotePureAmount = $(this).attr('quote-pure-amount'); // Monto puro independiente de cada cuota (Sin intereses)
        quoteAmount = $(this).attr('quote-amount'); // Monto independiente de cada cuota (Con intereses)
        checkboxQuoteId = $(this).attr('data-request'); // Obtengo el atributo, en este caso el id de cuota

        debtPureAmountSelected = formatCurrency(parseFloat(debtPureAmountSelected) + parseFloat(quotePureAmount)); // Acumulador. Suma total del monto puro de la cuota (Sin intereses)

        debtAmountSelected = formatCurrency(parseFloat(debtAmountSelected) + parseFloat(quoteAmount)); // Acumulador. Suma total del monto de la cuota (Con intereses)

        debtInterestSelected = formatCurrency(parseFloat(debtAmountSelected) - parseFloat(debtPureAmountSelected)); // Acumulador. Calculo solo del interes de las cuotas.

        checkboxQuoteIdArray.push(checkboxQuoteId); // Almaceno en un array el id de todas las cuotas seleccionadas
    });

    $("span[data-id='quotes-pay-amount-selected']").html(debtPureAmountSelected); // Inyecto la respuesta, en este caso, el monto puro (sin intereses) de las cuotas seleccionadas
    $("span[data-id='quotes-pay-interest-amount-selected']").html(debtInterestSelected); // Inyecto la respuesta, en este caso, el interes de las cuotas seleccionadas
    $("span[data-id='quotes-pay-amount-total-selected']").html(debtAmountSelected); // Inyecto la respuesta, en este caso, el monto (con intereses) de las cuotas seleccionadas

}

// Click en boton PAGAR independiente
$(document).on("click", 'i[data-id="quote-pay-unique"]', function () {
    var dataReq = $(this).attr('data-request');
    var checkboxes = $("input[data-id='quote-checkbox']");
    var checkboxesUnique = $("input[data-request='" + dataReq + "']");

    checkboxes.prop('checked', false);
    checkboxesUnique.click();
    checkboxQuoteIdArray.push(dataReq); // Almaceno en un array el id de la cuotas seleccionada

    if (checkboxesUnique.is(':checked')) {
        $('button[data-action="execute-pay"]').click();
    }

});

// Click en boton PAGAR
$(document).on("click", 'button[data-action="execute-pay"]', function () {

    $('button[data-id="quote-pay-button"]').attr('data-action', ''); // Deshabilito el boton
    $('button[data-id="quote-pay-button"]').attr('disabled', 'disabled'); // Deshabilito el boton

    showAlert('Procesando...', "info", '100000'); // Le seteo un tiempo alto para que no se cierre sola

    var montoTotalClient = $("span[data-id='quotes-pay-amount-total-selected']").html();
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=quota&_a_=ppt_pay&_ed_=' + montoTotalClient + '&_d_=' + $.base64.encode(checkboxQuoteIdArray),
        success: function (response) {
            showAlert('', 'info', 'close'); // Cierro la notificación anterior.
            $('button[data-id="quote-pay-button"]').attr('data-action', 'execute-pay'); // Habilito el boton
            $('button[data-id="quote-pay-button"]').removeAttr('disabled'); // Habilito el boton

            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Alerta: " + respuesta.descripcion + " <br/>" + respuesta.datos, "error", '5000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                var formUrl = respuesta.datos.form_url;
                window.open(formUrl, '_blank');
            }
        }
    });

});

// Funcion para crear la tabla de la lista de solicitudes
// Los parametros esperados para ret = open o close
// Segundo parametro, es para mostrar la primer columna con un checkbox o un numeral #. Por defecto esta en false
// Tercer parametro, data-id del checkbox habilitado. Es opcional
// Cuarto parametro, className de la columna. Opcional
// Quinto parametro, nombre de la ultima columna. Fix solo para el historial de pago
function createTableList(ret, showCheckBox = false, checkboxDataId, colClassName, lastColName = "A pagar") {
    var tableOpen = '<table class="table table-striped table-bordered table-hover table-sm mt-4 quote-table"><thead><tr>';
    var show = (showCheckBox == true) ? '<input type="checkbox" data-id="' + checkboxDataId + '" class="text-center "/>' : '#';
    tableOpen += '<th scope="col" class="' + colClassName + '">' + show + '</th><th scope="col">Tipo</th><th scope="col">Mes</th><th scope="col">' + lastColName + '</th></tr></thead><tbody>';
    var tableClose = '</tbody></table>';
    return (ret === "open") ? tableOpen : tableClose;
}
function loadQuotaDataListTemplate() {
    // Creo el div contenedor del template
    // Si existe el DIV lo borro
    if (elemExist('_quotes')) {
        $("#_quotes").remove();
    }
    var _quotes = document.createElement('div');
    _quotes.setAttribute("id", "_quotes");
    _quotes.setAttribute("style", "display:none");
    document.body.appendChild(_quotes);
// Cargo el template e inyecto dentro del div creado previamente
    $("#_quotes").load(
            "../mvc/templates/quota.html",
            {'rand': Math.random()},
            function () {
                loadQuotaData('pending');
            }
    );
}

function loadQuotaData(kind) {
    var idQuota = 0;
    var selectedQuotes = 0;
    var debtTotal = parseFloat(0);
    var idEstado = 0;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=quota&_a_=get_list&_k_=' + kind,
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Detalle: " + respuesta.descripcion + " <br/>" + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                var data = respuesta.datos;

                if (kind == 'pending') { // Cuotas pendientes, adeudadas.
                    if (data.length > 0) { // Si existe aunque sea una cuota
                        // Cargo el template e inyecto la informacion
                        $('#_quotes').html(createTableList('open', true, 'quote-checkbox-all') + data.map(tplQuotaData).join('') + createTableList('close'));
                        $('div[data-id="quote-pending-body-container"]').html($('#_quotes').html());
                    } else {
                        $('div[data-id="quote-pending-body-container"]').html("No hay cuotas pendientes a pagar");
                    }
                    // Cargo el controlador de los checkboxes de cada fila.
                    checkBoxController("quote-checkbox", "quotes-amount-selected", getQuotesCheckboxElement);

                    $('input[data-id="quote-checkbox-all"]').on('mouseup', function () {

                        var $checkboxes = $("input[data-id='quote-checkbox']");

                        // Hack para poder calcular el monto final de las cuotas seleccionadas
                        // El codigo se ejecuta "on mouseup". Valida el estado del checkbox si al accionarse el mouseup, el checkbox esta tildado
                        // En ese caso, estariamos destildando el checkbox. Entonces aca entra el hack. Tildo todos los checkboxes de la columna
                        // y luego disparo un click. Esto es para dejar todos destildados y simular el click del usuario para
                        // poder actualizar el valor total de las cuotas seleccionadas en el elemento que contiene esa informacion
                        if ($(this).is(':checked')) {
                            $checkboxes.prop('checked', true);
                            $checkboxes.click();
                        } else {
                            $checkboxes.prop('checked', false);
                            $checkboxes.click();
                        }
                    });

                    // Llamo a la misma funcion pero ahora para cargar el historial solamente
                    loadQuotaData('history');
                } else { // Historial
                    if (data.length > 0) { // Si existe aunque sea una cuota
                        // Cargo el template e inyecto la informacion para el historial
                        $('#_quotes').html(createTableList('open', false, '', 'd-none', 'Fecha de Pago') + data.map(tplQuotaData).join('') + createTableList('close'));
                        $('div[data-id="quote-history-body-container"]').html($('#_quotes').html());
                    } else {
                        $('div[data-id="quote-history-body-container"]').html("No hay un historial disponible");
                    }
                }
            }
        }
    });
}