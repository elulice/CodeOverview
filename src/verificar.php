<?php
require_once "../setupfolder/sys_param.php";
require_once "../setupfolder/db_param.php";
require_once "../setupfolder/fun_param.php";

// PAASO IMGS
$pathImgCredenPics = $cfg['paso_images_profile_credenciales'] . "/credenciales_fotos/";

// Creo una variable por cada post con el mismo nombre que trae del form
foreach ($_POST as $post => $value) {
    $$post = $value;
}

// Validar datos
$qrystr_data = "SELECT CONCAT(m.apellido, ' ', m.nombres) as nombres, c.cod_member, c.nro_matricula, t.nombre_titulo as titulo ";
$qrystr_data .= "FROM credenciales AS c ";
$qrystr_data .= "INNER JOIN matriculas ma ON ma.nro_matricula = c.nro_matricula ";
$qrystr_data .= "INNER JOIN titulos t ON t.id_titulo = ma.titulo  ";
$qrystr_data .= "INNER JOIN member m ON m.cod_member = c.cod_member ";
$qrystr_data .= "WHERE ma.estado IN(20,30) AND c.hash = '$hash'";

$qry_data = mysql_db_query($c_database, $qrystr_data, $link);
verificar($qrystr_data);

// Si existen datos
if (mysql_num_rows($qry_data) > 0):
    $row_datos = mysql_fetch_assoc($qry_data);

    $nombre = utf8_encode($row_datos["nombres"]);
    $member = $row_datos["cod_member"];
    $titulo = $row_datos["titulo"];
    $matricula = $row_datos["nro_matricula"];
    $image = base64_encode(file_get_contents($pathImgCredenPics . $member . ".jpg"));
    
    doLog($member, "MATRICULADOS EXTRANET", "CONSULTA DE QR MATRICULA. DATOS CORRECTOS", "Cod_M: $member | NroM: $matricula");
    ?>
    <div class="card verified">
        <!--<i class="fas fa-award verified-decoration"></i>-->
        <div class="watermark"></div>
        <img class="card-img-top" src="data:image/jpeg;base64,<?php echo $image; ?>" alt="Card image cap">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-user-graduate"></i> <?php echo $titulo; ?></h5>
        </div>
        <ul class="list-group list-group-flush">
            <li class="list-group-item p-0"></li>
            <li class="list-group-item"><i class="fas fa-address-card"></i> <?php echo $nombre; ?></li>
            <li class="list-group-item"><i class="fas fa-graduation-cap"></i> Matrícula: <?php echo $matricula; ?></li>
        </ul>
    </div>
    <?php
else:
    // Si no existen devuelvo 1, el cual informa un error del lado del cliente (JS)
    doLog('NO EXISTE', "MATRICULADOS EXTRANET", "CONSULTA DE QR MATRICULA. DATOS INCORRECTOS", "CONSULTA DE QR MATRICULA. DATOS INCORRECTOS.");
    echo 1;
endif;
?>