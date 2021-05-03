<?php

session_start();
require_once 'models/change_password.mdl.php';

class ChangePasswordCtrlr {

    private $model;

    // ********************************** CONSTRUCTOR **************************************** //
    public function __CONSTRUCT() {
        $this->model = new changePswMdl();
    }

    // ********************************** Index **************************************** //
    public function declararGlobalesJs($arrVariables) {
        $return = "<script style='text/javascript'>";
        foreach ($arrVariables as $nombre => $value) {
            $return .= "$nombre = '$value';";
        }
        $return .= "</script>";
        echo $return;
    }

    public function Index() {
        $this->declararGlobalesJs(array("directoryPsw" => "../"));
    }

    public function save() {
        $req = $_REQUEST;
        $result = $this->model->save($req);
        echo json_encode($result);
    }

}
