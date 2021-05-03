<?php

require_once 'models/paypertic.mdl.php';

class PayperticCtrlr {

    private $model;

    // ********************************** CONSTRUCTOR **************************************** //
    public function __CONSTRUCT() {
        $this->model = new PayperticMdl();
    }

    // ********************************** Index **************************************** //
    public function Index() {
        global $conn;
        $requestData = $_REQUEST;

//        require_once 'header.view.php';
//        require_once 'footer.view.php';
    }

    public function endpoint() {
        $result = $this->model->endpoint();

        $arr_ret = array();

        if ($result[estado] == false) {
            $arr_ret = array("estado" => false, "descripcion" => htmlentities((string) $result[descripcion], ENT_COMPAT | ENT_HTML401, "ISO8859-1"), 'datos' => array());
        } else {
            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $result[datos]);
        }

        $json_data_encoded = json_encode($arr_ret);

        echo $json_data_encoded;  // send data as json format
    }

}
