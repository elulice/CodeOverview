<?php

session_start();
require_once "../setupfolder/sys_param.php";
require_once "../setupfolder/db_param.php";
require_once "../setupfolder/fun_param.php";

foreach ($_GET as $get => $value) {
    $$get = $value;
}

switch ($step) {
    case '':
        $data = explode('_', $data);
        $codMember = base64_decode($data[0]);
        $pass = $data[1];

        $qrystr_change_pass = "SELECT * FROM member ";
        $qrystr_change_pass .= "WHERE cod_member = '$codMember' AND md5(psw_extra) = '$pass'";

        $qrystr_data = $conn->query($qrystr_change_pass);
        verificar($qrystr_change_pass);
        $row_extranetReg = $qrystr_data->fetch(PDO::FETCH_ASSOC);

//        $qry_change_pass = mysql_db_query($c_database, $qrystr_change_pass, $link);
//        verificar($qrystr_change_pass);

        if (sizeof($row_extranetReg) <= 1) { // 1 es cuando no trae resultados. Por ende, mayor a 1 significa que si se encontraron resultados
            $return = array('state' => 0, 'descrip' => " El enlace ha caducado o ya ha sido utilizado");
        } else {
            $return = array('state' => 1, 'descrip' => file_get_contents('new_pass_html.php'));
            // Asigna el nuevo valor de *extranet_registrado* a la sesion
            $_SESSION['extranetReg'] = $row_extranetReg['extranet_registrado'];
        }
        echo json_encode($return);
        break;
    case 'savePass':
        $codMember = base64_decode($cod_member);
        $qrystr_extra = "UPDATE member SET psw_extra = '$psw_extra' WHERE cod_member = '$codMember'";
        try {
            $conn->exec($qrystr_extra);
            $return = array('state' => '1', 'response' => 'La clave ha sido modificada correctamente. Serás redireccionado en breves');
        } catch (PDOException $e) {
            $return = array('state' => '0', 'response' => 'Ha ocurrio un error al modificar la clave. Por favor, contáctese con administración');
        }
        verificar($qrystr_extra);
        echo json_encode($return);
        break;
}