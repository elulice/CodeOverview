<?php

class GeneralMdl {

    public function sanitize($parValor) {
        global $conn;

        $parValor = $conn->quote($parValor);
        //Le volvemos a sacar las comillas 
        return(str_replace("'", "", $parValor));
    }

    public function getDatosId($tabla, $campo_clave, $id) {
        global $conn;

        //if($id>0)
        $where = " WHERE $campo_clave=$id";

        $qrystr = "	SELECT * FROM $tabla $where";

        try {
            $data = $conn->query($qrystr);
            $filas = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $filas);

            return $arr_ret;
        } catch (PDOException $e) {
            //~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr", 'datos' => array());
        }
        return($arr_ret);
    }

    public function declararGlobalesJs($arrVariables) {
        $return = "<script style='text/javascript'>";
        foreach ($arrVariables as $nombre => $value) {
            $return .= "$nombre = '$value';";
        }
        $return .= "</script>";
        echo $return;
    }

    public function getTipoDni() {
        global $conn;

        $this->qrystr = "SELECT * FROM tipo_doc";

        try {
            $data = $conn->query($this->qrystr);
            $filas = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $filas);

            return $arr_ret;
        } catch (PDOException $e) {
            //~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr", 'datos' => array());
        }
        return($arr_ret);
    }

    public function getEstadoCivil() {
        global $conn;

        $this->qrystr = "SELECT * FROM estado_civil";

        try {
            $data = $conn->query($this->qrystr);
            $filas = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $filas);

            return $arr_ret;
        } catch (PDOException $e) {
            //~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr", 'datos' => array());
        }
        return($arr_ret);
    }

    public function getProvincias() {
        global $conn;

//        $this->qrystr = "SELECT * FROM provincias";
        $this->qrystr = "SELECT id_provincia, CONVERT(CAST(provincia as BINARY) USING utf8) as provincia FROM provincias";

        try {
            $data = $conn->query($this->qrystr);
            $filas = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $filas);

            return $arr_ret;
        } catch (PDOException $e) {
            //~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr", 'datos' => array());
        }
        return($arr_ret);
    }

    public function getLocalidades($idProv) {
        global $conn;
        if (!empty($idProv)) {
            $where = "WHERE id_provincia = $idProv";
        }
        $this->qrystr = "SELECT * FROM localidades $where";

        try {
            $data = $conn->query($this->qrystr);
            $filas = $data->fetchAll(PDO::FETCH_ASSOC);

            $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $filas);

            return $arr_ret;
        } catch (PDOException $e) {
            //~ echo 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$qrystr";
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos: ' . $e->getMessage() . "<br>Consulta:<br>$this->qrystr", 'datos' => array());
        }
        return($arr_ret);
    }

    public function randString($minRand = 100000, $maxRand = 999999) {
        $rand = rand($minRand, $maxRand);

        $fecha = new DateTime();
        $newRand = $fecha->getTimestamp() . $rand;

        $newRand = base64_encode($newRand);

        return (string) $newRand;
    }

}

?>
