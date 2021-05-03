<?php

require_once '../setupfolder/db_param.php';
require_once '../setupfolder/sys_param.php';

// Obtenemos el controlador que queremos cargar
if (!isset($_REQUEST['_c_'])) {
    $_c_ = 'error';
} else {
    $_c_ = $_REQUEST['_c_'];
}
$nombre_controlador = $_c_;

if ($nombre_controlador == 'error') {
    require_once "views/error.php";  // Pantalla datos personales
} else {

    switch ($nombre_controlador) {
        case "logout":
            $cm_session = $nombre_controlador;
            break;
        case "paypertic":
            $cm_session = $nombre_controlador;
            break;
        default:
            $cm_session = $_SESSION["cod_member"];
    }

    switch (empty($cm_session)) {
        case 1: // Si no existe la sesion
            session_destroy();
            session_unset();
            header("Location: " . $cfg['server_root_extra'] . "/login");
            break;

        case "": // Si la sesion tiene un valor y es diferente al valor logout
            if (($cm_session != "" && $cm_session != 'logout') || $cm_session == "paypertic") {
                // Creamos el controlador
                require_once "ctrlrs/$nombre_controlador.ctrlr.php";

                //Pasa el nombre del controlador a una frase con espacios
                $nombre_controlador = str_replace("_", " ", $nombre_controlador);
                //Convierte a maysculas la primera letra de cada palabra
                $nombre_clase_controlador .= str_replace(" ", "", ucwords(" " . $nombre_controlador)) . "Ctrlr";

                $_controller = new $nombre_clase_controlador;

                // ***************************************************************
                // Obtenemos la accion a ejecutar
                $_accion = isset($_REQUEST['_a_']) ? $_REQUEST['_a_'] : 'index';

                //Pasa el nombre de accion a una frase con espacios
                $nombre_accion = str_replace("_", " ", $_accion);
                //Convierte a maysculas la primera letra de cada palabra
                $nombre_metodo_accion .= str_replace(" ", "", ucwords(" $nombre_accion"));

                // Llama la accion
                $_controller->$nombre_metodo_accion();
            } else if ($cm_session == "logout") {
                //finalizar la sesion
                session_destroy();
                session_unset();
//            session_unregister($cm_session);
                echo $cfg['server_root_extra'] . '/login';
            }
            break;
    }
}
?>
