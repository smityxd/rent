@extends('layouts.app')

@section('title', 'Propiedades')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button type="button" id="nuevoBtn" class="btn btn-success text-white btn-lg">Crear <i class="fa fa-plus-circle" aria-hidden="true"></i></button>
                <button type="button" id="actualizarBtn" class="btn btn-warning btn-lg">Actualizar <i
                        class="mdi mdi-refresh"></i></button>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-0">Mis Propiedades</h5>
            </div>
            <div class="table-responsive">
                <table id="miTabla" class="table">
                    <thead>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal1')
<div class="card">
    <form id="formid" class="form-horizontal">
        <input type="hidden" id="frm_id" name="id" value="0">
        <div class="card-body">
            <h4 class="card-title">Contrato</h4>
            <div class="form-group row">
                <label for="frm_inquilino_id" class="col-sm-3 text-end control-label col-form-label">Inquilino</label>
                <div class="col-sm-9">
                    <select class="form-control" id="frm_inquilino_id" name="inquilino_id">
                        <option value="">Seleccione una opción</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_unidad_id" class="col-sm-3 text-end control-label col-form-label">Unidad</label>
                <div class="col-sm-9">
                    <select class="form-control" id="frm_unidad_id" name="unidad_id">
                        <option value="">Seleccione una opción</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_fecha_inicio" class="col-sm-3 text-end control-label col-form-label">Fecha Inicio</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_fecha_inicio" name="fecha_inicio" placeholder="Fecha Inicio">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_duracion" class="col-sm-3 text-end control-label col-form-label">Duración (Meses)</label>
                <div class="col-sm-9">
                    <input type="numeric" class="form-control" id="frm_duracion" name="duracion" placeholder="Duración">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_fecha_fin" class="col-sm-3 text-end control-label col-form-label">Fecha Fin</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_fecha_fin" name="fecha_fin" placeholder="Fecha Fin" readonly>
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_renta" class="col-sm-3 text-end control-label col-form-label">Renta S/.</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_renta" name="renta" placeholder="Renta">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_servicios" class="col-sm-3 text-end control-label col-form-label">Servicios S/.</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_servicios" name="servicios" placeholder="Servicios">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_comentarios" class="col-sm-3 text-end control-label col-form-label">Comentarios</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="frm_comentarios" name="comentarios" placeholder="Comentarios" maxlength="255"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="frm_generar" name="generar" checked>
                        <label class="form-check-label" for="frm_generar">
                            Generar Cronograma de Pagos
                        </label>
                    </div>
                    <small class="text-danger">Al generar cronograma, reemplazará cualquier cronograma existente.</small>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="frm_estado" name="estado" checked>
                        <label class="form-check-label" for="frm_estado">
                            Activo
                        </label>
                    </div>
                </div>
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
@endsection

@section('scripts')
<script>
    let inquilinos = @json($inquilinos);
    let unidades = @json($unidades);
</script>
<script src="{{ asset('matrix/js/contratos.js') }}"></script>
@endsection