<?php

class changePswMdl {

    public function save($req) {
        global $conn;
        $codMember = $_SESSION["cod_member"];
        $passExtra = $req["new-pass"];
        try {
            $this->qrystr = "UPDATE member SET psw_extra='$passExtra' WHERE cod_member='$codMember';";
//        var_dump($req);
//        echo $this->qrystr;
//        die;
            try {
                $data = $conn->query($this->qrystr);
                $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => '');
                return $arr_ret;
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

}

?>
