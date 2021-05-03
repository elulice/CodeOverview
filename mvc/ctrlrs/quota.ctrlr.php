<?php

session_start();
require_once 'models/quota.mdl.php';
require_once 'models/paypertic.mdl.php';

class QuotaCtrlr {

    private $model;
    private $modelPpt;

    // ********************************** CONSTRUCTOR **************************************** //
    public function __CONSTRUCT() {
        $this->model = new QuotaMdl();
        $this->modelPpt = new PayperticMdl();
    }

    // ********************************** Index **************************************** //
    public function Index() {

        require_once 'models/general.mdl.php';
        $mdlGeneral = new GeneralMdl();
        GLOBAL $cfg;

        require_once 'views/header.view.php';
        require_once 'views/home.view.php';
        require_once 'views/quota.view.php';
        require_once 'views/footer.view.php';
        echo '<script type="text/javascript" src="../mvc/js/quota.js"></script>';
    }

    public function getData() {
        $data = $_REQUEST['_d_'];
        $result = $this->model->getData($data);
        echo json_encode($result);
    }

    public function getList() {
        $quoteKind = $_REQUEST['_k_']; // Espera valores: 'pending' o 'history'. Si no se define ninguno, el predeterminado es pending
        $result = $this->model->getList($quoteKind);
        echo json_encode($result);
    }

    public function pptPay() {
        $data = $_REQUEST['_d_'];
        $extraData = $_REQUEST['_ed_'];
        $result = $this->modelPpt->payOnline($data, $extraData);
        echo json_encode($result);
    }

}
