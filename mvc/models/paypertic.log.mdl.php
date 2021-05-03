<?php

class PayperticLogMdl {

    public function setLog($pptIdReg, $codMember, $ip, $detail) {
        global $conn;

        if (!empty($pptIdReg)) {
            $detail = $conn->quote($detail);

            $this->qrystr = "INSERT INTO paypertic_logs (ppt_id_reg, cod_member, moment, ip, detail) 
                VALUES ('$pptIdReg', '$codMember', NOW(), '$ip', $detail)";

            try {

                $queryData = $conn->query($this->qrystr);
                $arr_ret = array("estado" => true, "descripcion" => 'PPTLog->Correcto', "detalle" => "", 'datos' => array());
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
                return $arr_ret;
            }
        } else {
            $arr_ret = array("estado" => false, "descripcion" => 'PPTLog->PPT ID REG no esta setteado', "detalle" => "", 'datos' => array());
        }
    }

}

?>
