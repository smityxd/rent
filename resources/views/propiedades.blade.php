@extends('layouts.app')

@section('title', 'Propiedades')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button type="button" id="nuevoBtn" class="btn btn-success text-white btn-lg">Crear <i class="mdi mdi-plus"></i></button>
                <button type="button" id="actualizarBtn" class="btn btn-warning btn-lg">Actualizar <i class="mdi mdi-refresh"></i></button>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-0">Mis Propiedades</h5>
            </div>
            <table id="miTabla" class="table">
                <thead>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modal1')
<div class="card">
    <form id="formid" class="form-horizontal">
        <input type="hidden" id="frm_id" name="id" value="0">
        <div class="card-body">
            <h4 class="card-title">Propiedades</h4>
            <div class="form-group row">
                <label for="frm_nombre" class="col-sm-3 text-end control-label col-form-label">Nombre</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_nombre" name="nombre" placeholder="Nombre">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_ubigeo_id" class="col-sm-3 text-end control-label col-form-label">Ubigeo</label>
                <div class="col-sm-9">
                    <select class="form-control" id="frm_ubigeo_id" name="ubigeo_id">
                        <option value="">Seleccione una opción</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_direccion" class="col-sm-3 text-end control-label col-form-label">Direccion</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_direccion" name="direccion" placeholder="Direccion">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_descripcion" class="col-sm-3 text-end control-label col-form-label">Descripcion</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="frm_descripcion" name="descripcion" placeholder="Descripcion"></textarea>
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
            <div class="card-body">
                <button type="submit" id="guardarBtn" class="btn btn-primary">
                    Guardar
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let ubigeos = JSON.parse(@json($ubigeo));
</script>
<script src="{{ asset('matrix/js/propiedades.js') }}"></script>
@endsection