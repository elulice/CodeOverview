<div class="card-header">
    <h5 class="card-title" data-id="card-container-title">
        <i class="fas fa-key"></i> Ingrese su nueva clave
    </h5>
</div>
<div class="card-body" data-id="card-container-body">
    <!--<h5 class="card-title mt-n2"><i class="far fa-list-alt"></i> <span data-id="login-subtitle">Clave</span></h5>-->
    <form data-form="psubmit">
        <div class="form-group row">
            <div class="input-group mb-1 col-12" data-id="new-pass">
                <div class="input-group-prepend">
                    <span class="input-group-text minw-label" id="basic-addon1"><i class="fas fa-lock"></i> Clave</span>
                </div>
                <input name="psw_extra" data-id="new-pass-input" type="password" autocomplete="off" class="form-control" placeholder="Ingrese su nueva Clave" aria-label="Ingrese su nueva Clave" aria-describedby="basic-addon1" />
            </div>
            <div class="input-group mb-1 col-12" data-id="new-pass-repeat">
                <div class="input-group-prepend">
                    <span class="input-group-text minw-label" id="basic-addon2"><i class="fas fa-lock"></i> Repetir</span>
                </div>
                <input name="psw_extra_bis" data-id="new-pass-repeat-input" type="password" autocomplete="off" class="form-control" placeholder="Repita la nueva Clave" aria-label="Repita la nueva Clave" aria-describedby="basic-addon2" />
                <input name="cod_member" data-id="new-pass-cod-member" type="text" autocomplete="off" class="d-none"/>
                <input name="password" data-id="new-pass-psw-link" type="password" autocomplete="off" class="d-none"/>
            </div>
            <div class="col-12 mt-1 mb-n4">
                <button type="button" data-id='new-pass-save' class="btn btn-success float-right"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" data-id='new-pass-skip' class="btn btn-info float-right mr-1">Omitir <i class="fas fa-angle-double-right"></i></button>
            </div>
        </div>
    </form>
</div>
