function loadChangePassTemplate(directoryPsw) {
    // Creo el div contenedor del template
    // Si existe el DIV lo borro
    if (elemExist('_change_password')) {
        $("#_change_password").remove();
    }
    var _change_password = document.createElement('div');
    _change_password.setAttribute("id", "_change_password");
    _change_password.setAttribute("style", "display:none");
    document.body.appendChild(_change_password);
// Cargo el template e inyecto dentro del div creado previamente
    $("#_change_password").load(
            directoryPsw + "mvc/templates/change_password.html",
            {'rand': Math.random()},
            function () {
                loadChangePass(directoryPsw);
            }
    );
}

function loadChangePass(directoryPsw) {
    var title = "Cambiar Clave";
    var buttons = {
        confirm: {
            label: '<i class="far fa-save"></i> Guardar',
            className: 'btn-success'
        },
        cancel: {
            label: '<i class="far fa-times-circle"></i> Cancelar',
            className: 'btn-danger'
        }
    };

    $.ajax({
        cache: false,
        type: 'post',
        url: directoryPsw + "mvc/index.php",
        data: '_c_=change_password',
        success: function () {

            // Cargo el template e inyecto la informacion
            $('#_change_password').html(tplChangePswForm);
        },
        complete: function () {
            // Guardo el contenido del div en una variable y elimino el div del DOM
            // Esto es para que no haya atributos id ni elementos HTML duplicados
            var dataFromDiv = $('#_change_password').html();
            $("#_change_password").remove();

            // Una vez completado el AJAX asigno el evento
            var dialogPersonalDataForm = bootbox.confirm({
                title: title,
                message: dataFromDiv,
                onEscape: false,
                size: 'large',
                buttons: buttons,
                callback: function (result) {

                    var form = $('form[data-id="change-password-form"]'); // Form de datos
                    form.find('input').prop('disabled', false); // Quito el attributo disabled de todos los inputs justo antes de enviar el form

                    if (result) { // Si doy al boton Enviar Solicitud

                        // Obtengo el valor de los campos de psw y los comparo para saber si son iguales
                        var psw = $('input[data-id="new-pass-input"]').val();
                        var pswB = $('input[data-id="new-pass-input-b"]').val();

                        if (psw !== pswB) {
                            showAlert("Las claves no coinciden", "error", '1500');
                            return false;
                        } else {

                            $.ajax({
                                type: "POST",
                                url: directoryPsw + "mvc/index.php",
                                data: "_c_=change_password&_a_=save&" + form.serialize(),
                                cache: false,
                                success: function (data)
                                {
                                    var respuesta = $.parseJSON(data);
                                    if (respuesta.estado != true)
                                    {
                                        showAlert("Ha ocurrido un error al intentar modificar su clave, por favor comuní­quese con administración", "error", '3000');
                                    } else  //Sin errores de logica de negocio
                                    {
                                        showAlert("Su clave se ha modifcado correctamente", "correcto", '3000');
                                    }
                                }
                            });
                        }
                    }
                }
            });
            dialogPersonalDataForm.on('shown.bs.modal', function () {
                return false;
            });
        }
    });
}