<?php
$code = base64_decode($_REQUEST["hash"]);
?>

<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1.0 minimal-ui"/>
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta name="apple-mobile-web-app-status-bar-style" content="black">

        <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0" />
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />

        <link rel="shortcut icon" href="../favicon.png" type="image/x-icon" /> 

        <title>Colegio de Psicopedagogos</title>

        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css" rel="stylesheet" type="text/css">
        <link href="../css/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <!-- Alert Div -->
        <div class="alert alert-primary alert-style" data-id="alert-notif" role="alert"></div>

        <!-- Overlay. "Block" actions with the webpage -->
        <div class="overlay" data-id="overlay"></div>

        <!-- Header -->
        <nav class="navbar sticky-top navbar-dark bg-header-navbar mt-n1 shadow-lg p-35">
            <a class="navbar-brand" href="/cpppc_matriculados">
                <div class="header-logo"></div>
                <div class="header-title">
                    Verificación para Matriculados
                    <span class="header-subtitle">Colegio de Psicopedagogos de Córdoba</span>
                </div>
            </a>
        </nav>

        <!-- Page Content -->
        <div class="container mt-3" data-id="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="card card-shadow">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fab fa-slack"></i> Ingrese el código
                            </h5>
                        </div>
                        <div class="card-body">
                            <!--<h5 class="card-title mt-n2"><i class="far fa-list-alt"></i> Matrícula</h5>-->
                            <form data-form="verified-submit">
                                <div class="form-group row">
                                    <div class="input-group mb-1 col-12">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text minw-label" id="basic-addon1"><i class="fab fa-slack"></i> Código</span>
                                        </div>
                                        <input name="hash" id="codigo" type="text" autocomplete="off" class="form-control" maxlength="20" size="20" placeholder="Código" aria-label="N° de Matrícula" aria-describedby="basic-addon1" value="<?php echo $code; ?>" />
                                    </div>
                                    <div class="col-12 mt-1 mb-n4">
                                        <button type="submit" class="btn btn-success float-right">Validar <i class="far fa-check-circle"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js" integrity="sha384-6khuMg9gaYr5AxOqhkVIODVIvm9ynTT5J4V1cfthmT+emCG6yVmEZsRHdxlotUnm" crossorigin="anonymous"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js"></script>
        <script type="text/javascript" src="../js/cleave.js"></script>
        <script type="text/javascript" src="../js/main.js"></script>
        <script type="text/javascript" src="../js/jquery.base64.js"></script>
    </body> 
</html>