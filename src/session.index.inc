<?php

if (isset($_SESSION['cod_member']) || !empty($_SESSION['cod_member'])) {
    header("Location: " . $cfg['server_root_extra'] . "/inicio");
}
?>