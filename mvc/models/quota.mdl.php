<?php

class QuotaMdl {

    public function getData($data) {
        global $conn;
        $data = base64_decode($data);

        try {
            if (!empty($data)) {
                $this->qrystr = "";
            }
            try {
                $queryData = $conn->query($this->qrystr);
                $filas = $queryData->fetchAll(PDO::FETCH_ASSOC);
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

    public function getList($quoteKind) {
        global $conn;
        $codMember = $_SESSION["cod_member"];

        $where = " c.activa = 1 AND c.pagada = 0 ";
        $where .= "AND m.canal_pago_banco_deb = 0 AND m.canal_pago_tarjeta = 0 "; // Para validar que el matriculado no este adherido a un medio de pago automatico
        $join = " LEFT JOIN member m ON c.member = m.cod_member "; // Join para poder realizar la validacion anterior
        $orderBy = " ORDER BY c.campania ASC, c.tipo_cuota";
        $e = "'0'";
        if ($quoteKind == 'history') {
            $where = " c.activa = 0 AND c.pagada = 1 ";
            $join = "";
            $e = "'1'";
            $orderBy = " ORDER BY c.momento_pagada DESC, c.campania ASC, c.tipo_cuota";
        }

        try {
            $this->qrystr = "SELECT c.id_cuota, ct.descri_cuota, c.campania, camp.obs AS mes, c.valor_1 AS valor_cuota_pura, c.fecha_venc_1, c.fecha_venc_2, c.fecha_venc_3, DATE_FORMAT(c.momento_pagada, '%d-%m-%Y') as momento_pagada, 
                                
                                    FORMAT(IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_1, c.valor_1, 
                                    IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_2, c.valor_2, 
                                    IF(DATE(NOW() + INTERVAL 0 HOUR) <= c.fecha_venc_3, c.valor_3, 
                                    IF(punitorio_mora = '0', c.valor_3, (((DATEDIFF(DATE(NOW() + INTERVAL 0 HOUR), c.fecha_venc_1) * c.valor_1 * (punitorio_mora / 100)) / 30) + c.valor_1))))), 2) AS monto_a_pagar, 
                                    $e as estado 
                                FROM cuotas AS c
                                    INNER JOIN campanias AS camp ON c.campania = camp.id_campania 
                                    INNER JOIN cuotas_tipos AS ct ON c.tipo_cuota=ct.id_tipo_cuota 
                                    $join 
                                WHERE c.member = '$codMember' 
				AND $where
				$orderBy";

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

    public function registerQuotes($pptId) {
        // @$pptId = paypertic_transactions->ppt_id_reg
        global $conn;
				global $cfg;

//				require_once ($cfg['paso_helper'] . "/library_php.php");
				require_once $cfg['paso_libreria_local'] . "/funciones_locales.php";
				require_once $cfg['paso_libreria_local'] . "/gestor_contable.php";
        require_once 'models/general.mdl.php';
        $mdlGeneral = new GeneralMdl();

				$pptId = $mdlGeneral->sanitize($pptId);

				$cant_procesados_ok = 0;                                                  
				$cant_procesados_error = 0;
				$mensaje_error = '';
				
				////////////////////////////
				$acumulador_monto = 0; 
				$acumulador_cuota_pura = 0; 
				$acumulador_interes = 0;
				////////////////////////////                                   

				$resumen_control_acumulador = 0;

				// ********************************************************************************************************************************** //
				// busco todos los registros del transaction
				$qrystr_trans = "SELECT  *
											FROM paypertic_transactions
											WHERE ppt_id_reg = '$pptId'
											";
				$data_trans = $conn->query($qrystr_trans);
				$filas_trans = $data_trans->fetchAll(PDO::FETCH_ASSOC);
				$ppt_payment_total_amount = $filas_trans['ppt_payment_total_amount'];

				// ********************************************************************************************************************************** //
				// busco todos los registros de detalle del transaction que estén sin procesar
				$qrystr_det = "SELECT  qt.id_quote_transaction, 
												cu.*
											FROM paypertic_quote_transactions AS qt
												LEFT JOIN cuotas AS cu ON qt.id_cuota = cu.id_cuota
											WHERE qt.ppt_id_reg = '$pptId'
											";
				$data_det = $conn->query($qrystr_det);
				$filas_det = $data_det->fetchAll(PDO::FETCH_ASSOC);
								
				foreach($filas_det AS $row_det) 
				{
					$id_quote_transaction = $row_det['id_quote_transaction']; 	
					$id_cuota = $row_det['id_cuota']; 	
					//$monto = $row_det[monto]; 	
					$cuota_pura = $row_det['valor_1'];
					$tipo_cuota = $row_det['tipo_cuota'];
					$plan_pago_contenedor = $row_det['plan_pago_contenedor'];
					$descripcion = $row_det['descripcion'];
					$pagada = $row_det['pagada'];
					$activa = $row_det['activa'];
					$regional = $row_det['regional'];

//~ echo "id_quote_transaction: $id_quote_transaction<br>";

					if($id_cuota != 0)/// si encuentra una cuota para pagar la marco
					{
						if($pagada == 0 AND $activa == 1) // si la cuota no esta pagada y esta activa
						{
							$qrystr_cuota="UPDATE cuotas SET pagada = 1, activa = 0, momento_pagada = NOW() $cfg[dif_hora] WHERE id_cuota = '$id_cuota'";
							$data_cuota = $conn->query($qrystr_cuota);
							//echo $qrystr_cuota."<BR>";
							$arr_cupones_asientos[] = $id_cuota;
							//// marco el registro como procesado
							$qrystr_cuota="UPDATE paypertic_quote_transactions SET process_ok = 1 WHERE id_quote_transaction = $id_quote_transaction ";
							$data_cuota = $conn->query($qrystr_cuota);
							//echo $qrystr_cuota."<BR>";
							
							////////// ACUMULO EL MONTO DE LA CUOTA CORRECTA
							//$acumulador_monto = $acumulador_monto + $monto;
							$acumulador_cuota_pura = $acumulador_cuota_pura + $cuota_pura;
							$cant_procesados_ok++;          
							
							//Si la cuota es de plan hacer tratamiento del plan 
							if($tipo_cuota == 2)
							{
								/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								// VERIFICO SI EN EL PLAN DE PAGO CONTENEDOR HAY CUOTAS IMPAGAS
								$qrystr_plan="SELECT id_cuota FROM cuotas WHERE plan_pago_contenedor = '$plan_pago_contenedor' AND activa = 1 AND pagada = 0";
								$data_plan = $conn->query($qrystr_plan);
								$filas_plan = $data_plan->fetchAll(PDO::FETCH_ASSOC);
								$cant_impagas = sizeof($filas_plan);
								if($cant_impagas == 0)/// si no quedan mas cuotas impagas en el plan pongo como pagado el plan
								{
									$qrystr_rc1 = "UPDATE planes_pago SET pagado = 1 WHERE id_plan_pago = '$plan_pago_contenedor'";
									$data_rc1 = $conn->query($qrystr_rc1);
								}
								////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							}                                        
						}
						else
						{ // si la cuota ya estaba pagada le agrego un error
							$qrystr_upd_cuota=" UPDATE paypertic_quote_transactions 
																	SET errors = CONCAT(NOW() $cfg[dif_hora],' // LA CUOTA $descripcion ($id_cuota) YA ESTABA PAGADA PREVIAMENTE EN EL MOMENTO $row_det[momento_pagada]'),
																		process_ok = 0
																	WHERE id_quote_transaction = $id_quote_transaction";
							$data_upd_cuota = $conn->query($qrystr_upd_cuota);
							//echo $qrystr_cuota."<BR>";
							$cant_procesados_error++;
							//$mensaje_error .= " // Registro: $id_registro - La cuota $tipo_mensaje $id_cuota YA ESTABA PAGADA PREVIAMENTE EN EL MOMENTO $row_det[momento_pagada] <BR>";
						}
					}
					else// si no existe la cuota agrego un log con el detalle del error
					{
						$qrystr_upd_cuota=" UPDATE paypertic_quote_transactions SET errors = CONCAT(NOW() $cfg[dif_hora],' // LA CUOTA $descripcion ($id_cuota)  NO SE ENCONTRÓ'), 
																	process_ok = 0
																WHERE id_quote_transaction = $id_quote_transaction";
						$data_upd_cuota = $conn->query($qrystr_upd_cuota);
						$cant_procesados_error++;
						//$mensaje_error .= " // Registro: $id_registro - NO SE ENCONTRO LA $tipo_mensaje CORRESPONDIENTE  <BR>";
					}
				}
				
//~ echo"regional: $regional<br>";
				if($regional <> 0){
						////////////////////////////////////////// ASIENTO CTA CTE //////////////////////////////////////////////			
						///////////////////// OBTENGO EL NODO DEL CANAL DE PAGOS ///////////////////
						//Obtener canal de pago
						$qrystr_canal = "SELECT id_canal FROM canal_pago_autom WHERE id_regional = $regional AND descri LIKE('PayPertic%')";
//~ echo $qrystr_canal."<BR>";
						$data_canal = $conn->query($qrystr_canal);
						$row_canal = $data_canal->fetch(PDO::FETCH_ASSOC);
						$canal_pago = $row_canal['id_canal'];
//~ echo"canal_pago: $canal_pago<br>";

						//Obtener campania predet
						$qrystr_camp = "SELECT id_campania FROM campanias WHERE predet = 1";
						$data_camp = $conn->query($qrystr_camp);
						$row_camp = $data_camp->fetch(PDO::FETCH_ASSOC);
						$campania = $row_camp['id_campania'];

						if($canal_pago <> 0){

								$titular = get_titular_regional($regional); // obtengo el titular del regional del usuario
								//echo "tit: $regional";
								$qrystr_nodo = "SELECT nodo, CURDATE() AS fecha FROM canal_pago_autom WHERE id_canal = $canal_pago";
								//echo $qrystr_nodo."<BR>";
								$data_nodo = $conn->query($qrystr_nodo);
								$row_nodo = $data_nodo->fetch(PDO::FETCH_ASSOC);
								$nodo_plan = "9." . $row_nodo[nodo];
								$fecha = $row_nodo[fecha];
								////////////////////////////////////////////////////////////////////////////
//~ echo"acumulador_cuota_pura: $acumulador_cuota_pura - acumulador_interes: $acumulador_interes<br>";								
								/// si el monto es 0 al pedo voy a hacer el asiento
								if($acumulador_cuota_pura <> 0)
								{
									//  **** INTERESES ****
									//Agrupaciones a las que se relacionará el asiento contable.
									$arr_agrupaciones = array();
									$arr_agrupaciones[5]=$campania;  //5: Asociación a Campaña.
									
									$acumulador_interes = $ppt_payment_total_amount - $acumulador_cuota_pura;
//~ echo"acumulador_cuota_pura: $acumulador_cuota_pura - acumulador_interes: $acumulador_interes<br>";								
									
									$gestorCtaCte = new GestorContable();
									$id_asiento_intereses = $gestorCtaCte->guardarAsiento($titular,"9.1.1.7","Intereses T $id_quote_transaction de PayPerTiy procesado",0,$acumulador_interes,'',$fecha,$campania,$regional,'M',$arr_agrupaciones);
//~ echo"id_asiento_intereses: $id_asiento_intereses<br>";								

									//  ** FIN INTERESES **

									//  **** CUOTA PURA ****
									//Agrupaciones a las que se relacionará el asiento contable.
									$arr_agrupaciones = array();
									$arr_agrupaciones[5]=$campania;  //5: Asociación a Campaña.
									$arr_agrupaciones[3]=$id_lote;   //3: Asociación a Lote Banco Cupones.
									$arr_agrupaciones[7]=$id_asiento_intereses;  //5: Intereses Asociados.

									$gestorCtaCte = new GestorContable();
									$id_asiento = $gestorCtaCte->guardarAsiento($titular,$nodo_plan,"Lote $id_lote de Cupones procesado",0,$acumulador_cuota_pura,'',$fecha,$campania,$regional,'M',$arr_agrupaciones);

									//ASIGNO EL ASIENTO DE CTACTE CREADO A CADA UNA DE LAS CUOTAS PAGADAS
									for($i=0;$i<sizeof($arr_cupones_asientos);$i++)
									{
										////////////////////////////////////////////////////////////////////////////////
										$qrystr2="UPDATE cuotas SET id_asiento = $id_asiento WHERE id_cuota=".$arr_cupones_asientos[$i]."";
										$data2 = $conn->query($qrystr2);
										////////////////////////////////////////////////////////////////////////////////				
									}		
									//  ** FIN CUOTA PURA **
								}
								////////////////////////////////////// FIN ASIENTO CTA CTE //////////////////////////////////////////////	
						}
				}
						
				// *********************************************************************************************************************************************** //
				//Si $cant_procesados_error es 0 entonces poner el internal_status de la paypertic_transactions en 2, de lo contrario en 3
				if($cant_procesados_error == 0)
					$internal_status = 2;          //2	Procesado Correctamente
				else
					$internal_status = 3;          //3	Procesado con Errores 
				
				$qrystr_is="UPDATE paypertic_transactions SET internal_status = $internal_status WHERE ppt_id_reg=".$pptId."";
				$data_is = $conn->query($qrystr_is);
				// *********************************************************************************************************************************************** //
    }
}

?>
