<?php

require_once '../classes/class.ppt.php';
require_once "../setupfolder/sys_param.php";
require_once 'models/paypertic.log.mdl.php';
require_once 'models/general.mdl.php';
require_once 'models/quota.mdl.php';

class PayperticMdl {

    public function payOnline($data, $extraData) {
        global $conn;
        global $cfg;
        $generalMdl = new GeneralMdl();

        $quotesIDs = base64_decode($data);
        $fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        if (!empty($quotesIDs)) {
            $this->qrystr = "SELECT c.id_cuota, camp.obs AS mes, ct.descri_cuota, FORMAT(IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_1, c.valor_1, 
                                        IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_2, c.valor_2, 
                                        IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_3, c.valor_3, 
                                        IF(c.punitorio_mora = '0', c.valor_3, (((DATEDIFF(DATE(NOW() + INTERVAL 0 HOUR), c.fecha_venc_1) * c.valor_1 * (c.punitorio_mora / 100)) / 30) + c.valor_1))))), 2) AS monto_a_pagar 
                                        FROM cuotas c 
                                        LEFT JOIN member m ON c.member = m.cod_member 
                                        INNER JOIN cuotas_tipos AS ct ON c.tipo_cuota=ct.id_tipo_cuota 
                                        INNER JOIN campanias AS camp ON c.campania = camp.id_campania 
                                        WHERE id_cuota IN ($quotesIDs) AND m.canal_pago_banco_deb = 0 AND m.canal_pago_tarjeta = 0 
                                        AND c.pagada = 0 AND c.activa = 1";
        }

        try {

            $queryData = $conn->query($this->qrystr);
            while ($row = $queryData->fetch(PDO::FETCH_ASSOC)) {
                // Datos de la transacción
                $idQuote = $row["id_cuota"];
                $campObs = $row["mes"];
                $description = "$idQuote Cuota: $campObs";
                $quoteType = $row["descri_cuota"];
                $monto = $row["monto_a_pagar"];
                $montoTotal = $montoTotal + $monto;

                $details[] = array(
                    'external_reference' => "$idQuote", // ID CUOTA
                    'concept_id' => "$quoteType", // TIPO CUOTA STRING
                    'concept_description' => "$description",
                    'amount' => $monto
                );
            }

            if (bccomp($extraData, $montoTotal, 2) != 0) { // Si monto total mostrado al cliente y el monto total obtenido de la base no coinciden. 
                $arr_ret = array("estado" => false, "descripcion" => 'Ha ocurrido un error. Por favor actualice la página', "detalle" => "Detalle: Error de coincidencia en los montos totales", 'datos' => array());
                return $arr_ret;
            }
        } catch (PDOException $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error de base de datos', "detalle" => $e->getMessage() . " | Query: $this->qrystr", 'datos' => array());
            return $arr_ret;
        }

        // Datos para la API de PPT
        $user = $cfg['ppt'][$_SESSION["regional"]]['user'];
        $pass = $cfg['ppt'][$_SESSION["regional"]]['pass'];
        $cliId = $cfg['ppt'][$_SESSION["regional"]]['clientId'];
        $cliSec = $cfg['ppt'][$_SESSION["regional"]]['clientSecret'];
        $responseUrl = $fullUrl . $cfg['server_root_extra'] . "/direct/paypertic/endpoint";

        // Fecha y hora de hoy
        $datetimeToday = date("Y-m-d H:i:s");
        // Convertido a formato ISO8601 para PPT
        // Agrego dos dias, ya que PPT valida que sea "menor que" no "igual"
        // De esta manera tendría validez de 2 y 3 dias (48hs y 72hs)
        $dueDate = date(DATE_ISO8601, strtotime("$datetimeToday +2 days"));
        $lastDueDate = date(DATE_ISO8601, strtotime("$datetimeToday +3 days"));

        // Datos del titular
        $name = $_SESSION['nombre'];
        $email = $_SESSION['email'];
        $dni = $_SESSION['dni'];

        if ($cfg['mode'] == 'test') {// Si en sys_param esta el mode declarado como test, entonces utilizo un mail personalizado
            $email = $cfg['mail']['to'];
        }

        $pptData = array(
            'external_transaction_id' => $generalMdl->randString(),
            'currency_id' => 'ARS',
            'details' => $details,
            'payer' => array(
                'name' => "$name",
                'email' => "$email",
                'external_reference' => "$_SESSION[cod_member]",
                'identification' => array(
                    'type' => "DNI_ARG",
                    'number' => "$dni",
                    'country' => "ARG"
                )
            ),
            "due_date" => "$dueDate",
            "last_due_date" => "$lastDueDate",
            "notification_url" => $responseUrl
        );
//        var_dump($pptData);
//        die;

        $ppt = new PPT($user, $pass, $cliId, $cliSec);

        try {
            $result = $ppt->create_preference($pptData);
            if ($result["response"]["code"] >= 400) { // Si ha ocurrido un error en PPT
                $arr_ret = array("estado" => false, "descripcion" => 'Error PayPerTic: ' . $result["response"]["message"], 'datos' => array());
                $this->logTables('error', $result["response"]);
            } else {
                $arr_ret = array("estado" => true, "descripcion" => '', 'datos' => $result["response"]);
                $this->logTables('success', $result["response"]);
            }
        } catch (PDOException $e) {
            $arr_ret = array("estado" => false, "descripcion" => 'Error PayPerTic: ' . $e->getMessage(), 'datos' => array());
        }

        return $arr_ret;
    }

    public function logTables($type = NULL, $data) {
        global $conn;
        global $cfg;

        $PPTLOG = new PayperticLogMdl();
        switch ($type) {
            case "success":
                $pptIdTransaction = $data['id'];
                $pptExternalTransactionId = $data["external_transaction_id"];
                $pptLocalCreationDate = "CURDATE()";
                $pptLocalCreationMoment = "NOW()";
                $pptAllDetails = $data['details'];
                $pptFinalAmount = $data["final_amount"];
                $pptDescription = json_encode($data["details"]);
                $pptStatus = $data["status"];
                $codMember = $_SESSION['cod_member'];
                $codRegional = $_SESSION['regional'];

                // Insercion a la tabla paypertic_transactions
                $this->queryPptTransaction = "INSERT INTO paypertic_transactions (ppt_id_transaction, ppt_external_transaction_id, ppt_local_creation_date, ppt_local_creation_moment, ppt_description, ppt_payment_total_amount, ppt_transaction_status_id, cod_member, regional) 
                    VALUES('$pptIdTransaction', '$pptExternalTransactionId', $pptLocalCreationDate, $pptLocalCreationMoment, '$pptDescription', '$pptFinalAmount', '$pptStatus', '$codMember', '$codRegional') ";
                $conn->query($this->queryPptTransaction);
                $pptTransactionInsertedId = $conn->lastInsertId();

                // Log
                $PPTLOG->setLog($pptTransactionInsertedId, "$codMember", $cfg['clientIp'], 'Ok. Transaccion correcta. Pago pendiente');

                // Insercion a la tabla paypertic_quote_transactions
                foreach ($pptAllDetails as $key => $details) {
                    $idCuota = $details['external_reference'];
                    $this->queryPptQuoteTransaction = "INSERT INTO paypertic_quote_transactions (ppt_id_reg, id_cuota) VALUES ('$pptTransactionInsertedId', '$idCuota')";
                    $conn->query($this->queryPptQuoteTransaction);
                }
                break;
            case "error":
                $pptCode = "Error Code:" . $data["code"];
                $pptExtendedCode = "Error Extended Code:" . $data["extended_code"];
                $pptLocalCreationDate = "CURDATE()";
                $pptLocalCreationMoment = "NOW()";
                $pptLocalResponseMoment = "NOW()";
                $pptDescription = $data["message"];
                $pptTransactionStatusId = "rejected";

                $codMember = $_SESSION['cod_member'];
                $codRegional = $_SESSION['regional'];
                $internalStatus = "3"; // Procesado con Errores
                //                
                // Insercion a la tabla paypertic_transactions
                $this->queryPptTransaction = "INSERT INTO paypertic_transactions (ppt_id_transaction, ppt_external_transaction_id, ppt_local_creation_date, ppt_local_creation_moment, ppt_local_response_moment, ppt_description, ppt_transaction_status_id, cod_member, regional, internal_status) 
                    VALUES('$pptCode', '$pptExtendedCode', $pptLocalCreationDate, $pptLocalCreationMoment, $pptLocalResponseMoment, '$pptDescription', '$pptTransactionStatusId', '$codMember', '$codRegional', '$internalStatus') ";
                $conn->query($this->queryPptTransaction);
                $pptTransactionInsertedId = $conn->lastInsertId();

                // Log
                $PPTLOG->setLog($pptTransactionInsertedId, "$codMember", $cfg['clientIp'], "[$pptCode | $pptExtendedCode]:$pptDescription");
                break;
        }
    }

    public function endpoint() {
        global $conn;
        global $cfg;

        $PPTLOG = new PayperticLogMdl();
        $QuotaMdl = new QuotaMdl();

        switch ($_SERVER['REQUEST_METHOD']) {
            case "POST":
                // Almaceno en variables el POST
                $post = (array) json_decode(file_get_contents('php://input'), TRUE);
                $postJson = json_encode($post);

                // Obtengo el id de PPT de la transaccion, el status y el payer information.
                $pptIdTransaction = $post["id"];
                $pptTransactionStatusId = $post["status"];
                $pptPayerInformation = $post["payer"];
                $codMember = $pptPayerInformation["external_reference"];

                // Busco el id de transaccion de la tabla basado en el id de PPT
                $this->queryPptId = "SELECT ppt_id_reg, ppt_transaction_status_id FROM paypertic_transactions WHERE ppt_id_transaction = '$pptIdTransaction'";
                $queryPptId = $conn->query($this->queryPptId);
                $pptIdData = $queryPptId->fetch(PDO::FETCH_ASSOC);
                $pptId = $pptIdData["ppt_id_reg"];
                $pptLastTransactionStatusId = $pptIdData["ppt_transaction_status_id"];

                try {
                    // Si el último status de la transaccion conocido en la base es igual al que informa PPT > es una peticion duplicada. 
                    // En ese caso retornar Header 200 y salir.
                    if ($pptLastTransactionStatusId == $pptTransactionStatusId) {
                        // Log
                        $PPTLOG->setLog($pptId, "$codMember", $cfg['clientIp'], "Ok.Peticion duplicada.CompleteResponseData:$postJson");
                        header("HTTP/1.1 200 OK");
                        exit();
                    }

                    // Update paypertic_transactions
                    $this->querystrEndpoint = " UPDATE paypertic_transactions SET ppt_transaction_status_id = '$pptTransactionStatusId', ppt_local_response_moment = NOW() WHERE ppt_id_reg = '$pptId'";  //, internal_status = '2'
                    $statement = $conn->prepare($this->querystrEndpoint);
                    $statement->execute();
                    $affectedRows = $statement->rowCount();

                    // Si todo va OK
                    if ($affectedRows > 0) {
                        // Log
                        $PPTLOG->setLog($pptId, "$codMember", $cfg['clientIp'], "Ok.Estado de transaccion actualizado a:$pptTransactionStatusId.CompleteResponseData:$postJson");
                        // Le damos el OK a PPT. Luego hacer el manejo interno del pago de cuotas
                        header("HTTP/1.1 200 OK");

                        // Si fue aprobado | status = approved
                        if ($pptTransactionStatusId == 'approved') {
                            // Registrar las cuotas pagas
                            $QuotaMdl->registerQuotes($pptId);
                        }

                        exit();
                    } else { // Si no se pudo ejecutar la consulta
                        // Log
                        $PPTLOG->setLog($pptId, "$codMember", $cfg['clientIp'], "Error PayPerTic Endpoint->No se pudo ejecutar la query: paypertic.mdl.php. Line: 220");
                        header("HTTP/1.1 400 Bad Request");
                        exit();
                    }
                } catch (PDOException $e) {
                    // Log
                    $PPTLOG->setLog($pptId, "$codMember", $cfg['clientIp'], "Error PayPerTic Endpoint: paypertic.mdl.php. Line: 233." . $e->getMessage());
                    header("HTTP/1.1 400 Bad Request");
                    exit();
                }

                break;
            default:
                $serverRequest = $_SERVER['REQUEST_METHOD'];
                // Log
                $PPTLOG->setLog('NULL', "0", $cfg['clientIp'], "Error PayPerTic Endpoint: Se intento ingresar por otro SERVER_REQUEST diferente a POST. SERVER_REQUEST->[$serverRequest]");
                header("HTTP/1.1 400 Bad Request");
                exit();
                break;
        }
    }

}

?>
