<?php

require_once 'models/general.mdl.php';

class GeneralCtrlr {

    private $model;

    // ********************************** CONSTRUCTOR **************************************** //
    public function __CONSTRUCT() {
        $this->model = new GeneralMdl();
    }

    // ********************************** Index **************************************** //
    public function Index() {
        global $conn;
        $requestData = $_REQUEST;

        require_once 'header.view.php';
        //require_once 'src/dosimetria_acumulada.view.php';
        require_once 'footer.view.php';
    }

    public function getTipoDni() {
        $result = $this->model->getTipoDni();
        echo json_encode($result);
    }

    public function getEstadoCivil() {
        $result = $this->model->getEstadoCivil();
        echo json_encode($result);
    }

    public function getProvincias() {
        $result = $this->model->getProvincias();

        $asd = $this->utf8_encode_recursive($result[datos]);

        $arr_ret = array();

        if ($result[estado] == false) {
            $arr_ret = array("estado" => false, "descripcion" => htmlentities((string) $result[descripcion], ENT_COMPAT | ENT_HTML401, "ISO8859-1"), 'datos' => array());
        } else {

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $this->utf8_encode_recursive($result[datos]));
        }

        $json_data_encoded = json_encode($arr_ret);

        echo $json_data_encoded;  // send data as json format
    }

    public function getLocalidades() {
        $idProv = $_REQUEST['_d_'];
        $result = $this->model->getLocalidades($idProv);

        $arr_ret = array();

        if ($result[estado] == false) {
            $arr_ret = array("estado" => false, "descripcion" => htmlentities((string) $result[descripcion], ENT_COMPAT | ENT_HTML401, "ISO8859-1"), 'datos' => array());
        } else {
            $this->formatearFilas($result[datos]);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $this->utf8_encode_recursive($result[datos]));
        }

        $json_data_encoded = json_encode($arr_ret);

        echo $json_data_encoded;  // send data as json format
    }

    public function utf8_encode_recursive($code) {
        if (is_array($code)) {
            foreach ($code as &$c) {
                $c = $this->utf8_encode_recursive($c);
            }
            return $code;
        }
        return utf8_encode((string) $code);
    }

    public function formatearFilas(&$rows) {
        foreach ($rows AS &$row) {
            //Asignar htmlentities para que JSON no falle
            foreach ($row AS &$campo) {
                $campo = htmlentities((string) $campo);
                //FIN Asignar htmlentities para que JSON no falle
            }
        }
    }

}
