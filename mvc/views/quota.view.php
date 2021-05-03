<div class='row'>
    <div class='col-12 text-center'>
        <div class='card card-shadow'>
            <div class='card-header' data-id='card-header-container'>
                <h5 class='card-title'><i class='fas fa-door-open'></i> Bienvenido/a <br/><?php echo $_SESSION['nombre']; ?>.</h5>
                <div class="row inside-section-header-buttons">
                    <div class="col-1 pl-0 pr-0">
                        <span class='back-button' data-id='go-back-button' alt='Volver' title='Volver'>Atrás<i class="fas fa-chevron-circle-left"></i></span>
                    </div>
                    <div class="col-11 pl-0 pr-0">
                        <span class='logout-member-button-container' data-id='member-logout-two' alt='Cerrar Sesión' title='Cerrar Sesión'>Cerrar sesión <i class='fas fa-sign-out-alt logout-member-button'></i></span>
                        <span class='modify-password-container' data-id='modify-password-two' alt='Cambiar Clave' title='Cambiar Clave'><i class='fas fa-key modify-password-button'></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <ul class="nav nav-tabs pt-3">
                    <li class="nav-item">
                        <a href="#pending_quotes" class="nav-link active" data-toggle="tab">Cuotas Pendientes</a>
                    </li>
                    <li class="nav-item">
                        <a href="#history_quotes" class="nav-link" data-toggle="tab">Historial</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="tab-pane active" id="pending_quotes">
                    <div class="col-lg-12 overflow-auto max-vh-50 quotes-class border-bottom border-light">
                        <div data-id='quote-pending-body-container'></div>
                        <p class="clearfix"></p>
                    </div>
                    <div class="col-12 p-1 pt-2">
                        <div class='row mt-1 ml-2 mr-2 pt-1 pb-1 quotes-selected-row font-weight-bold'>
                            <div class='col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12 text-center'>Cuotas Seleccionadas: <span data-id='quotes-amount-selected' id="quotes-amount-selected">0</span></div>
                            <div class='col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12 text-center'>Monto Cuotas Puras: $<span data-id='quotes-pay-amount-selected'>0.00</span></div>
                            <div class='col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12 text-center'>Monto Intereses: $<span data-id='quotes-pay-interest-amount-selected'>0.00</span></div>
                            <div class='col-12 col-lg-3 col-md-12 col-sm-12 col-xs-12 text-center'>Total: $<span data-id='quotes-pay-amount-total-selected'>0.00</span></div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-right">
                                <button type="button" class="btn btn-success mr-2 mt-2" data-action="" data-id="quote-pay-button" disabled="disabled"><i class="fas fa-comment-dollar"></i> Pagar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane mb-4" id="history_quotes">
                    <div class="col-lg-12 overflow-auto max-vh-50 quotes-class border-bottom border-light">
                        <div data-id='quote-history-body-container'></div>
                        <p class="clearfix"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>