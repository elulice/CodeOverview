<!DOCTYPE HTML>
<html>
    <?php include('src/head.index.inc'); ?>
    <?php include('src/session.index.inc'); ?>
    <body>
        <!-- Alert Div -->
        <div class="alert alert-primary alert-style" data-id="alert-notif" role="alert"></div>

        <!-- Overlay. "Block" actions with the webpage -->
        <div class="overlay" data-id="overlay"></div>

        <!-- HEADER ONLY FOR PROTOTYPE -->
        <!--<span class="proto-header">******** PROTOTIPO ********</span>-->

        <!-- Header -->
        <nav class="navbar sticky-top navbar-dark bg-header-navbar mt-n1 shadow-lg p-35">
            <a class="navbar-brand" href="<?php echo $cfg['home_link']; ?>">
                <div class="header-logo"></div>
                <div class="header-title">
                    Autogestión para Matriculados
                    <span class="header-subtitle">Colegio de Psicopedagogos de Córdoba</span>
                </div>
            </a>
        </nav>

        <!-- Page Content -->
        <div class="container mt-3" data-id="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="card card-shadow" data-id="card-container">
                        <div class="card-header" data-id="card-header-container">
                            <h5 class="card-title" data-id="card-container-title">
                                <i class="fas fa-server"></i> Ingrese sus datos
                            </h5>
                        </div>
                        <div class="card-body" data-id="card-container-body">
                            <h5 class="card-title mt-n2"><i class="far fa-list-alt"></i> <span data-id="login-subtitle">Matrícula</span></h5>
                            <form data-form="msubmit">
                                <div class="form-group row">
                                    <div class="input-group mb-1 col-12" data-id="login-tipo-matricula">
                                        <div class="input-group-prepend">
                                            <label class="input-group-text minw-label" for="inputGroupSelect01"><i class="fas fa-th-list"></i> Tipo</label>
                                        </div>
                                        <select name="tipo_matricula" data-id="login-input-tipo-matricula" class="custom-select form-control" id="inputGroupSelect01">
                                            <option value="">Tipo de Matrícula</option>
                                            <option value="P">P</option>
                                            <option value="D">D</option>
                                            <option value="PE">PE</option>
                                            <option value="DE">DE</option>
                                            <option value="ADH">ADH</option>
                                        </select>
                                    </div>
                                    <div class="input-group mb-1 col-12" data-id="login-nro-matricula">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text minw-label" id="basic-addon1"><i class="fab fa-slack"></i> N°</span>
                                        </div>
                                        <input name="matricula" data-id="login-input-nro-matricula" type="number" autocomplete="off" class="form-control" maxlength="8" size="8" placeholder="N° de Matrícula" aria-label="N° de Matrícula" aria-describedby="basic-addon1" />
                                    </div>
                                    <div class="input-group mb-1 col-12 d-none" data-id="login-dni">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text minw-label" id="basic-addon2"><i class="far fa-address-card"></i> DNI</span>
                                        </div>
                                        <input name="dni" data-id="login-input-dni" type="number" autocomplete="off" class="form-control" maxlength="8" size="8" placeholder="Ingrese su DNI" aria-label="Ingrese su DNI" aria-describedby="basic-addon2" />
                                    </div>
                                    <div class="input-group mb-1 col-12 d-none" data-id="login-pass">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text minw-label" id="basic-addon3"><i class="fas fa-lock"></i> Clave</span>
                                        </div>
                                        <input name="password" data-id="login-input-pass" type="password" autocomplete="new-password" class="form-control" placeholder="**********" aria-label="Ingrese su Clave" aria-describedby="basic-addon3" value="" />
                                        <div class="col-12 pr-0">
                                            <span class="recover-pass" data-id="recover-pass">¿Olvidaste tu Clave?</span>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-1 mb-n4">
                                        <button type="button" data-id="login-help" class="btn btn-warning float-left"><i class="fas fa-question-circle"></i> Ayuda</button>
                                        <button type="button" data-id='login-next' class="btn btn-success float-right">Siguiente <i class="fas fa-arrow-right"></i></button>
                                        <button type="submit" data-id='login-submit' class="btn btn-success float-right d-none">Ingresar <i class="far fa-check-circle"></i></button>
                                        <button type="button" data-id='login-back' class="btn btn-primary float-left d-none"><i class="fas fa-arrow-left"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include('src/footer.inc'); ?>
    </body> 
</html>
