<?php

class Home {

    public function getData($idSolicitud) {
        global $conn;
        $codMember = $_SESSION["cod_member"];

        try {
            if (!empty($idSolicitud)) {
                $this->qrystr = "SELECT scdd.*, scd.observaciones, '1' as readyonly FROM solicitud_cambios_datos_det scdd "
                        . "LEFT JOIN solicitud_cambios_datos scd ON scdd.id_solicitud = scd.id_solicitud WHERE scdd.id_solicitud = '$idSolicitud' AND scdd.tipo = 'MOD'";
                $profileImage = array('image' => (file_exists("../img/credenciales_fotos/solicitudes/$idSolicitud-$codMember.jpg") ? base64_encode(file_get_contents("../img/credenciales_fotos/solicitudes/$idSolicitud-$codMember.jpg")) : ""));
            } else {
                $this->qrystr = "SELECT apellido, nombres, tipo_doc_id, nro_doc, fechanac, lugar_nacimiento, estado_civil, sexo, conyugue, direccion, barrio, id_provincia, id_localidad, cp, telefono_fijo, telefono_celular, nro_cuit, email, '' as observaciones FROM member WHERE cod_member = '$codMember'";
                $profileImage = array('image' => (file_exists("../img/credenciales_fotos/$codMember.jpg") ? base64_encode(file_get_contents("../img/credenciales_fotos/$codMember.jpg")) : ""));
            }

            try {
                $data = $conn->query($this->qrystr);
                $filas = $data->fetchAll(PDO::FETCH_ASSOC);
                $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => $filas);
                $arr_ret['datos'][0] += $profileImage; // Agrego el dato "imagen" al array

                return $arr_ret;
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

    public function getList() {
        global $conn;
        $codMember = $_SESSION["cod_member"];

        try {
            $this->qrystr = "SELECT d.*, e.descri_estado as estado FROM solicitud_cambios_datos d "
                    . "LEFT JOIN solicitud_cambios_datos_estados e ON d.id_estado_solicitud = e.id_estado_solicitud "
                    . "WHERE cod_member = '$codMember' ORDER BY id_solicitud DESC";

            try {
                $data = $conn->query($this->qrystr);
                $filas = $data->fetchAll(PDO::FETCH_ASSOC);
                $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => $filas);
                return $arr_ret;
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

    public function save($req) {
        global $conn;
        $codMember = $_SESSION["cod_member"];
        $observaciones = $req["observaciones"];
//        var_dump($req);
//        die;
        try {
            $this->qrystr = "INSERT INTO solicitud_cambios_datos (fecha_solicitud, fecha_cambio_estado, cod_member, observaciones) VALUES (CURDATE(), CURDATE(), '$codMember', '$observaciones')";
//            echo $this->qrystr;
//            die;
            try {
                $conn->query($this->qrystr);
                $insertIdSolicitud = $conn->lastInsertId();
                if ($insertIdSolicitud > 0) {
                    $arr_ret = $this->saveDet($req, $insertIdSolicitud);
                    $this->saveDetOriginal($insertIdSolicitud);
                    $this->uploadImage($insertIdSolicitud, $_FILES);
                }
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

    public function saveDet($request, $idSolicitud) {
        global $conn;
        $apellido = $request["apellido"];
        $nombres = $request["nombres"];
        $tipoDoc = $request["tipo_doc_id"];
        $nroDoc = $request["nro_doc"];
        $fechaNac = $request["fechanac"];
        $lugarNac = $request["lugar_nacimiento"];
        $sexo = $request["sexo"];
        $eCivil = $request["estado_civil"];
        $conyuge = $request["conyuge"];
        $direc = $request["direccion"];
        $barrio = $request["barrio"];
        $idProv = $request["id_provincia"];
        $idLoca = $request["id_localidad"];
        $cp = $request["cp"];
        $tel = $request["telefono_fijo"];
        $cel = $request["telefono_celular"];
        $nroCuit = $request["nro_cuit"];
        $email = $request["email"];

        try {
            $this->qrystrDet = "INSERT INTO solicitud_cambios_datos_det (id_solicitud, tipo, apellido, nombres, tipo_doc_id, nro_doc, fechanac, lugar_nacimiento, sexo, estado_civil, conyugue, direccion, barrio, id_provincia, id_localidad, cp, telefono_fijo, telefono_celular, nro_cuit, email) VALUES ";
            $this->qrystrDet .= "('$idSolicitud', 'MOD', '$apellido','$nombres','$tipoDoc','$nroDoc','$fechaNac','$lugarNac','$sexo','$eCivil','$conyuge','$direc','$barrio','$idProv','$idLoca','$cp','$tel','$cel','$nroCuit','$email')";

            try {
                $conn->query($this->qrystrDet);
                $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => '');
                return $arr_ret;
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystrDet", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

    public function saveDetOriginal($idSolicitud) {
        global $conn;
        $codMember = $_SESSION["cod_member"];

        try {
            $this->qrystrDet = "INSERT INTO solicitud_cambios_datos_det (id_solicitud, tipo, apellido, nombres, tipo_doc_id, nro_doc, fechanac, lugar_nacimiento, sexo, estado_civil, conyugue, direccion, barrio, id_provincia, id_localidad, cp, telefono_fijo, telefono_celular, nro_cuit, email) ";
            $this->qrystrDet .= "(SELECT '$idSolicitud', 'ORI', apellido, nombres, tipo_doc_id, nro_doc, fechanac, lugar_nacimiento, sexo, estado_civil, conyugue, direccion, barrio, id_provincia, id_localidad, cp, telefono_fijo, telefono_celular, nro_cuit, email FROM member WHERE cod_member='$codMember')";

            try {
                $conn->query($this->qrystrDet);
                $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => '');
                return $arr_ret;
            } catch (PDOException $e) {
                $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystrDet", 'datos' => array());
            }
        } catch (Exception $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error', "detalle" => $e->getMessage(), 'datos' => array());
        }
        return($arr_ret);
    }

    public function cancel($idSolicitud) {
        global $conn;

        try {
            $this->qrystr = "UPDATE solicitud_cambios_datos SET id_estado_solicitud = '40' WHERE id_solicitud = '$idSolicitud'";
            $this->qrystr2 = "UPDATE solicitud_cambios_datos_det SET id_estado_solicitud = '40' WHERE id_solicitud = '$idSolicitud'";

            try {
                $conn->query($this->qrystr);
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

    public function uploadImage($idSolicitud, array $FILE) {
        $errors = array(); // Almaceno todos los errores
        $codMember = $_SESSION["cod_member"];
        $newFileName = "$idSolicitud-$codMember";
        $uploadDirectory = "../img/credenciales_fotos/";
        $fileExtensions = array('jpg', 'jpeg'); // Extensiones permitidas

        $fileName = $FILE['personal-data-image']['name'];
        $fileSize = $FILE['personal-data-image']['size'];
        $fileTmpName = $FILE['personal-data-image']['tmp_name'];
        $fileExtension = strtolower(end(explode('.', $fileName)));
        $file_name = $newFileName . ".jpg";

        $uploadPath = $uploadDirectory . "solicitudes/" . $file_name; // Donde se va a guardar la imagen 

        if (!in_array($fileExtension, $fileExtensions)) { // Si la extension esta permitida
            $errors[] = "El archivo no está permitido. Solo se aceptan JPG/JPEG";
        }

        if ($fileSize > 4000000) { // Si el archivo pesa mas de 4000kb
            $errors[] = "El archivo no puede ser mayor de 4Mb";
        }
        if (empty($errors)) { // Si cumple con todo los requisitos
            $didUpload = move_uploaded_file($fileTmpName, $uploadPath); // Subo el archivo

            if ($didUpload) { // Si el archivo se subio bien muestro la imagen subida
                $result = "imagen subida correctamente";
            } else {
                // Sino informo el error
                $errors[] = "Ha ocurrido un error subiendo el archivo, comuniquese con el Administrador";
            }
            $arr_ret = array("estado" => true, "descripcion" => '', "detalle" => '', 'datos' => $result);
        } else {
            foreach ($errors as $error) { // Si hay errores, recorro el array y los muestro
                $result .= "- $error <br/>";
            }
            $arr_ret = array("estado" => false, "descripcion" => '', "detalle" => '', 'datos' => $result);
        }
        return($arr_ret);
    }

}

?>
