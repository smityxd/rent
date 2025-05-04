@extends('layouts.app')

@section('title', 'Propiedades')

@section('content')
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Mis Datos</h4>
                <form id="formid" class="form-horizontal">
                    <input type="hidden" id="frm_updated_at" name="updated_at" value="">
                    <div class="form-group row">
                        <label for="frm_nombres" class="col-sm-3 text-end control-label col-form-label">Nombres</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control crm" id="frm_nombres" name="nombres" placeholder="Nombres">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_apellidos" class="col-sm-3 text-end control-label col-form-label">Apellidos</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control crm" id="frm_apellidos" name="apellidos" placeholder="Apellidos">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_documento" class="col-sm-3 text-end control-label col-form-label">Documento</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control crm" id="frm_documento" name="documento" placeholder="Documento">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_direccion" class="col-sm-3 text-end control-label col-form-label">Dirección</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control crm" id="frm_direccion" name="direccion" placeholder="Dirección">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_ubigeo_id" class="col-sm-3 text-end control-label col-form-label">Ubigeo</label>
                        <div class="col-sm-9">
                            <select class="form-control crm" id="frm_ubigeo_id" name="ubigeo_id">
                                <option value="">Seleccione una opción</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_email" class="col-sm-3 text-end control-label col-form-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control crm" id="frm_email" name="email" placeholder="Email">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_telefono" class="col-sm-3 text-end control-label col-form-label">Celular</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control crm" id="frm_telefono" name="telefono" placeholder="Teléfono">
                        </div>
                    </div>
                    
                    <div class="border-top">
                        <div class="card-body d-flex justify-content-end">
                            <button type="submit" id="guardarBtn" class="btn btn-primary btn-lg">
                                Guardar <i class="fa fa-save"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Contraseña</h4>
                <form id="formid2" class="form-horizontal">
                    <input type="hidden" id="frm_updated_at2" name="updated_at" value="">
                    <div class="form-group row">
                        <label for="frm_password0" class="col-sm-3 text-end control-label col-form-label">Contraseña Actual</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control crm" id="frm_password0" name="password0" placeholder="Contraseña Actual">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_password" class="col-sm-3 text-end control-label col-form-label">Nueva Contraseña</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control crm" id="frm_password" name="password" placeholder="Nueva Contraseña">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="frm_password1" class="col-sm-3 text-end control-label col-form-label">Repetir Contraseña</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control crm" id="frm_password1" name="password1" placeholder="Repetir Contraseña">
                        </div>
                    </div>
                    
                    <div class="border-top">
                        <div class="card-body d-flex justify-content-end">
                            <button type="submit" id="resetpassBtn" class="btn btn-primary btn-lg">
                                Cambias Contraseña <i class="fa fa-key"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let usuario = @json($usuario);
    let ubigeos = @json($ubigeos);
</script>
<script src="{{ asset('matrix/js/profile.js') }}"></script>
@endsection