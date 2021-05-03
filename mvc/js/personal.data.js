$(document).ready(function () {
    loadPersonalDataListTemplate();
});

// Funcion para crear la tabla de la lista de solicitudes
// Los parametros esperados son open o close
function createTableList(ret) {
    var tableOpen = '<table class="table table-striped table-bordered table-hover table-sm mt-4 table-responsive-sm"><thead><tr>';
    tableOpen += '<th scope="col">#</th><th scope="col">Fecha Solicitado</th><th scope="col">Fecha Estado</th><th scope="col">Estado</th><th scope="col">Obs</th><th scope="col">Opc</th></tr></thead><tbody>';
    var tableClose = '</tbody></table>';
    return (ret === "open") ? tableOpen : tableClose;
}
function loadPersonalDataListTemplate() {
// Creo el div contenedor del template
    // Si existe el DIV lo borro
    if (elemExist('_personal_data')) {
        $("#_personal_data").remove();
    }
    var _personal_data = document.createElement('div');
    _personal_data.setAttribute("id", "_personal_data");
    _personal_data.setAttribute("style", "display:none");
    document.body.appendChild(_personal_data);
// Cargo el template e inyecto dentro del div creado previamente
    $("#_personal_data").load(
            "../mvc/templates/personal_data.html",
            {'rand': Math.random()},
            function () {
                loadPersonalData();
            }
    );
}

function loadPersonalDataFormTemplate(idSolicitud) {
// Creo el div contenedor del template
// Si existe el DIV lo borro
    if (elemExist('_personal_data_form')) {
        $("#_personal_data_form").remove();
    }
    var _personal_data_form = document.createElement('div');
    _personal_data_form.setAttribute("id", "_personal_data_form");
    _personal_data_form.setAttribute("style", "display:none");
    document.body.appendChild(_personal_data_form);
// Cargo el template e inyecto dentro del div creado previamente
    $("#_personal_data_form").load(
            "../mvc/templates/personal_data_form.html",
            {'rand': Math.random()},
            function () {
                loadPersonalDataForm(idSolicitud);
            }
    );
}

function loadPersonalData() {
    var idSolicitud = 0;
    var idEstado = 0;
    var flag = 0;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=personal_data&_a_=get_list',
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Detalle: " + respuesta.descripcion + " <br/>" + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                var data = respuesta.datos;
                $.each(data, function (i, val) {
                    idSolicitud = val.id_solicitud;
                    idEstado = val.id_estado_solicitud;
                    if (idEstado === '10') { // Si hay algun estado en 10 enciendo el flag para deshabilitar el boton
                        flag = '1';
                    }
                });

                if (idSolicitud !== 0) { // Si existe aunque sea un id_solicitud
                    // Cargo el template e inyecto la informacion
                    $('#_personal_data').html(createTableList('open') + data.map(tplPersonalData).join('') + createTableList('close'));
                    $('div[data-id="card-body-container"]').html($('#_personal_data').html());
                    $('i[data-id="personal-data-view-detail"]').click(function () {
                        var idSolicitudView = $(this).attr('data-request');
                        loadPersonalDataFormTemplate(idSolicitudView);
                    });
                    $('i[data-id="personal-data-view-cancel"]').click(function () {
                        var idSolicitudCancel = $(this).attr('data-request');
                        cancelPersonalDataRequest(idSolicitudCancel);
                    });
                    if (flag === '1') { // Si hay una solicitud pendiente, entonces anulo el boton que genera una nueva
                        $('button[data-id="personal-data-modify"]').attr("disabled", "disabled").addClass('cursor-default disabled').removeAttr('data-id').attr('data-id', 'personal-data-modify-nulled');
                    } else { // Si no hay solicitud pendiente
                        $('button[data-id="personal-data-modify-nulled"]').removeAttr("disabled", "disabled").removeClass('cursor-default disabled').removeAttr('data-id').attr('data-id', 'personal-data-modify');
                    }
                } else {
                    $('div[data-id="card-body-container"]').html("No tenes solicitudes");
                }
            }
        }
    });
}

function loadPersonalDataForm(idSolicitud = "") {
    showAlert('Consultando datos...', "info", '100000'); // Mientras se carga la informacion muestro un mensaje
    var tipoDoc;
    var estadoCivil;
    var idProv;
    var idLoca;
    var title = "Solicitud de Modificación de Datos";
    var buttons = {
        confirm: {
            label: 'Enviar Solicitud <i class="far fa-paper-plane"></i>',
            className: 'btn-success btn-send-solicitud-identifier'
        },
        cancel: {
            label: '<i class="far fa-times-circle"></i> Cancelar',
            className: 'btn-danger'
        }
    };

    if (idSolicitud !== "") {
        buttons = {
            confirm: {
                label: '',
                className: 'd-none'
            },
            cancel: {
                label: '<i class="fas fa-minus-circle"></i> Cerrar',
                className: 'btn-info'
            }
        };
        title = "Solicitud N°: #" + idSolicitud;
    }
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=personal_data&_a_=get_data&_d_=' + idSolicitud,
        success: function (response) {
            var respuesta = $.parseJSON(response);
            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Detalle: " + respuesta.descripcion + " <br/>" + respuesta.detalle, "error", '10000', 'default', '1');
            } else  //Sin errores de logica de negocio
            {
                var data = respuesta.datos;
                tipoDoc = respuesta.datos[0].tipo_doc_id;
                estadoCivil = respuesta.datos[0].estado_civil;
                idProv = respuesta.datos[0].id_provincia;
                idLoca = respuesta.datos[0].id_localidad;
                // Cargo el template e inyecto la informacion
                $('#_personal_data_form').html(data.map(tplPersonalDataForm).join(''));
            }
        },
        complete: function () {

            showAlert('', 'info', 'close'); // Cierro la declarada al principio de la funcion.
            // Una vez completado el AJAX asigno el evento
            var dialogPersonalDataForm = bootbox.confirm({
                title: title,
                message: $('#_personal_data_form').html(),
                onEscape: false,
                size: 'large',
                buttons: buttons,
                callback: function (result) {

                    var form = $('form[data-id="personal-data-form"]'); // Form de datos
                    var formData = new FormData(form[0]); // Creo FormData para traer los archivos.
                    form.find('input').prop('disabled', false); // Quito el attributo disabled de todos los inputs justo antes de enviar el form
                    form.find('select').prop('disabled', false); // Quito el attributo disabled de todos los select justo antes de enviar el form

                    if (result) { // Si doy al boton Enviar Solicitud
//                        showAlert('Generando Solicitud', "info", '100000'); // Le seteo un tiempo alto para que no se cierre sola
                        $.ajax({
                            type: "POST",
                            url: "../mvc/index.php?_c_=personal_data&_a_=save&" + form.serialize(),
                            data: formData,
                            async: true,
                            cache: false,
                            contentType: false,
                            enctype: 'multipart/form-data',
                            processData: false,
                            beforeSend: function () {
                                showAlert('Generando Solicitud. Aguarde por favor...', "info", '10000000000', 'fa-spinner fa-spin'); // Le seteo un tiempo alto para que no se cierre sola
                            },
                            success: function (data)
                            {
                                var respuesta = $.parseJSON(data);
                                showAlert('', 'info', 'close'); // Cierro la notificación anterior.

                                if (respuesta.estado != true)
                                {
                                    showAlert("Ha ocurrido un error al generar su solicitud, por favor comuní­quese con administración", "error", '3000');
                                } else  //Sin errores de logica de negocio
                                {
                                    showAlert("Su solicitud se ha generado correctamente", "correcto", '3000');
                                    loadPersonalDataListTemplate();
                                }
                            }
                        });
                    }
                }
            });
            dialogPersonalDataForm.on('shown.bs.modal', function () {
                $('.modal').animate({scrollTop: 0}, 100, 'swing');
                getTipoDniSelect(tipoDoc, 'tipodoc');
                getEstadoCivilSelect(estadoCivil, 'estadoCivil');
                getProvinciaSelect(idProv, 'prov');
                getLocalidadesSelect(idLoca, idProv, 'loca');
                $('div[data-id="edit-personal-data"]').click(function () {
                    var inputChild = $(this).parent().parent().find('input');
                    if (inputChild.length === 0) {
                        inputChild = $(this).parent().parent().find('select');
                    }
                    var cancelOpt = $(this).next();
                    $(this).addClass('d-none');
                    inputChild.removeAttr('disabled');
                    cancelOpt.removeClass('d-none');
                });
                $('div[data-id="cancel-edit-personal-data"]').click(function () {
                    var inputChild = $(this).parent().parent().find('input');
                    var originalValue = $(this).attr('data-original-value');
                    if (inputChild.length === 0) {
                        inputChild = $(this).parent().parent().find('select');
                    }
                    var editOpt = $(this).prev();
                    $(this).addClass('d-none');
                    inputChild.val(originalValue).change();
                    inputChild.attr('disabled', 'disabled');
                    editOpt.removeClass('d-none');
                });
                $('select[data-id="prov"]').change(function () {
                    $('select[data-id="loca"]').attr('readonly', true);
                    var sIdProv = $(this).val();
                    getLocalidadesSelect(0, sIdProv, 'loca');
                });
                $('input[data-id="personal-data-form-image-input"]').unbind();
                $('input[data-id="personal-data-form-image-input"]').change(function (e) {
                    readURL(this, 'personal-data-form-prev-image');
                });
            });
        }
    });
}

// Opcion predeterminada y el data-id del select donde inyectar la info
function getTipoDniSelect(predet, dataId) {
    var options = "";
    var isSelected;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=general&_a_=get_tipo_dni',
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Descripcion: " + respuesta.descripcion + " | Detalle: " + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                $.each(respuesta.datos, function (i, val) {
                    isSelected = "";
                    if (val.tipo_doc_id === predet) {
                        isSelected = "selected";
                    }
                    options += "<option value='" + val.tipo_doc_id + "' " + isSelected + ">" + val.descri_tipo_doc + "</option>";
                });
            }
            $('select[data-id="' + dataId + '"]').html(options);
        }
    });
}

// Opcion predeterminada y el data-id del select donde inyectar la info
function getEstadoCivilSelect(predet, dataId) {
    var options = "";
    var isSelected;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=general&_a_=get_estado_civil',
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Descripcion: " + respuesta.descripcion + " | Detalle: " + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                $.each(respuesta.datos, function (i, val) {
                    isSelected = "";
                    if (val.id_estado_civil === predet) {
                        isSelected = "selected";
                    }
                    options += "<option value='" + val.id_estado_civil + "' " + isSelected + ">" + val.estado_civil + "</option>";
                });
            }
            $('select[data-id="' + dataId + '"]').html(options);
        }
    });
}

// Opcion predeterminada y el data-id del select donde inyectar la info
function getProvinciaSelect(predet, dataId) {
    var options = "";
    var isSelected;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=general&_a_=get_provincias',
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Descripcion: " + respuesta.descripcion + " | Detalle: " + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                $.each(respuesta.datos, function (i, val) {
                    isSelected = "";
                    if (val.id_provincia === predet) {
                        isSelected = "selected";
                    }
                    options += "<option value='" + val.id_provincia + "' " + isSelected + ">" + val.provincia + "</option>";
                });
            }
            $('select[data-id="' + dataId + '"]').html(options);
        }
    });
}

// Opcion predeterminada, depencia de la provincia asociada a la localidad y el data-id del select donde inyectar la info
function getLocalidadesSelect(predet, id_prov_dependence = "", dataId) {
    var options = "";
    var isSelected;
    $.ajax({
        cache: false,
        type: 'post',
        url: "../mvc/index.php",
        data: '_c_=general&_a_=get_localidades&_d_=' + id_prov_dependence,
        success: function (response) {
            var respuesta = $.parseJSON(response);

            //Si hubo errores de lógica de negocio mostrarlos
            if (respuesta.estado != true)
            {
                showAlert("Descripcion: " + respuesta.descripcion + " | Detalle: " + respuesta.detalle, "error", '10000', 'default', '1');

            } else  //Sin errores de logica de negocio
            {
                $.each(respuesta.datos, function (i, val) {
                    isSelected = "";
                    if (val.id_localidad === predet) {
                        isSelected = "selected";
                    }
                    options += "<option value='" + val.id_localidad + "' " + isSelected + ">" + val.localidad + "</option>";
                });
            }
            $('select[data-id="' + dataId + '"]').html(options);
        },
        complete: function () {
            $('select[data-id="' + dataId + '"]').removeAttr('readonly');
        }
    });
}

function cancelPersonalDataRequest(idSolicitud) {
    bootbox.confirm({
        message: "<h6>Está seguro que desea cancelar su solicitud N°: #" + idSolicitud + "</h6>",
        buttons: {
            confirm: {
                label: 'Confirmar',
                className: 'btn-success'
            },
            cancel: {
                label: 'Cancelar',
                className: 'btn-danger'
            }
        },
        callback: function (result) {
            if (result) {
                $.ajax({
                    cache: false,
                    type: 'post',
                    url: "../mvc/index.php",
                    data: '_c_=personal_data&_a_=cancel&_d_=' + idSolicitud,
                    success: function (response) {
                        var respuesta = $.parseJSON(response);

                        //Si hubo errores de lógica de negocio mostrarlos
                        if (respuesta.estado != true)
                        {
                            showAlert("Descripcion: " + respuesta.descripcion + " | Detalle: " + respuesta.detalle, "error", '10000', 'default', '1');

                        } else  //Sin errores de logica de negocio
                        {
                            showAlert("Su solicitud ha sido cancelada correctamente", "correcto", '3000');
                            loadPersonalDataListTemplate();
                        }
                    }
                });
            }
        }
    });
}

function uploadImagePersonalData() {
    var form = $('form[data-form="personal-data-form-image-upload"]');
    form.unbind();
    form.submit(function (e) {
        e.preventDefault(); // Evita se ejecute el form normalmente
        e.stopImmediatePropagation(); // Evita se ejecute el form normalmente
        var formData = new FormData($(this)[0]);

        $.ajax({
            type: 'POST',
            url: "../mvc/index.php?_c_=personal_data&_a_=upload_image&" + form.serialize(),
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            enctype: 'multipart/form-data',
            processData: false,
            success: function (data) {
                console.log(data);
            }
        });
    });
    return form;
}

// Funcion para previsualizar imagen
function readURL(input, dataIdContainer) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            var imgContainer = $('img[data-id="' + dataIdContainer + '"]');
            imgContainer.attr('src', e.target.result);
            var imagePreviewContainter = imgContainer.parent().parent(); // Div donde se previsualiza la imagen
            imagePreviewContainter.removeClass('d-none'); // Remuevo la clase d-none = display: none
        };

        reader.readAsDataURL(input.files[0]);
    }
}

$('button[data-id="personal-data-modify"]').click(function () {
    loadPersonalDataFormTemplate();
});