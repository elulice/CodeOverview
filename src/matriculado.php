<?php

$ret = "<div class='row'>";
$ret .= "    <div class='col-12 text-center'>";
$ret .= "        <div class='card card-shadow'>";
$ret .= "            <div class='card-header' data-id='card-header-container'>";
$ret .= "                <h5 class='card-title'><i class='fas fa-door-open'></i> Bienvenido/a <br/>$nombre.</h5>";
$ret .= "                <span class='logout-member-button-container' data-id='member-logout' alt='Cerrar Sesión' title='Cerrar Sesión'>Cerrar sesión <i class='fas fa-sign-out-alt logout-member-button'></i></span>";
if ($extranetReg == '1'):
    $ret .= "                <span class='modify-password-container' data-id='modify-password' alt='Cambiar Clave' title='Cambiar Clave'><i class='fas fa-key modify-password-button'></i></span>";
endif;
$ret .= "            </div>";
$ret .= "            <div class='card-body' data-id='card-body-container'>";
$ret .= "                <h5 class='card-title mt-n2 card-title-style'>Por favor seleccione una opción</h5>";
$ret .= "                <form data-form='mmatriculado'>";
$ret .= "                    <input type='hidden' name='member' value='$member' />";
$ret .= "                    <input type='hidden' name='regional' value='$regional' />";
$ret .= "                    <div class='form-group row'>";
$ret .= "                        <div class='mt-1 mb-n4 buttons-container'>";
$ret .= "                            <div class='row m-0'>";
if ($showPptButton):
    $ret .= "                                <div class='col'>";
    $ret .= "                                    <div data-button='print' data-action='pay_online' class='btn btn-warning'><i class='fas fa-money-check-alt'></i>";
    $ret .= "                                        <span>Pago en línea</span>";
    $ret .= "                                    </div>";
    $ret .= "                                </div>";
endif;
$ret .= "                                <div class='col $showFirstButton'>";
$ret .= "                                    <div data-button='print' data-action='$action' class='btn btn-success'><i class='fas fa-print'></i>";
$ret .= "                                        <span>$txtButton</span>";
$ret .= "                                    </div>";
$ret .= "                                </div>";
if ($extranetReg == '1'): // Si el usuario esta registrado
    $ret .= "                                <div class='col'>";
    $ret .= "                                    <div data-button='print' data-action='imprimir_credencial' class='btn btn-info'><i class='fas fa-id-card-alt'></i>";
    $ret .= "                                        <span>Imprimir Credencial</span>";
    $ret .= "                                    </div>";
    $ret .= "                                </div>";
endif;
$ret .= "                                <div class='col $showSecondButton'>";
$ret .= "                                    <div data-button='print' data-action='$actionSb' class='btn btn-primary float-left'><i class='fas fa-print'></i>";
$ret .= "                                        <span>$txtButtonSb</span>";
$ret .= "                                    </div>";
$ret .= "                                </div>";
if ($extranetReg == '1'): // Si el usuario esta registrado
    $ret .= "                                <div class='col'>";
    $ret .= "                                    <div data-button='request' data-action='personal-data-list' class='btn btn-dark'><i class='fas fa-list-alt'></i>";
    $ret .= "                                        <span>Solicitudes de Datos Personales</span>";
    $ret .= "                                    </div>";
    $ret .= "                                </div>";
endif;
if ($extranetReg != '1'): // Si el usuario no tiene ninguna opcion disponible
    $ret .= "                                    <div class='row m-0' data-id='message-register'>";
    $ret .= "                                       <div class='col-12'>";
    $ret .= "                                           <div class='jumbotron'>";
    $ret .= "                                               <h3>¡ Registrate !</h3>";
    $ret .= "                                               <p class='lead'>Generá tu clave y accedé a todas las funciones.</p>";
//    $ret .= "                                             <hr class='my-4'>";
//    $ret .= "                                             <p>Solo te llevará unos segundos.</p>";
    $ret .= "                                               <p>";
    $ret .= "                                                   <button class='btn btn-success btn-sm btn-sign-up' data-ref='sign-up' data-id='$member' type='button'>Registrarme <i class='fas fa-sign-in-alt'></i></button>";
    $ret .= "                                               </p>";
    $ret .= "                                           </div>";
    $ret .= "                                       </div>";
    $ret .= "                                    </div>";
    // PRUEBA PARA PAYPERTIC - QUITAR AL FINALIZAR
    $ret .= "                                    <div class='row m-0' data-id='message-register'>";
    $ret .= "                                       <div class='col-12'>";
    $ret .= "                                           <div class='jumbotron'>";
    $ret .= "                                               <p>";
    $ret .= "                                                   <button class='btn btn-success btn-sm btn-sign-up' data-id='paypertic' data-member='$member' type='button'>PAYPERTIC <i class='fas fa-sign-in-alt'></i></button>";
    $ret .= "                                               </p>";
    $ret .= "                                           </div>";
    $ret .= "                                       </div>";
    $ret .= "                                    </div>";
// FIN PRUEBA PARA PAYPERTIC - QUITAR AL FINALIZAR
endif;
$ret .= "                            </div>";
$ret .= "                        </div>";
$ret .= "                    </div>";
$ret .= "                </form>";
$ret .= "            </div>";
$ret .= "        </div>";
$ret .= "    </div>";
$ret .= "</div>";
echo $ret;
