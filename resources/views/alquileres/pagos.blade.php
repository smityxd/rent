@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <select class="form-control" id="contratos" name="contratos">
                    <option value="">[Seleccionar Contrato]</option>
                </select>
                <hr>
                <div id="datosContrato"></div>
            </div>
        </div>
    </div>
</div>
<div class="row detalle d-none">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                CRONOGRAMA DE PAGOS
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="miTablaPagos" class="table table-sm table-bordered">
                        <thead>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('modal1')
<div class="card">
    <form id="formid" class="form-horizontal">
        <input type="hidden" id="frm_id" name="id" value="0">
        <input type="hidden" id="frm_pago_id" name="pago_id" value="0">
        <input type="hidden" id="frm_contrato_id" name="contrato_id" value="0">
        <div class="card-body">
            <h4 class="card-title">Pago</h4>
            <div class="form-group row">
                <label for="frm_fecha_pago" class="col-sm-3 text-end control-label col-form-label">Fecha de Pago</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" id="frm_fecha_pago" name="fecha_pago" placeholder="Fecha de Pago">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_renta" class="col-sm-3 text-end control-label col-form-label">Renta S/.</label>
                <div class="col-sm-9">
                    <input type="number" class="form-control" id="frm_renta" name="renta" placeholder="Renta">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_servicios" class="col-sm-3 text-end control-label col-form-label">Servicios S/.</label>
                <div class="col-sm-9">
                    <input type="number" class="form-control" id="frm_servicios" name="servicios" placeholder="Servicios">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_otros_pagos" class="col-sm-3 text-end control-label col-form-label">Otros Pagos
                    S/.</label>
                <div class="col-sm-9">
                    <input type="number" class="form-control" id="frm_otros_pagos" name="otros_pagos" placeholder="Otros Pagos">
                </div>
            </div>
            <div class="form-group row">
                <label for="frm_comentario" class="col-sm-3 text-end control-label col-form-label">Comentario</label>
                <div class="col-sm-9">
                    <textarea class="form-control" id="frm_comentario" name="comentario" placeholder="Comentario" maxlength="255"></textarea>
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

@section('modal2')
<div class="card">
    <div id="recibo"></div>
</div>
@endsection

@section('scripts')
<script>
let contratos = @json($contratos);
let urlRecibo = "{{ route('alquileres.formatos.descargar-recibo') }}";
</script>
<script src="{{ asset('matrix/js/pagos.js') }}"></script>
@endsection