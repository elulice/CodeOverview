<?php

session_start();

require_once "../setupfolder/sys_param.php";
require_once "../setupfolder/db_param.php";
require_once "../setupfolder/fun_param.php";

$showFirstButton = "d-none";
$showSecondButton = "d-none";
$dontShowInfo = "";

function showButton($member) {
//    global $c_database;
//    global $link;
    global $conn;

    // Valido si muestro boton para cupon anual
    $mes_actual = date('m');
    $anio_actual = date('Y');
    $mostrar_boton = false;

    if (($mes_actual == 05 OR $mes_actual == 06) AND $anio_actual == 2015) { //05 mayo
        $qrystr_cuot = "SELECT * FROM `cuotas`
                          WHERE tipo_cuota IN(1,4) AND campania <1241 AND pagada =0 AND activa =1 AND member ='$member'";

        $data = $conn->query($qrystr_cuot);
        verificar($qrystr_cuot);
        $tiene_anteriores_impagas = $data->fetchColumn();

//        $qry_cuot = mysql_db_query($c_database, $qrystr_cuot, $link);
//        verificar($qrystr_cuot);
//        $tiene_anteriores_impagas = mysql_num_rows($qry_cuot);

        $qrystr_cuot2 = "SELECT * FROM `cuotas`
                          WHERE tipo_cuota IN(1,4) AND campania >=1241 AND pagada =1 AND member ='$member'";

        $data2 = $conn->query($qrystr_cuot2);
        verificar($qrystr_cuot);
        $tiene_posteriores_pagas = $data2->fetchColumn();

//        $qry_cuot2 = mysql_db_query($c_database, $qrystr_cuot2, $link);
//        verificar($qrystr_cuot2);
//        $tiene_posteriores_pagas = mysql_num_rows($qry_cuot2);

        if ($tiene_anteriores_impagas == 0 and $tiene_posteriores_pagas == 0) {
            $mostrar_boton = true;
        }
    }
    return $mostrar_boton;
}

if ((!isset($_SESSION['cod_member']) && empty($_SESSION['cod_member']))) { // Si no existe la session
// Creo una variable por cada post con el mismo nombre que trae del form
    foreach ($_REQUEST as $post => $value) {
        $$post = $value;
    }

// ----------------------------------
// Datos harcodeados solo para prueba
// ----------------------------------    
//    $tipo_matricula = "P";
//    $matricula = "144154";
//    $dni = "36185335";
// ----------------------------------    
// ----------------------------------
// Si viene del boton omitir, entonces la pass esta encriptada
// Por ende indico en la consulta que el campo para comprar tambien tiene que estar encriptado
    if ($isEncrypted == '1' && !empty($password)) {
        $psw_extra = 'md5(psw_extra)';
        $cod_member = base64_decode($cod_member);
        $wherePsw = "ma.member = '$cod_member'";
    } else {
        $matricula = $tipo_matricula . $matricula;
        $psw_extra = 'psw_extra';
        $wherePsw = "ma.nro_matricula = '$matricula'";
    }
// Validar datos
    if ($tipo_matricula == 'ADH') {
        $qrystr_data = "SELECT *, CONCAT('ADH',nro_adherente) as nro_matricula ";
        $qrystr_data .= "FROM member ";
        $qrystr_data .= "WHERE nro_adherente = '$matricula' AND ";
        $qrystr_data .= (empty($password)) ? "nro_doc = '$dni' " : "psw_extra = '$password' "; // Si el campo password viene vacio, entonces busco los datos por dni y matricula
    } else {

        $qrystr_data = "SELECT ma.nro_matricula, me.* ";
        $qrystr_data .= "FROM matriculas AS ma ";
        $qrystr_data .= "INNER JOIN member AS me ON ma.member = me.cod_member ";
        $qrystr_data .= "WHERE $wherePsw AND ";
        $qrystr_data .= (empty($password)) ? "me.nro_doc = '$dni' " : "$psw_extra = '$password' "; // Si el campo password viene vacio, entonces busco los datos por dni y matricula
        $qrystr_data .= "AND ma.estado IN(20,30)";
    }

    $qry_data = $conn->query($qrystr_data);
    verificar($qrystr_data);
    $row_datos = $qry_data->fetch(PDO::FETCH_ASSOC);

//    echo $qrystr_data;
//    exit;

    if (sizeof($row_datos) <= 1) { // Si la query no trae datos
        //    ERROR: Los datos ingresados no son válidos
        doLog('NO EXISTE', "MATRICULADOS EXTRANET", "CONSULTA DE MATRICULA. DATOS INCORRECTOS", "CONSULTA DE MATRICULA. DATOS INCORRECTOS.");
        echo 1; // El 1 los espera el archivo main.js
        session_destroy(); // Destruyo sessiones
        session_unset(); // Destruyo sessiones
        die;
    }

    $_SESSION['matricula'] = $row_datos['nro_matricula'];

    $_SESSION['dni'] = $row_datos['nro_doc'];

    $_SESSION['email'] = $row_datos['email'];

    $_SESSION['extranetReg'] = $row_datos["extranet_registrado"];

    $_SESSION['nombre'] = utf8_encode("$row_datos[apellido], $row_datos[nombres]");

    $_SESSION['cod_member'] = $row_datos[cod_member];

    $_SESSION['regional'] = $row_datos[regional];

    $_SESSION['canal_pago_banco_cupon'] = $row_datos[canal_pago_banco_cupon];

    // Si no tiene ningun medio de pago automatico y no es RIO IV (Regional 2), entonces muestro el boton
    if ($row_datos[canal_pago_banco_deb] == 0 && $row_datos[canal_pago_tarjeta] == 0 && $row_datos[regional] != '2') {
        $_SESSION['showPptButton'] = true;
    } else {
        $_SESSION['showPptButton'] = false;
    }

    $_SESSION['canal_pago_tarjeta'] = $row_datos[canal_pago_tarjeta];
} else {
    $matricula = $_SESSION['matricula'];
    $dni = $_SESSION['dni'];
    $extranetReg = $_SESSION['extranetReg'];
    $nombre = $_SESSION['nombre'];
    $member = $_SESSION['cod_member'];
    $regional = $_SESSION['regional'];
    $canal_pago_banco_cupon = $_SESSION['canal_pago_banco_cupon'];
    $showPptButton = $_SESSION['showPptButton'];
}
//SI NO ES CAV Válido llamar a votar_cav con el error especificado
if ($matricula != '' || $_SESSION['matricula'] != '') {
    doLog($member, "MATRICULADOS EXTRANET", "CONSULTA DE MATRICULA. DATOS CORRECTOS", "Cod_M: $member | NroM: $matricula | DNI: $dni");
    // Dependiendo los casos, creo los textos para los botones y los oculto o muestro
    switch ($regional) {
        case '3': // VILLA MARIA
            if ($canal_pago_banco_cupon == 3) { // Banco V Maria
                $showFirstButton = "";
                $action = "imprimir_cupones_banco";
//                $txtButton = "Imprimir Cupones Referidos a Fecha Actual";
                $txtButton = "Cupones de Pago";

//                if (showButton($member)) {
//                    $showSecondButton = "";
//                    $actionSb = "imprimir_cupon_anual";
//                    $txtButtonSb = "Imprimir Cupon de Pago Anual";
//                }
            }
            break;
        case '2': // RIO IV
            $showFirstButton = "";
            $showPptButton = false;
            $action = "imprimir_cupones_banco";
            $txtButton = "Cupones de Pago";
            break;
        case '1': // CORDOBA
            if ($canal_pago_banco_cupon == 1) { // Banco Provincia de Cordoba
                $showFirstButton = "";
                $action = "imprimir_cupones_banco";
                $txtButton = "Cupones de Pago para Banco";
            }
            break;
    }
    include('matriculado.php');
} else {
    //    ERROR: Los datos ingresados no son válidos
    doLog('NO EXISTE', "MATRICULADOS EXTRANET", "CONSULTA DE MATRICULA. DATOS INCORRECTOS", "CONSULTA DE MATRICULA. DATOS INCORRECTOS.");
    echo 1; // El 1 los espera el archivo main.js
    session_destroy(); // Destruyo sessiones
    session_unset(); // Destruyo sessiones
    die;
}
    