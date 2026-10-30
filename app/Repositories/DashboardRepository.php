<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function resumen(int $usuarioId): object
    {
        return DB::selectOne("SELECT
            (SELECT COUNT(*) FROM propiedades WHERE propietario_id=? AND estado<>0) AS propiedades,
            (SELECT COUNT(*) FROM unidades u INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND u.estado<>0) AS unidades,
            (SELECT COUNT(*) FROM inquilinos WHERE usuario_id=? AND estado<>0) AS inquilinos,
            (SELECT COUNT(*) FROM contratos c INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND c.estado='1' AND c.fecha_fin>=CURDATE()) AS contratos_vigentes,
            (SELECT COUNT(*) FROM contratos c INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND c.estado='1' AND c.fecha_fin<CURDATE()) AS contratos_vencidos,
            (SELECT COUNT(*) FROM contratos c INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND c.estado='1' AND c.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY)) AS contratos_por_vencer,
            (SELECT COUNT(DISTINCT u.id) FROM unidades u INNER JOIN propiedades p ON p.id=u.propiedad_id INNER JOIN contratos c ON c.unidad_id=u.id WHERE p.propietario_id=? AND u.estado<>0 AND c.estado='1' AND c.fecha_inicio<=CURDATE() AND c.fecha_fin>=CURDATE()) AS unidades_ocupadas",
            [$usuarioId,$usuarioId,$usuarioId,$usuarioId,$usuarioId,$usuarioId,$usuarioId]);
    }

    public function contratosPorVencer(int $usuarioId, int $dias=30): array
    {
        return DB::select("SELECT c.id,CONCAT('#',RIGHT(CONCAT('00000000',c.id),5)) AS codigo,c.fecha_inicio,c.fecha_fin,i.nombres,i.apellidos,p.nombre AS propiedad,u.descripcion AS unidad,DATEDIFF(c.fecha_fin,CURDATE()) AS dias_restantes FROM contratos c INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id INNER JOIN inquilinos i ON i.id=c.inquilino_id WHERE p.propietario_id=? AND c.estado='1' AND c.fecha_fin>=CURDATE() AND c.fecha_fin<=DATE_ADD(CURDATE(),INTERVAL ? DAY) ORDER BY c.fecha_fin ASC", [$usuarioId,$dias]);
    }

    public function contratosVencidos(int $usuarioId, int $limite=10): array
    {
        return DB::select("SELECT c.id,CONCAT('#',RIGHT(CONCAT('00000000',c.id),5)) AS codigo,c.fecha_inicio,c.fecha_fin,i.nombres,i.apellidos,p.nombre AS propiedad,u.descripcion AS unidad,ABS(DATEDIFF(c.fecha_fin,CURDATE())) AS dias_vencido FROM contratos c INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id INNER JOIN inquilinos i ON i.id=c.inquilino_id WHERE p.propietario_id=? AND c.estado='1' AND c.fecha_fin<CURDATE() ORDER BY c.fecha_fin DESC LIMIT $limite", [$usuarioId]);
    }

    public function gananciasPorPropiedad(int $usuarioId): array
    {
        return DB::select("SELECT p.id,p.nombre,COALESCE(SUM(CASE WHEN pg.pago_id IS NOT NULL AND pg.pago_id<>0 THEN pg.renta+pg.servicios+pg.otros_pagos ELSE 0 END),0) AS ganado_total,COALESCE(SUM(CASE WHEN pg.pago_id IS NOT NULL AND pg.pago_id<>0 AND pg.fecha_pago>=DATE_FORMAT(CURDATE(),'%Y-%m-01') THEN pg.renta+pg.servicios+pg.otros_pagos ELSE 0 END),0) AS ganado_mes FROM propiedades p LEFT JOIN unidades u ON u.propiedad_id=p.id LEFT JOIN contratos c ON c.unidad_id=u.id LEFT JOIN pagos pg ON pg.contrato_id=c.id WHERE p.propietario_id=? GROUP BY p.id,p.nombre ORDER BY ganado_total DESC, p.nombre ASC", [$usuarioId]);
    }

    public function ingresosMensuales(int $usuarioId, int $meses=12): array
    {
        return DB::select("SELECT DATE_FORMAT(pg.fecha_pago,'%Y-%m') AS periodo,COALESCE(SUM(pg.renta+pg.servicios+pg.otros_pagos),0) AS total FROM pagos pg INNER JOIN contratos c ON c.id=pg.contrato_id INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND pg.pago_id IS NOT NULL AND pg.pago_id<>0 AND pg.fecha_pago>=DATE_FORMAT(DATE_SUB(CURDATE(),INTERVAL ? MONTH),'%Y-%m-01') GROUP BY DATE_FORMAT(pg.fecha_pago,'%Y-%m') ORDER BY periodo ASC", [$usuarioId,$meses-1]);
    }

    public function cobranzaDelMes(int $usuarioId): object
    {
        return DB::selectOne("SELECT COALESCE(SUM(CASE WHEN pg.pago_id IS NULL OR pg.pago_id=0 THEN pg.renta+pg.servicios+pg.otros_pagos ELSE 0 END),0) AS por_cobrar,COALESCE(SUM(CASE WHEN pg.pago_id IS NULL OR pg.pago_id=0 THEN pg.renta_pagado+pg.servicios_pagado+pg.otros_pagos_pagado ELSE 0 END),0) AS cobrado FROM pagos pg INNER JOIN contratos c ON c.id=pg.contrato_id INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id WHERE p.propietario_id=? AND pg.fecha_pago>=DATE_FORMAT(CURDATE(),'%Y-%m-01') AND pg.fecha_pago<DATE_ADD(DATE_FORMAT(CURDATE(),'%Y-%m-01'),INTERVAL 1 MONTH)", [$usuarioId]);
    }

    public function pagosRecientes(int $usuarioId, int $limite=8): array
    {
        return DB::select("SELECT pg.id,pg.fecha_pago,pg.renta,pg.servicios,pg.otros_pagos,pg.renta+pg.servicios+pg.otros_pagos AS total,p.nombre AS propiedad,u.descripcion AS unidad,i.nombres,i.apellidos FROM pagos pg INNER JOIN contratos c ON c.id=pg.contrato_id INNER JOIN unidades u ON u.id=c.unidad_id INNER JOIN propiedades p ON p.id=u.propiedad_id INNER JOIN inquilinos i ON i.id=c.inquilino_id WHERE p.propietario_id=? AND pg.pago_id IS NOT NULL AND pg.pago_id<>0 ORDER BY pg.fecha_pago DESC,pg.id DESC LIMIT $limite", [$usuarioId]);
    }
}