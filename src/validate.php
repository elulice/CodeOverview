<?php

session_start();
require_once "../setupfolder/sys_param.php";
require_once "../setupfolder/db_param.php";
require_once "../setupfolder/fun_param.php";

// Creo una variable por cada post con el mismo nombre que trae del form
foreach ($_POST as $post => $value) {
    $$post = $value;
}

switch ($step) {
    case "":
//        $tipo_matricula = "P";
//        $matricula = "144154";
//        $dni = "36185335";

        if ($tipo_matricula == 'ADH') {
            $qrystr_data = "SELECT * ";
            $qrystr_data .= "FROM member ";
            $qrystr_data .= "WHERE nro_adherente = '$matricula' ";
        } else {
            $matricula = $tipo_matricula . $matricula;

            $qrystr_data = "SELECT * ";
            $qrystr_data .= "FROM matriculas AS ma ";
            $qrystr_data .= "INNER JOIN member AS me ON ma.member = me.cod_member ";
            $qrystr_data .= "WHERE ma.nro_matricula = '$matricula' ";
            $qrystr_data .= "AND ma.estado IN(20,30)";
        }

//echo $qrystr_data;

        $qry_data = $conn->query($qrystr_data);
        verificar($qrystr_data);
        $row_datos = $qry_data->fetch(PDO::FETCH_ASSOC);

//        $qry_data = mysql_db_query($c_database, $qrystr_data, $link);
//        verificar($qrystr_data);
//        $row_datos = mysql_fetch_assoc($qry_data);

        $matricula = $row_datos['nro_matricula'];
        $extranetReg = $row_datos["extranet_registrado"];

        if ($matricula == '') {
            // ERROR: Los datos ingresados no son válidos
            doLog('NO EXISTE', "MATRICULADOS EXTRANET", "CONSULTA DE MATRICULA. DATOS INCORRECTOS", "CONSULTA DE MATRICULA. DATOS INCORRECTOS.");
            echo 1;
            die;
        } else {
            if ($extranetReg == 1) { // Si ya esta registrado.
                // Indico al JS que tiene que solicitar la pass
                echo 'password';
            } else { // Si NO esta registrado
                // Indico al JS que tiene que solicitar el DNI
                echo 'dni';
            }
        }
        break;
    case "checkMail":
        global $cfg;
        // Consulto el email
        $codMember = $data;
        $qrystr_data = "SELECT email ";
        $qrystr_data .= "FROM member ";
        $qrystr_data .= "WHERE cod_member = '$codMember' ";

//        echo $qrystr_data;

        $qry_data = $conn->query($qrystr_data);
        verificar($qrystr_data);
        $row_email = $qry_data->fetch(PDO::FETCH_ASSOC);

//        $qry_data = mysql_db_query($c_database, $qrystr_data, $link);
//        verificar($qrystr_data);
//        $row_email = mysql_fetch_assoc($qry_data);

        $email = $row_email['email'];

        if ($cfg['mode'] == 'test') {// Si en sys_param esta el mode declarado como test, entonces utilizo un mail personalizado
            $email = $cfg['mail']['to'];
        }

        if (empty($email)) { // Si no tiene
//            $response = '<div class="input-group col-12"><div class="input-group-prepend"><span class="input-group-text minw-label" id="basic-addon1"><i class="fas fa-envelope-open-text"></i> Correo</span></div><input data-id="sign-up-email" type="email" required pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" autocomplete="off" class="form-control" placeholder="Ingrese su dirección de Correo" aria-label="Ingrese su dirección de Correo" aria-describedby="basic-addon1" /></div>';
            $response = "No tiene una dirección de correo asociada. Por favor comuníquese con administración.";
            $return = array('exist' => 'N', 'response' => $response);
        } else { // Si TIENE
            $email = preg_replace("/(?!^).(?=[^@]+@)/", "*", $email);
            $return = array('exist' => 'Y', 'response' => $email);
        }
        echo json_encode($return);
        break;
    case "sendMail":
        // Librerias Mail
        require('../setupfolder/phpmailer/class.phpmailer.php');
        require('../setupfolder/phpmailer/class.smtp.php');
        // Fin librerias Mail
        global $cfg;
        $codMember = $data;
        $rndPsw = randomPass(); // Genero una passw
        $codMemberLink = base64_encode($codMember) . "_";
        $link_mail = $cfg['mail']['link'] . $codMemberLink . md5($rndPsw);

        if (!empty($inputMail)) { // Si el mail se ingreso de forma manual
            $emailSend = $inputMail;
        } else { // Si no 
            // Consulto el email
            $qrystr_data = "SELECT email ";
            $qrystr_data .= "FROM member ";
            $qrystr_data .= "WHERE cod_member = '$codMember' ";

            $qry_data = $conn->query($qrystr_data);
            verificar($qrystr_data);
            $row_email = $qry_data->fetch(PDO::FETCH_ASSOC);

//            $qry_data = mysql_db_query($c_database, $qrystr_data, $link);
//            verificar($qrystr_data);
//            $row_email = mysql_fetch_assoc($qry_data);

            $emailSend = $row_email['email'];
        }

        $mail = new PHPMailer;

        $mail->isSMTP();
        $mail->Host = $cfg['mail']['host'];
        $mail->SMTPAuth = $cfg['mail']['auth'];
        $mail->Username = $cfg['mail']['user'];
        $mail->Password = $cfg['mail']['pass'];
        $mail->SMTPSecure = $cfg['mail']['secure'];
        $mail->Port = $cfg['mail']['port'];
        $mail->setFrom($cfg['mail']['from_mail'], $cfg['mail']['from_name']);
        $mail->isHTML(true);

        $mail->Subject = 'Datos Colegio Psicopedagogos de Cordoba';
        $mail->Body = "Ingresa al siguiente enlace para generar tu nueva clave: <a href='$link_mail'>Generar Clave</a><br/>Si no podes ingresar, copiá y pegá el siguiente enlace en tu navegador: $link_mail";
        $mail->AltBody = "Si no podes ingresar, copiá y pegá el siguiente enlace en tu navegador: $link_mail";

        // Si en sys_param esta el mode declarado como test, entonces utilizo un mail personalizado
        if ($cfg['mode'] == 'test') {
            $emailSend = $cfg['mail']['to'];
        }

        $mail->addAddress("$emailSend");

        if (!$mail->send()) {
            $return = array('state' => '0', 'response' => "Ha ocurrido un error al intentar enviar los datos. Informe el error a administración");
            doLog('ERROR MAIL', "VALIDATE.PHP", "No se pudo enviar el mail.", "Ocurrio un error al enviar el mail de nueva clave. [ ERROR: $mail->ErrorInfo ]");
        } else {
            $email = preg_replace("/(?!^).(?=[^@]+@)/", "*", $emailSend);
            $return = array('state' => '1', 'response' => "Los datos se enviaron correctamente a la dirección: <b>$email</b>. Revise su casilla de correo. Puede que el mail se encuentre en la carpeta de correo no deseado o spam");
            doLog('ENVIO MAIL', "VALIDATE.PHP", "Se envio mail.", "Se envio correctamente mail a $emailSend para cambio de clave.");

            $qrystr_extra = "UPDATE member SET extranet_registrado = '1', psw_extra = '$rndPsw' WHERE cod_member = '$codMember'";
            verificar($qrystr_extra);
            try {
                $conn->exec($qrystr_extra);
                $_SESSION['extranetReg'] = '1'; // Se setea la session en 1
            } catch (PDOException $e) {
                echo "ERROR EN UPDATE VALIDATE";
                doLog('ERROR EN UPDATE VALIDATE', "VALIDATE.PHP", "NO SE PUDO REALIZAR EL UPDATE de extranet_registrado. [Qry: $qrystr_extra ]");
            }
        }
        echo json_encode($return);
//        mysql_db_query($c_database, $qrystr_extra, $link);
//        verificar($qrystr_extra);
        break;
}