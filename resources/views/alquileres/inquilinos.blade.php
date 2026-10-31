@extends('layouts.app')

@section('title', 'Inquilinos')

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
            <h4 class="card-title">Inquilino</h4>
            <div class="form-group row">
                <label for="frm_nombre" class="col-sm-3 text-end control-label col-form-label">Nombres</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_nombres" name="nombres" placeholder="Nombres">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_apellidos" class="col-sm-3 text-end control-label col-form-label">Apellidos</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_apellidos" name="apellidos" placeholder="Apellidos">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_dni" class="col-sm-3 text-end control-label col-form-label">DNI</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_dni" name="dni" placeholder="DNI">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_observaciones" class="col-sm-3 text-end control-label col-form-label">Observaciones</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="frm_observaciones" name="observaciones" placeholder="Observaciones"></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="frm_estado" name="estado"
                            checked>
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
<script src="{{ asset('matrix/js/inquilinos.js') }}"></script>
@endsection