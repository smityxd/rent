@extends('layouts.app')

@section('title', 'Dashboard principal')

@section('content')
<style>
.dashboard-card { border: 0; box-shadow: 0 3px 12px rgba(0,0,0,.08); }
.metric-icon { width: 48px; height: 48px; border-radius: 12px; display:flex; align-items:center; justify-content:center; font-size:24px; }
.progress-thin { height: 8px; }
.gain-bar { height: 14px; border-radius: 7px; background: #e9ecef; overflow:hidden; }
.gain-bar-fill { height:100%; background: linear-gradient(90deg,#36bea6,#1e88e5); }
.money { font-variant-numeric: tabular-nums; }
.table-dashboard th { font-size:12px; text-transform:uppercase; color:#6c757d; white-space:nowrap; }
.table-dashboard td { vertical-align:middle; }
</style>

<div class="row g-3">
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card"><div class="card-body d-flex align-items-center"><div class="metric-icon bg-light-primary text-primary me-3"><i class="mdi mdi-home-modern"></i></div><div><small class="text-muted">Propiedades</small><h3 class="mb-0">{{ $resumen->propiedades }}</h3></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card"><div class="card-body d-flex align-items-center"><div class="metric-icon bg-light-info text-info me-3"><i class="mdi mdi-door-open"></i></div><div><small class="text-muted">Unidades</small><h3 class="mb-0">{{ $resumen->unidades }}</h3></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card"><div class="card-body d-flex align-items-center"><div class="metric-icon bg-light-success text-success me-3"><i class="mdi mdi-account-group"></i></div><div><small class="text-muted">Inquilinos</small><h3 class="mb-0">{{ $resumen->inquilinos }}</h3></div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="card dashboard-card"><div class="card-body d-flex align-items-center"><div class="metric-icon bg-light-warning text-warning me-3"><i class="mdi mdi-file-document"></i></div><div><small class="text-muted">Contratos vigentes</small><h3 class="mb-0">{{ $resumen->contratos_vigentes }}</h3></div></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4"><div class="card dashboard-card bg-warning text-white"><div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-white mb-1">Contratos por vencer</h6><h2 class="text-white mb-0">{{ $resumen->contratos_por_vencer }}</h2></div><i class="mdi mdi-calendar-clock fs-1"></i></div><small>Próximos 30 días</small></div></div></div>
    <div class="col-md-4"><div class="card dashboard-card bg-danger text-white"><div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-white mb-1">Contratos vencidos</h6><h2 class="text-white mb-0">{{ $resumen->contratos_vencidos }}</h2></div><i class="mdi mdi-calendar-remove fs-1"></i></div><small>Requieren atención</small></div></div></div>
    <div class="col-md-4"><div class="card dashboard-card bg-success text-white"><div class="card-body"><div class="d-flex justify-content-between"><div><h6 class="text-white mb-1">Ocupación</h6><h2 class="text-white mb-0">{{ $porcentaje_ocupacion }}%</h2></div><i class="mdi mdi-key-variant fs-1"></i></div><small>{{ $resumen->unidades_ocupadas }} de {{ $resumen->unidades }} unidades ocupadas</small></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card dashboard-card"><div class="card-body"><div class="d-flex justify-content-between align-items-center mb-3"><div><h5 class="card-title mb-0">Ingresos cobrados</h5><small class="text-muted">Últimos 12 meses</small></div></div>
        @php $maxIngreso = max(collect($ingresos_mensuales)->max('total'), 1); @endphp
        @foreach($ingresos_mensuales as $ingreso)
            <div class="d-flex align-items-center mb-2"><div style="width:70px" class="small text-muted">{{ $ingreso['periodo'] }}</div><div class="gain-bar flex-grow-1"><div class="gain-bar-fill" style="width: {{ $maxIngreso > 0 ? round(($ingreso['total']/$maxIngreso)*100,1) : 0 }}%"></div></div><div class="money text-end ms-2" style="width:100px">S/ {{ number_format($ingreso['total'],2,'.',',') }}</div></div>
        @endforeach
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card dashboard-card"><div class="card-body"><h5 class="card-title mb-3">Cobranza del mes</h5><div class="d-flex justify-content-between mb-2"><span class="text-muted">Cobrado</span><strong class="text-success money">S/ {{ number_format($cobranza_mes['cobrado'],2,'.',',') }}</strong></div><div class="d-flex justify-content-between mb-2"><span class="text-muted">Pendiente</span><strong class="text-warning money">S/ {{ number_format($cobranza_mes['pendiente'],2,'.',',') }}</strong></div><div class="progress progress-thin mb-2"><div class="progress-bar bg-success" style="width: {{ $cobranza_mes['porcentaje_cobrado'] }}%"></div></div><div class="d-flex justify-content-between"><small>Por cobrar total</small><strong class="money">S/ {{ number_format($cobranza_mes['por_cobrar'],2,'.',',') }}</strong></div></div></div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12"><div class="card dashboard-card"><div class="card-body"><h5 class="card-title">Ganancias por propiedad</h5><div class="table-responsive"><table class="table table-hover table-dashboard mb-0"><thead><tr><th>Propiedad</th><th class="text-end">Ganado este mes</th><th class="text-end">Ganancia total</th><th style="width:35%">Participación</th></tr></thead><tbody>@php $maxGanancia = max(collect($ganancias_por_propiedad)->max('ganado_total'), 1); @endphp @forelse($ganancias_por_propiedad as $ganancia)<tr><td>{{ $ganancia->nombre }}</td><td class="text-end text-success money">S/ {{ number_format($ganancia->ganado_mes,2,'.',',') }}</td><td class="text-end money">S/ {{ number_format($ganancia->ganado_total,2,'.',',') }}</td><td><div class="gain-bar"><div class="gain-bar-fill" style="width: {{ round(($ganancia->ganado_total/$maxGanancia)*100,1) }}%"></div></div></td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No hay propiedades registradas</td></tr>@endforelse</tbody></table></div></div></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7"><div class="card dashboard-card"><div class="card-body"><h5 class="card-title">Contratos por vencer</h5><div class="table-responsive"><table class="table table-hover table-dashboard mb-0"><thead><tr><th>Contrato</th><th>Inquilino</th><th>Propiedad / Unidad</th><th>Vencimiento</th><th>Días</th></tr></thead><tbody>@forelse($contratos_por_vencer as $contrato)<tr><td>{{ $contrato->codigo }}</td><td>{{ $contrato->nombres }} {{ $contrato->apellidos }}</td><td>{{ $contrato->propiedad }} / {{ $contrato->unidad }}</td><td>{{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }}</td><td><span class="badge bg-warning text-dark">{{ $contrato->dias_restantes }} días</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No hay contratos próximos a vencer</td></tr>@endforelse</tbody></table></div></div></div></div>
    <div class="col-lg-5"><div class="card dashboard-card"><div class="card-body"><h5 class="card-title">Contratos vencidos</h5><div class="table-responsive"><table class="table table-hover table-dashboard mb-0"><thead><tr><th>Contrato</th><th>Inquilino</th><th>Venció</th></tr></thead><tbody>@forelse($contratos_vencidos as $contrato)<tr><td>{{ $contrato->codigo }}</td><td>{{ $contrato->nombres }} {{ $contrato->apellidos }}</td><td><span class="badge bg-danger">Hace {{ $contrato->dias_vencido }} días</span></td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">No hay contratos vencidos</td></tr>@endforelse</tbody></table></div></div></div></div>
</div>

<div class="row g-3 mt-1 mb-3"><div class="col-12"><div class="card dashboard-card"><div class="card-body"><h5 class="card-title">Últimos pagos registrados</h5><div class="table-responsive"><table class="table table-hover table-dashboard mb-0"><thead><tr><th>Fecha</th><th>Inquilino</th><th>Propiedad / Unidad</th><th class="text-end">Monto</th></tr></thead><tbody>@forelse($pagos_recientes as $pago)<tr><td>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</td><td>{{ $pago->nombres }} {{ $pago->apellidos }}</td><td>{{ $pago->propiedad }} / {{ $pago->unidad }}</td><td class="text-end text-success money">S/ {{ number_format($pago->total,2,'.',',') }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-4">No hay pagos registrados</td></tr>@endforelse</tbody></table></div></div></div></div></div>
@endsection
