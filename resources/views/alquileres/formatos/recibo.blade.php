<h1>Mensualidad - Pago #{{ $id}}</h1>
<p>
    <strong>Propiedad:</strong> {{ $infoContrato['p_desc'] }}<br>
    <strong>Unidad Habitacional:</strong> {{ $infoContrato['u_desc'] }}<br>
    <strong>Fecha Inicio:</strong> {{ $infoContrato['fecha_inicio'] }}<br>
    <strong>Fecha Fin:</strong> {{ $infoContrato['fecha_fin'] }}<br>
    <strong>Inquilino:</strong> {{ $infoContrato['nombres'] }} {{ $infoContrato['apellidos'] }}<br>
    <strong>Estado Contrato:</strong> {{ $infoContrato['estado'] }}
</p>
<table class="table table-bordered recibo">
    <tr class="bg-{{ $pago->estado_color }} {{ $pago->estado_color }} text-white">
        <th>Estado de Pago</th>
        <td colspan="3">{{ $pago->estado_ }}</td>
    </tr>
    <tr>
        <th>Fecha de pago</th>
        <td colspan="3">{{ $pago->fecha_pago }}</td>
    </tr>
    <tr>
        <th>Concepto</th>
        <td>Por Pagar</td>
        <td>Pagado</td>
        <td class="pendiente">Pendiente</td>
    </tr>
    <tr>
        <th>Renta S/.</th>
        <td>{{ $pago->renta }}</td>
        <td>{{ $pago->renta_pagado }}</td>
        <td class="pendiente">{{ $pago->renta_saldo }}</td>
    </tr>
    <tr>
        <th>Servicios S/.</th>
        <td>{{ $pago->servicios }}</td>
        <td>{{ $pago->servicios_pagado }}</td>
        <td class="pendiente">{{ $pago->servicios_saldo }}</td>
    </tr>
    <tr>
        <th>Otros Pagos S/.</th>
        <td>{{ $pago->otros_pagos }}</td>
        <td>{{ $pago->otros_pagos_pagado }}</td>
        <td class="pendiente">{{ $pago->otros_pagos_saldo }}</td>
    </tr>
    <tr>
        <th>Total</th>
        <td>{{ number_format($pago->renta + $pago->servicios + $pago->otros_pagos,2) }}</td>
        <td>{{ number_format($pago->renta_pagado + $pago->servicios_pagado + $pago->otros_pagos_pagado,2) }}</td>
        <td class="pendiente">{{ number_format($pago->renta_saldo + $pago->servicios_saldo + $pago->otros_pagos_saldo,2) }}</td>
    </tr>
    <tr>
        <th>Comentario</th>
        <td colspan="3"><?=empty($pago->comentario)?'Sin Comentario':$pago->comentario?></td>
    </tr>
    <tr class="bg-{{ $pago->estado_color }} {{ $pago->estado_color }} text-white">
        <th>Vencimiento</th>
        <td colspan="3">{{ $pago->estado_fecha }}</td>
    </tr>
</table>
<h3>Pagos Realizados</h3>
@if(count($rsp)==0)
    No se realizaron pagos
@else
    <table class="table table-bordered recibo">
        <tr>
            <th>Fecha</th>
            <th>Renta S/.</th>
            <th>Servicios S/.</th>
            <th>Otros S/.</th>
            <th>Comentario</th>
        </tr>
    @foreach($rsp as $pag)
        <tr>
            <td>{{ $pag->fecha_pago }}</td>
            <td>{{ $pag->renta }}</td>
            <td>{{ $pag->servicios }}</td>
            <td>{{ $pag->otros_pagos }}</td>
            <td>{{ $pag->comentario }}</td>
        </tr>
    @endforeach
    </table>
@endif