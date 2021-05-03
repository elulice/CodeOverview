<?php

session_start();
require_once 'models/personal_data.mdl.php';

class PersonalDataCtrlr {

    private $model;

    // ********************************** CONSTRUCTOR **************************************** //
    public function __CONSTRUCT() {
        $this->model = new Home();
    }

    // ********************************** Index **************************************** //
    public function Index() {

        require_once 'models/general.mdl.php';
        $mdlGeneral = new GeneralMdl();
        GLOBAL $cfg;

        require_once 'views/header.view.php';
        require_once 'views/home.view.php';
        require_once 'views/personal_data.view.php';
        require_once 'views/footer.view.php';
        echo '<script type="text/javascript" src="../mvc/js/personal.data.js"></script>';
    }

    public function getData() {
        $idSolicitud = $_REQUEST['_d_'];
        $result = $this->model->getData($idSolicitud);
        echo json_encode($result);
    }

    public function getList() {
        $result = $this->model->getList();
        echo json_encode($result);
    }

    public function save() {
        $req = $_REQUEST;
        $result = $this->model->save($req);
        echo json_encode($result);
    }

    public function cancel() {
        $idSolicitud = $_REQUEST['_d_'];
        $result = $this->model->cancel($idSolicitud);
        echo json_encode($result);
    }

    public function uploadImage() {
        $req = $_REQUEST;
        $result = $this->model->uploadImage($req);
        echo json_encode($result);
    }

}
