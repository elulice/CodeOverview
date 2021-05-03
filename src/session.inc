<?php
require_once "../setupfolder/sys_param.php";
if (!isset($_SESSION['cod_member']) || empty($_SESSION['cod_member'])) {
    header("Location: " . $cfg['server_root_extra'] . "/login");
}
?>