<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class FormatoRepository
{
    public function contratoPerteneceUsuario(int $contratoId, int $usuarioId): bool
    {
        return DB::table('contratos')
            ->join('unidades', 'unidades.id', '=', 'contratos.unidad_id')
            ->join('propiedades', 'propiedades.id', '=', 'unidades.propiedad_id')
            ->where('contratos.id', $contratoId)
            ->where('propiedades.propietario_id', $usuarioId)
            ->exists();
    }

    public function datosContrato(int $contratoId): ?object
    {
        return DB::selectOne("SELECT
            CONCAT(us.nombres,', ',us.apellidos) AS propietario_nombre,
            us.documento AS propietario_dni,
            CONCAT(us.direccion,' ',COALESCE(ubius.distrito,''),' - ',COALESCE(ubius.provincia,''),' - ',COALESCE(ubius.departamento,'')) AS propietario_direccion,
            CONCAT(i.nombres,', ',i.apellidos) AS inquilino_nombre,
            i.dni AS inquilino_dni,
            CONCAT(pr.direccion,' ',COALESCE(ubipr.distrito,''),' - ',COALESCE(ubipr.provincia,''),' - ',COALESCE(ubipr.departamento,'')) AS propiedad_direccion,
            un.descripcion AS unidad_nombre,
            pr.nombre AS propiedad_nombre,
            c.duracion AS meses,
            DATE_FORMAT(c.fecha_inicio,'%d/%m/%Y') AS inicio,
            DATE_FORMAT(c.fecha_fin,'%d/%m/%Y') AS fin,
            DATE_FORMAT(c.fecha_inicio,'%d') AS dia_pago,
            c.renta,
            c.servicios,
            DATE_FORMAT(DATE_SUB(c.fecha_inicio,INTERVAL 1 DAY),'%d') AS dia_servicio
            FROM contratos c
            INNER JOIN inquilinos i ON i.id=c.inquilino_id
            INNER JOIN unidades un ON un.id=c.unidad_id
            INNER JOIN propiedades pr ON pr.id=un.propiedad_id
            INNER JOIN usuarios us ON us.id=pr.propietario_id
            LEFT JOIN ubigeos ubius ON us.ubigeo_id=ubius.codigo
            LEFT JOIN ubigeos ubipr ON pr.ubigeo_id=ubipr.codigo
            WHERE c.id=?", [$contratoId]);
    }

    public function formatoPersonalizado(int $contratoId): ?object
    {
        return DB::table('formatos')->select('id','contenido')->where('contrato_id', $contratoId)->first();
    }

    public function formatoPredeterminado(): ?object
    {
        return DB::table('formatos')->select('id','contenido')->where('contrato_id', null)->where('id', 1)->first()
            ?? DB::table('formatos')->select('id','contenido')->whereNull('contrato_id')->first();
    }

    public function guardarPersonalizado(int $contratoId, string $contenido): bool
    {
        $existente = $this->formatoPersonalizado($contratoId);
        if ($existente) {
            return DB::table('formatos')->where('id', $existente->id)->update(['contenido'=>$contenido,'updated_at'=>now()]) >= 0;
        }

        return DB::table('formatos')->insert([
            'contrato_id'=>$contratoId,
            'contenido'=>$contenido,
            'created_at'=>now(),
            'updated_at'=>now(),
        ]);
    }

    public function restaurarPredeterminado(int $contratoId): bool
    {
        return DB::table('formatos')->where('contrato_id', $contratoId)->delete() >= 0;
    }

    public function pago(int $pagoId): ?object
    {
        return DB::table('pagos')->where('id', $pagoId)->first();
    }

    public function datosRecibo(int $pagoId): ?object
    {
        $pago = DB::selectOne("SELECT pg.*, DATE_FORMAT(pg.fecha_pago,'%d/%m/%Y') AS fecha_pago,
            (pg.renta-IFNULL(pg.renta_pagado,0)) AS renta_saldo,
            (pg.servicios-IFNULL(pg.servicios_pagado,0)) AS servicios_saldo,
            (pg.otros_pagos-IFNULL(pg.otros_pagos_pagado,0)) AS otros_pagos_saldo,
            CASE WHEN pg.estado='C' THEN 'Cancelado' WHEN pg.estado='P' THEN 'Pendiente' ELSE 'Anulado' END AS estado_,
            CASE WHEN pg.estado='C' THEN 'success' WHEN pg.estado='P' AND pg.fecha_pago<CURDATE() THEN 'danger' ELSE 'warning' END AS estado_color,
            CASE WHEN pg.estado='C' THEN 'Pagado' WHEN pg.fecha_pago<CURDATE() THEN 'Vencido' ELSE 'Pendiente' END AS estado_fecha
            FROM pagos pg WHERE pg.id=?", [$pagoId]);
        return $pago;
    }

    public function pagosRealizados(int $contratoId): array
    {
        return DB::select("SELECT DATE_FORMAT(fecha_pago,'%d/%m/%Y') AS fecha_pago,renta,servicios,otros_pagos,IFNULL(comentario,'') AS comentario FROM pagos WHERE contrato_id=? AND pago_id IS NOT NULL AND pago_id<>0 ORDER BY fecha_pago DESC", [$contratoId]);
    }

    public function pagoPerteneceUsuario(int $pagoId, int $usuarioId): bool
    {
        return DB::table('pagos')
            ->join('contratos','contratos.id','=','pagos.contrato_id')
            ->join('unidades','unidades.id','=','contratos.unidad_id')
            ->join('propiedades','propiedades.id','=','unidades.propiedad_id')
            ->where('pagos.id',$pagoId)
            ->where('propiedades.propietario_id',$usuarioId)
            ->exists();
    }
}
