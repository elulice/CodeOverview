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

            <div class='col'><button type="button" class="btn btn-info row float-right mt-2 mb-n4 mr-1" data-id="personal-data-modify"><i class="fas fa-user-cog"></i> Modificar Datos Personales</button></div>
            <div class='card-body' data-id='card-body-container'></div>
        </div>
    </div>
</div>