$(document).ready(function () {
// OBTENGO LOS PARAMETROS DE LA URL //
// ESTO LO UTILIZO SOLO PARA EL CAMBIO DE CLAVE //
    var queryString = window.location.href;
    var urlParams = queryString.split('nueva_clave/');
    var data_clave = (typeof (urlParams[1]) !== 'undefined' && urlParams[1] !== null ? urlParams[1] : '');
    // FIN OBTENGO LOS PARAMETROS DE LA URL //

    // SI EL PARAMETRO NO ESTA VACIO //
    if (data_clave.length !== 0) {
        var resp;
        var codMember;
        $.ajax({
            type: "POST",
            url: '../src/new_pass.php?data=' + data_clave,
            success: function (data)
            {
                var rsp = $.parseJSON(data);
                if (rsp.state === 0) { // Si el enlace ya fue utilizado
                    resp = '<div class="card-header"><h5 class="card-title" data-id="card-container-title">';
                    resp += '<i class="far fa-times-circle"></i>' + rsp.descrip;
                    resp += "</h5></div>";
                } else {
                    resp = rsp.descrip; // Si el enlace todavia es valido
                }
                codMember = data_clave.split('_');
                $('div[data-id="card-container"]').html(resp); // Inyecto la respuesta
                $('input[data-id="new-pass-cod-member"]').val(codMember[0]); // Inyecto el codMember en un input
                $('input[data-id="new-pass-psw-link"]').val(codMember[1]); // Inyecto la pass de la URL en un input
                window.history.pushState('data', 'Title', '../inicio');
            }
        });
    }

// Cuando se presiona ENTER en el campo de Nro Matricula
    $('input[data-id="login-input-nro-matricula"]').on("keydown", function (event) {
        if (event.which == 13) {
            $('button[data-id="login-next"]').click();
            return false;
        }
    });
    $('div[data-button="print"], div[data-button="request"]').unbind().click(function (e) {
        e.preventDefault();
        var action = $(this).attr('data-action');
        // Llamo a la funcion que controla el nuevo formulario
        submitFormMat(action);
    });
// Para comprobar si un elemento de DOM existe o si una variable está declarada
    window.elemExist = function (id) {
        var element = document.getElementById(id);
        if (typeof (element) !== 'undefined' && element !== null) {
//        console.log("exists");
            return true;
        } else {
//        console.log("not exists");
            return false;
        }
    };
    // Funcion para evitar que se clickee dos veces un elemento
    window.dontClickTwice = function (elem) {
        if ($(elem).attr("data-disabled")) {
            return false;
        } else {
            $(elem).attr("data-disabled", "true");
            setTimeout(function () {
                $(elem).removeAttr("data-disabled");
            }, 1000);
            return true;
        }
    };
    function botbooxcred(msg, url) {
        // Recibo el mensaje (imagen) y la muestro
        msg = "<img src='data:image/jpeg;base64," + msg + "' width='100%'/>";
        bootbox.confirm({
            message: msg,
            backdrop: true,
            size: 'large',
            buttons: {
                confirm: {
                    label: 'Descargar',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'Cerrar',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if (result) { // Si doy al boton Descargar
                    // Paso la url de nuevo, con el 2do parametro en 1 (uno) indicando una descarga
                    credential(url, '1');
                    return false;
                }
            }
        });
    }

// Funcion para mostrar los errores cuando falta la foto-carnet para la credencial
    function botbooxCredPhoto(msg) {
        var callBotbooxCredPhoto = bootbox.dialog({
            title: "<span class='bb-title-center'><i class='fas fa-info-circle'></i></i> No se puede generar la Credencial</span>",
            message: "<div class='text-justify fz-15px'>" + msg + "</div>",
            onEscape: function () {
                // Se cierra el dialog
                this.modal('hide');
            }
        });
    }

// Cuando se dispara el form de login
    $('form[data-form="msubmit"]').submit(function (e) {
        e.preventDefault(); // Evita se ejecute el form normalmente
        var formValidate = $(this).attr('data-validate'); // Obtengo la instruccion si es DNI o Pass
        // Si tengo que validar DNI o Pass, dependiendo el caso, checkeo que los inputs no estén vacios
        if ((formValidate === 'dni' && $('input[data-id="login-input-dni"]').val().length !== 0) || (formValidate === 'pass' && $('input[data-id="login-input-pass"]').val().length !== 0)) {
//        if ((formValidate === 'dni' && $('input[data-id="login-input-dni"]').val().length === 0) || (formValidate === 'pass' && $('input[data-id="login-input-pass"]').val().length === 0)) {
            var form = $(this);
            $.ajax({
                type: "POST",
                url: 'src/system.php',
                // Serializa los elementos del form
                data: form.serialize(),
                success: function (data)
                {
                    // data = 1 es error
                    if (data === '1') {
                        // Llamo a la funcion que muestras los errores
                        showAlert("Los datos ingresados no son válidos", 'error');
                    } else {

                        // Si no hay errores, hago un redirect a inicio
                        window.location = 'inicio';
                    }
                },
                complete: function () {
                    // Al finalizar el ajax, asigno el evento a los botones
                    $('div[data-button="print"]').unbind().click(function (e) {
                        e.preventDefault();
                        var action = $(this).attr('data-action');
                        // Llamo a la funcion que controla el nuevo formulario
                        submitFormMat(action);
                    });
                }
            });
        } else { // Si alguno de los inputs (DNI o Pass) esta vacio
            if (formValidate === 'dni') { // Si el campo DNI esta vacio
                $('div[data-id="login-dni"]').effect("shake");
            }
            if (formValidate === 'pass') { // Si el campo pass esta vacio
                $('div[data-id="login-pass"]').effect("shake");
            }
        }
    });
    // Boton omitir por ahora (clave nueva)
    // En este caso como el elemento es dinamico, creo el bind del click asi, para que lo detecte.
    $(document).on("click", 'button[data-id="new-pass-skip"]', function () {
        var form = $('form[data-form="psubmit"]');
        $.ajax({
            type: "POST",
            url: 'src/system.php?isEncrypted=1',
            // Serializa los elementos del form
            data: form.serialize(),
            success: function (data)
            {
                // data = 1 es error
                if (data === '1') {
                    // Llamo a la funcion que muestras los errores
                    showAlert("Los datos ingresados no son válidos", 'error');
                } else {
                    // Si no hay errores, cargo el contenido en la misma página
                    window.location.reload();
                }
            },
            complete: function () {
                // Al finalizar el ajax, asigno el evento a los botones
                $('div[data-button="print"]').unbind().click(function (e) {
                    e.preventDefault();
                    var action = $(this).attr('data-action');
                    // Llamo a la funcion que controla el nuevo formulario
                    submitFormMat(action);
                });
            }
        });
    });
    // Boton guardar clave nueva
    // En este caso como el elemento es dinamico, creo el bind del click asi, para que lo detecte.
    $(document).on("click", 'button[data-id="new-pass-save"]', function () {
// Valido que inputs no esten vacios y que coincidan las claves
        if (($('input[data-id="new-pass-input"]').val().length !== 0 && $('input[data-id="new-pass-repeat-input"]').val().length !== 0) && ($('input[data-id="new-pass-input"]').val() === $('input[data-id="new-pass-repeat-input"]').val())) {
            var form = $('form[data-form="psubmit"]');
            $.ajax({
                type: "POST",
                url: 'src/new_pass.php?step=savePass',
                // Serializa los elementos del form
                data: form.serialize(),
                success: function (data)
                {
                    var resp = $.parseJSON(data);
                    if (resp.state === '1') { // Si la clave se modifico correctamente
                        showAlert(resp.response, "correcto", '5000');
                        setTimeout(function () {
                            window.location.href = server_root_extra;
                        }, 3000);
                    } else { // Si ocurrio algun error
                        showAlert(resp.response, "error", '5000');
                    }
                }
            });
        } else { // Si alguno de los inputs esta vacio
            if ($('input[data-id="new-pass-input"]').val().length === 0 || $('input[data-id="new-pass-repeat-input"]').val().length === 0) {
                if ($('input[data-id="new-pass-input"]').val().length === 0) { // Si el input pass esta vacio
                    $('div[data-id="new-pass"]').effect("shake");
                }
                if ($('input[data-id="new-pass-repeat-input"]').val().length === 0) { // Si el input repeat-pass esta vacio
                    $('div[data-id="new-pass-repeat"]').effect("shake");
                }
            } else { // Si las claves no coinciden
                if ($('input[data-id="new-pass-input"]').val() !== $('input[data-id="new-pass-repeat-input"]').val()) {
                    showAlert("Los claves no coinciden", 'error');
                }
            }
        }
    });
    $('button[data-id="login-back"]').click(function () { // Click en el boton volver
        var from = $(this).attr('data-from');
        $('form[data-form="msubmit"]').removeAttr('data-validate'); // Quito al form la instruccion de lo que tiene que validar
        $('button[data-id="login-next"]').fadeIn(500); // Muestro el boton de siguiente
        $('span[data-id="login-subtitle"]').html('Matrícula'); // Cambio el subtitulo del contenedor
        $('div[data-id="login-tipo-matricula"]').show("slide", {direction: "left"}, 100); // Muestro tipo matricula
        $('div[data-id="login-nro-matricula"]').show("slide", {direction: "left"}, 100); // Muestro nro matricula
        $('button[data-id="login-submit"]').hide().addClass("d-none"); // Oculto boton de login
        $('button[data-id="login-back"]').hide().addClass("d-none"); // Oculto el boton de volver
        $('button[data-id="login-back"]').removeAttr('data-from'); // Quito el atrib al boton para saber con que elementos trabajar después
        $('button[data-id="login-help"]').fadeIn(500); // Muestro el boton de ayuda

        if (from === 'pass') {
            $('div[data-id="login-pass"]').hide().addClass("d-none"); // Oculto el campo pass
        } else {
            $('div[data-id="login-dni"]').hide().addClass("d-none"); // Oculto el campo dni
        }
    });
    $('button[data-id="login-next"]').click(function (e) { // Click en boton siguiente
        e.preventDefault(); // Evita se ejecute el form normalmente
        if ($('#inputGroupSelect01').val().length !== 0 && $('input[data-id="login-input-nro-matricula"]').val().length !== 0) { // Si el input tipo y nro matricula NO están vacios
//        if ($('#inputGroupSelect01').val().length === 0 && $('input[data-id="login-input-nro-matricula"]').val().length === 0) { // Si el input tipo y nro matricula NO están vacios
            var form = $('form[data-form="msubmit"]'); // Variable para obtener el form
            $.ajax({
                type: "POST",
                url: 'src/validate.php',
                // Serializa los elementos del form
                data: form.serialize(),
                success: function (data)
                {
                    if (data === 'password') { // Si esta registrado, entonces solicito el pass
                        $('button[data-id="login-help"]').hide(); // Oculto el boton de ayuda
                        $('button[data-id="login-next"]').hide(); // Oculto el boton de siguiente
                        $('span[data-id="login-subtitle"]').html('Ingrese su Clave'); // Cambio el sub titulo del contenedor
                        $('div[data-id="login-tipo-matricula"]').hide(); // Oculto campo tipo matricula
                        $('div[data-id="login-nro-matricula"]').hide(); // Oculto campo nro matricula
                        $('div[data-id="login-pass"]').hide().removeClass("d-none").show("slide", {direction: "right"}, 100, function () { // Muestro campo para ingresar pass
                            $('input[data-id="login-input-pass"]').focus(); // Focus en el input de pass
                            $('input[data-id="login-input-dni"]').val(''); // Limpio el input de dni por las dudas
                        });
                        $('button[data-id="login-submit"]').hide().removeClass("d-none").fadeIn(500); // Muestro boton de login
                        $('button[data-id="login-back"]').hide().removeClass("d-none").fadeIn(500); // Muestro boton de volver
                        $('button[data-id="login-back"]').attr('data-from', 'pass'); // Agrego un atrib al boton para saber con que elementos trabajar después
                        $('form[data-form="msubmit"]').attr('data-validate', 'pass'); // Indico al form que tiene que validar por PASS y NO por DNI
                    } else if (data === 'dni') { // Si NO esta registrado, entonces solicito el dni
                        $('button[data-id="login-help"]').hide(); // Oculto el boton de ayuda
                        $('button[data-id="login-next"]').hide(); // Oculto el boton de siguiente
                        $('span[data-id="login-subtitle"]').html('Ingrese su DNI'); // Cambio el sub titulo del contenedor
                        $('div[data-id="login-tipo-matricula"]').hide(); // Oculto campo tipo matricula
                        $('div[data-id="login-nro-matricula"]').hide(); // Oculto campo nro matricula
                        $('div[data-id="login-dni"]').hide().removeClass("d-none").show("slide", {direction: "right"}, 100, function () { // Muestro campo para ingresar pass
                            $('input[data-id="login-input-dni"]').focus(); // Focus en el input de pass
                            $('input[data-id="login-input-pass"]').val(''); // Limpio el input de pass por las dudas
                        });
                        $('button[data-id="login-submit"]').hide().removeClass("d-none").fadeIn(500); // Muestro boton de login
                        $('button[data-id="login-back"]').hide().removeClass("d-none").fadeIn(500); // Muestro boton de volver
                        $('button[data-id="login-back"]').attr('data-from', 'dni'); // Agrego un atrib al boton para saber con que elementos trabajar después
                        $('form[data-form="msubmit"]').attr('data-validate', 'dni'); // Indico al form que tiene que validar por DNI y NO por PASS
                    } else if (data === '1') { // 1 = ERROR, la matricula no existe
                        // Llamo a la funcion que muestras los errores
                        showAlert("Los datos ingresados no son válidos", 'error');
                    } else {
                        // Si no devuelve ninguna de las opciones esperadas, informo un error.
                        showAlert("Hubo un error en el sistema. Contacte al administrador", 'error');
                    }
                },
                complete: function () {
                    // Al finalizar el ajax, asigno el evento a los botones
                    $('div[data-button="print"]').unbind().click(function (e) {
                        e.preventDefault();
                        var action = $(this).attr('data-action');
                        // Llamo a la funcion que controla el nuevo formulario
                        submitFormMat(action);
                    });
                }
            });
        } else { // Si input tipo y nro matricula están vacios 
            if ($('#inputGroupSelect01').val().length === 0) { // Tipo matricula
                $('div[data-id="login-tipo-matricula"]').effect("shake");
            }
            if ($('input[data-id="login-input-nro-matricula"]').val().length === 0) { // Nro matricula
                $('div[data-id="login-nro-matricula"]').effect("shake");
            }
        }
        $('span[data-id="recover-pass"]').unbind().click(function (e) { // Boton recuperar clave
            e.preventDefault();
            recoverPass(); // Llamo a la funcion que maneja la recuperación de clave
        });
    });
    $('button[data-id="login-help"]').click(function (e) { // Cuando hago click en el boton de Ayuda
        e.preventDefault(); // Evita se ejecute el form normalmente
        window.open('help', '_blank');
    });
    // Para que funcione todo lo que esta dentro de la funcion submitFormMat hay que agregar esto en el archivo 
    // extranet_ajax.php [que se encuentra en la carpeta www/cpppc] debajo de la linea que declara el header()
    // ----------------------------------------
    // foreach ($_REQUEST as $post => $value) {
    // $post = trim(base64_decode($post));
    // $value = trim(base64_decode($value));
    // $$post = $value;
    // }
    // ----------------------------------------
    function submitFormMat(action) {
        var formUrl = "";
        var form = $('form[data-form="mmatriculado"]');
        // Encripto en base64 los datos para que no sean legibles en la url
        var actionUrl = $.base64.encode("pagina") + "=" + $.base64.encode(action);
        // Cuando se dispara el form
        form.one("submit", function (e) {
            e.preventDefault(); // evita se ejecute el form normalmente
            // Obtengo todos los datos del form
            var formSerialize = form.serializeArray();
            // Por cada nombre de campo y su valor, lo encripto en base64 los datos para que no sean legible en la url
            $.each(formSerialize, function (i, field) {
                formUrl += "&" + $.base64.encode(field.name) + "=" + $.base64.encode(field.value);
            });
            // Armo la URL
            var url = '../..' + server_root_intra + '/extranet_ajax.php?' + actionUrl + formUrl;
            switch (action) {
                case "imprimir_credencial": // Si es una credencial
                    var msg = "Generando su credencial, aguarde por favor";
                    showAlert(msg, 'info', '2000000', 'fa-spinner fa-spin');
                    credential(url, '0');
                    break;
                case "pay_online": // Si es pago de cuotas Online
                    window.location = 'solicitudes/quota';
                    break;
                case "personal-data-list":
                    window.location = 'solicitudes/personal_data';
                    break;
                default:
                    // Abro la URL en una nueva pestaña
                    window.open(url, '_blank');
            }
        });
        // Disparo el form
        form.submit();
    }

    // Funcion para mostrar los errores. La declaro diferente al resto. Esto es para que sea global
    window.showAlert = function (msg, type = "info", duration = '2000', icon = 'default', isDebug = '0') {
        // Si isDebug esta seteado en 1 (uno), no mostramos el error verdadero en el msg, sino en la consola.
        if (isDebug === '1') {
            console.log(msg);
            msg = "Ha ocurrido un error, por favor notifique a administración";
        }
        var notif = $('div[data-id="alert-notif"]');
        var overlay = $('div[data-id="overlay"]');
        // Limpio las posibles animaciones que se estén ejecutando anteriormente
        notif.stop(true);
        overlay.stop(true);
        // Cerrar forzosamente
        if (duration === 'close') {
            notif.dequeue().fadeOut("slow");
            overlay.dequeue().fadeOut("slow");
            return false;
        }
        // Opciones de type: correcto | info | error
        type = type.replace('info', 'primary');
        type = type.replace('correcto', 'success');
        type = type.replace('error', 'danger');
        var nIcon = ((icon === 'default') ? '<i class="fas ' + ((type === 'primary') ? 'fa-info-circle' : ((type === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle')) + '"></i> ' : '<i class="fas ' + icon + '"></i> ');
        var nMsg = nIcon + msg;
        // Creo la notificacion
        notif.removeClass();
        notif.addClass('alert alert-' + type + ' alert-style');
        notif.html(nMsg);
        overlay.fadeIn(300);
        notif.fadeIn(function () {
            $(this).delay(duration).fadeOut("slow");
            overlay.delay(duration).fadeOut("slow");
        });
    };
    function credential(url, download) {

        // Si la accion es descargar
        // Abro una nueva pagina con la URL indicada arriba "url"
        if (download === '1') {
            // Agrego el valor download a la url
            url += "&" + $.base64.encode("download") + "=" + $.base64.encode(download);
            window.open(url, '_blank');
            return false;
        }
        $.ajax({
            type: "POST",
            url: url,
            success: function (data)
            {
                // data = 0 es error general || data = 1 = 2 = 3 es error en imagen perfil. Cada numero es un regional
                if (data === '0' || data === '1' || data === '2' || data === '3') {
                    // Armo todos los mensajes
                    var messageCred = "No se puede generar la Credencial porque no contamos con tu foto.<br/>";
                    var messageReg;
                    var messageDesc = "<br/>- La foto debe ser de tipo foto-carnet.<br/>";
                    messageDesc += "- Debe tener 300 píxeles de ancho por 300 píxeles de alto. <br/>";
                    messageDesc += "- No debe pesar más de 500 KBytes. <br/>";
                    messageDesc += "<span class='fz-13px bb-bottom-descrip'>Luego de que generes una solicitud, el personal administrativo del Colegio controlará y validará la misma. Esto proceso puede demorar hasta 72 horas hábiles.</span>"
                    switch (data) {
                        case '0':
                            // Llamo a la funcion que muestras los errores y corto la ejeccion con un return false;
                            showAlert("No cumple con las condiciones para generar su credencial. Comuníquese con Adminsitración", 'error', '5000');
                            return false;
                            break;
                        case '1':
                            // Regional 1 Córdoba
                            messageReg = "Ingresá a la sección <i class='fas fa-list-alt'></i> <b>Solicitud de Datos personales</b>. Recordá que debes cumplir con los siguientes requisitos: ";
                            break;
                        case '2':
                            // Regional 2 Río IV
                            messageReg = "Ingresá a la sección <i class='fas fa-list-alt'></i> <b>Solicitud de Datos personales</b>. Recordá que debes cumplir con los siguientes requisitos: ";
                            break;
                        case '3':
                            // Regional 3 Villa María
                            messageReg = "Ingresá a la sección <i class='fas fa-list-alt'></i> <b>Solicitud de Datos personales</b>. Recordá que debes cumplir con los siguientes requisitos: ";
                            break;
                        default:
                            break;
                    }
                    // Concateno para armar el mensaje
                    var ret = messageCred + messageReg + messageDesc;
                    // Llamo a la funcion que levanta el bootbox
                    // Y cierro el alert de "Generando Credencial"
                    showAlert('', 'info', 'close');
                    botbooxCredPhoto(ret);
                } else {
                    // Oculto el Alert
                    showAlert('', 'info', 'close');
                    // Si no hay errores, genero la credencial
                    botbooxcred(data, url);
                }
            }
        });
    }

    // Cuando se dispara el form
    $('form[data-form="verified-submit"]').submit(function (e) {
        e.preventDefault(); // Evita se ejecute el form normalmente
        var form = $(this);
        $.ajax({
            type: "POST",
            url: '../src/verificar.php',
            // Serializa los elementos del form
            data: form.serialize(),
            success: function (data)
            {
                // data = 1 es error
                if (data === '1') {
                    // Llamo a la funcion que muestras los errores
                    showAlert("Los datos ingresados no son válidos", 'error');
                } else {
                    // Si no hay errores, cargo el contenido en la misma pagina
                    $('div[data-id="container"]').html(data);
//                    console.log(data);
                }
            }
        });
    });
    // Funcion para recuperar clave

    function recoverPass() {
        var tipoM = $('select[data-id="login-input-tipo-matricula"]').val();
        var nroM = $('input[data-id="login-input-nro-matricula"]').val();
        var codMember = "";
        console.log('TIPO M' + tipoM);
        console.log('NRO M' + nroM);
        $.ajax({
            type: "POST",
            url: 'src/validate.php',
            data: 'step=checkMail&action=recoverPass&t_matricula=' + tipoM + '&n_matricula=' + nroM,
            success: function (rsp) {
                var resp = $.parseJSON(rsp);
                codMember = resp.codMember;
                if (resp.exist === "N") {
                    var title = "<i class='fas fa-envelope-open-text'></i> Es necesaria una dirección de Correo";
                    var msg = "<div class='ml-3 mr-3'>Para poder recuperar su clave, necesitamos por favor que nos indiques una dirección de Correo. ";
                    msg += resp.response + "</div>";
                    var buttonsMail = {
                        confirm: {
                            label: 'Enviar Datos <i class="far fa-paper-plane"></i>',
                            className: 'd-none'
                        },
                        cancel: {
                            label: '<i class="far fa-times-circle"></i> Cancelar',
                            className: 'btn-danger'
                        }
                    }
                } else {
                    var title = "<i class='fas fa-envelope-open-text'></i> Confirme su dirección";
                    var msg = "Enviaremos un correo con las instrucciones necesarias para recuperar su clave a la siguiente dirección: <br/>";
                    msg += "<h5 class='position-absolute text-center w-100 mt-4'>" + resp.response + "</h5><br/>";
                    msg += "<p class='small mt-5 mb-0 text-right'>Revise su casilla de correo. Puede que el mail se encuentre en la carpeta de correo no deseado o spam. <br/> Si desea cambiar la dirección de correo asociada. Por favor comuníquese con administración.</p>";
                    var buttonsMail = {
                        confirm: {
                            label: 'Enviar Datos <i class="far fa-paper-plane"></i>',
                            className: 'btn-success send-data-mail'
                        },
                        cancel: {
                            label: '<i class="far fa-times-circle"></i> Cancelar',
                            className: 'btn-danger'
                        }
                    }
                }
                var dialogMail = bootbox.confirm({
                    title: title,
                    message: msg,
                    backdrop: true,
                    size: 'large',
                    buttons: buttonsMail,
                    callback: function (result) {
                        if (result) { // Si doy al boton Enviar
                            showAlert('Enviando datos de recuperación...', "info", '100000'); // Le seteo un tiempo alto para que no se cierre sola
                            if ($(this).is(":not(:disabled)")) { // Si esta habilitado el boton
                                $.ajax({
                                    type: "POST",
                                    url: 'src/validate.php',
                                    data: 'step=sendMail&data=' + codMember,
                                    success: function (data)
                                    {
                                        var resp = $.parseJSON(data);
                                        showAlert('', 'info', 'close'); // Cierro la notificación anterior.
                                        if (resp.state === '0') { // Si el mail no se pudo enviar
                                            showAlert(resp.response, "error", '10000');
                                        } else { // Si el mail se envio correctamente
                                            showAlert(resp.response, "correcto", '10000');
                                            $('div[data-id="message-register"]').remove();
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
            }
        });
    }

    // Si existe el elemento con id codigo
    if (elemExist('codigo')) {
        var code = document.getElementById('codigo');
        // Le doy formato de cada 4 caracteres agrego un guion
        var cleave = new Cleave('#codigo', {
            delimiter: '-',
            blocks: [4, 4, 4, 4],
            uppercase: true
        });
        // Esto es para cuando se lee el QR.
        // Desde verificar.php en la raiz, se carga automaticamente el value en el campo codigo
        // Entonces si cuando se completa el $(document).ready tiene algun value, se dispara el form
        if (code.value.length !== 0) {
            $('form[data-form="verified-submit"]').submit();
        }
    }

    // Funcion para validar si es un mail valido
    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    // Cuando se presiona el boton de cerrar sesion
    $("span[data-id='member-logout']").click(function () {
        $.ajax({
            cache: false,
            type: 'post',
            url: "mvc/index.php",
            data: '_c_=logout',
            success: function (rsp) {
                if (rsp !== '') {
                    window.location = rsp;
                }
            }
        });
    });
    $("span[data-id='member-logout-two']").click(function () {
        $.ajax({
            cache: false,
            type: 'post',
            url: "../mvc/index.php",
            data: '_c_=logout',
            success: function (rsp) {
                console.log(rsp);
                if (rsp !== '') {
                    window.location = rsp;
                }
            }
        });
    });
    $("span[data-id='go-back-button']").click(function () {
        window.history.back();
    });
// Boton Registrarme
// En este caso como el elemento es dinamico, creo el bind del click asi, para que lo detecte.
    $(document).on("click", 'button[data-ref="sign-up"]', function () {
        var codMember = $(this).attr('data-id');
        var valMail = false; // Por defecto no valido el campo mail
        $.ajax({
            type: "POST",
            url: 'src/validate.php',
            data: 'step=checkMail&data=' + codMember,
            success: function (rsp) {
                var resp = $.parseJSON(rsp);
                valMail = true; // Seteo en true para validar el campo mail debajo
                if (resp.exist === "N") {
                    var title = "<i class='fas fa-envelope-open-text'></i> Es necesaria una dirección de Correo";
                    var msg = "<div class='ml-3 mr-3'>Para poder finalizar con su registro, necesitamos por favor que nos indiques una dirección de Correo. ";
                    msg += resp.response + "</div>";
                    var buttonsMail = {
                        confirm: {
                            label: 'Enviar Datos <i class="far fa-paper-plane"></i>',
                            className: 'd-none'
                        },
                        cancel: {
                            label: '<i class="far fa-times-circle"></i> Cancelar',
                            className: 'btn-danger'
                        }
                    }
                } else {
                    valMail = false; // Seteo en false para NO validar el campo mail debajo
                    var title = "<i class='fas fa-envelope-open-text'></i> Confirme su dirección";
                    var msg = "Para poder finalizar con su registro, enviaremos los datos a la siguiente dirección de Correo: <br/>";
                    msg += "<h5 class='position-absolute text-center w-100 mt-4'>" + resp.response + "</h5><br/>";
                    msg += "<p class='small mt-5 mb-0 text-right'>Si desea modificar su dirección, comuníquese con administración.</p>";
                    var buttonsMail = {
                        confirm: {
                            label: 'Enviar Datos <i class="far fa-paper-plane"></i>',
                            className: 'btn-success send-data-mail'
                        },
                        cancel: {
                            label: '<i class="far fa-times-circle"></i> Cancelar',
                            className: 'btn-danger'
                        }
                    }
                }
                var dialogMail = bootbox.confirm({
                    title: title,
                    message: msg,
                    backdrop: true,
                    size: 'large',
                    buttons: buttonsMail,
                    callback: function (result) {
                        var codMember = $('button[data-ref="sign-up"]').attr('data-id'); // Obtengo el codmember
                        var inputMail = "";
                        if (result) { // Si doy al boton Enviar
                            showAlert('Enviando datos...', "info", '100000'); // Le seteo un tiempo alto para que no se cierre sola
                            if ($(this).is(":not(:disabled)")) { // Si esta habilitado el boton
                                if (valMail) { // Si es un mail que se ingreso manualmente
                                    inputMail = $('input[data-id="sign-up-email"]').val(); // Obtengo el valor del input
                                }
                                $.ajax({
                                    type: "POST",
                                    url: 'src/validate.php',
                                    data: 'step=sendMail&data=' + codMember + "&inputMail=" + inputMail,
                                    success: function (data)
                                    {
                                        var resp = $.parseJSON(data);
                                        showAlert('', 'info', 'close'); // Cierro la notificación anterior.
                                        if (resp.state === '0') { // Si el mail no se pudo enviar
                                            showAlert(resp.response, "error", '10000');
                                        } else { // Si el mail se envio correctamente
                                            showAlert(resp.response, "correcto", '10000');
                                            $('div[data-id="message-register"]').remove();
                                        }
                                    }
                                });
                            }
                        }
                    }
                });
                // Cuando se abre el bootbox
                dialogMail.on('shown.bs.modal', function () {
                    $('input[data-id="sign-up-email"]').focus(); // Hago focus en el campo de email.
                    if (valMail) { // Deshabilito el boton de enviar siempre y cuando haya que ingresar un mail manualmente
                        $('.send-data-mail').attr('disabled', 'disabled'); // Deshabilito el boton de enviar
                        $('.send-data-mail').addClass('disabled'); // Deshabilito el boton de enviar
                    }
                });
                $(document).on("keyup", 'input[data-id="sign-up-email"]', function () { // Mientras escribo valido si es un mail valido
                    if (isEmail($(this).val())) { // Si, si lo es
//                        console.log('YES');
                        $('.send-data-mail').removeAttr('disabled'); // Habilito el boton de enviar
                        $('.send-data-mail').removeClass('disabled'); // Habilito el boton de enviar
                    } else {
//                        console.log('NO');
                        $('.send-data-mail').attr('disabled', 'disabled'); // Deshabilito el boton de enviar
                        $('.send-data-mail').addClass('disabled'); // Deshabilito el boton de enviar
                    }
                });
            }
        });
    });
// Cuando se presiona el boton de modificar clave
    $("span[data-id='modify-password']").click(function () {
        var directoryPsw = "";
        loadChangePassTemplate(directoryPsw);
    });
    $("span[data-id='modify-password-two']").click(function () {
        var directoryPsw = "../";
        loadChangePassTemplate(directoryPsw);
    });
    window.formatCurrency = function (num)
    {
        num = num.toString().replace(/\$|\,/g, '');
        if (isNaN(num))
            num = 0;
        var signo = (num == (num = Math.abs(num)));
        num = Math.floor(num * 100 + 0.50000000001);
        centavos = num % 100;
        num = Math.floor(num / 100).toString();
        if (centavos < 10)
            centavos = '0' + centavos;
        for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
            num = num.substring(0, num.length - (4 * i + 3)) + num.substring(num.length - (4 * i + 3));
        return (((signo) ? '' : '-') + num + '.' + centavos);
    };
    ////////////////////////////////////////////////////////////////////////
    // Controlador de checkboxes.
    // Espera 2 parametros: 
    //      - El primero, el atributo data-id de el/los checkbox/es a 'vigilar'
    //      - El segundo, el #id del elemento que va a recibir el resultado
    //      ----------------------------------------------------------------
    // Return esperado:
    //      - Cantidad numerica de checkboxes tildados
    ////////////////////////////////////////////////////////////////////////

    // Funcion para evitar que se clickee dos veces un elemento
    window.checkBoxController = function (checkboxDataId, elementResponseId, getQuotesCheckboxElement) {

        var $checkboxes = $("input[data-id='" + checkboxDataId + "']");
        $checkboxes.on('change', function () {
            var countCheckedCheckboxes = $checkboxes.filter(':checked').length;
            var checkedCheckboxes = $checkboxes.filter(':checked');
            $('#' + elementResponseId).html(countCheckedCheckboxes);
            $('#' + elementResponseId).text(countCheckedCheckboxes);
            // Funcion custom para las cuotas
            getQuotesCheckboxElement(checkedCheckboxes);
        });
    };
});
