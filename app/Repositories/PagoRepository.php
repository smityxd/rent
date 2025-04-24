<?php

namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class PagoRepository
{
    /**
     * Retorna los pagos con formato y filtros opcionales
     *
     * @param int|null $contrato_id
     * @param int|null $pago_id
     * @return array
     */
    public static function obtenerReportePagos($contrato_id = null, $pago_id = null, $pago_id_parent = null)
    {
        $query = "
            SELECT
                id,
                pago_id,
                DATE_FORMAT(fecha_pago, '%d/%m/%Y') as fecha_pago,
                renta,
                servicios,
                otros_pagos,
                renta_pagado,
                servicios_pagado,
                otros_pagos_pagado,
                (renta - IFNULL(renta_pagado, 0)) as renta_saldo,
                (servicios - IFNULL(servicios_pagado, 0)) as servicios_saldo,
                (otros_pagos - IFNULL(otros_pagos_pagado, 0)) as otros_pagos_saldo,
                ifnull(comentario,'') as comentario,
                CASE 
                    WHEN estado='P' THEN 'Pendiente'
                    WHEN estado='C' THEN 'Cancelado'
                    ELSE 'Anulado'
                END as estado_,
                pagos.estado,
                CASE 
                    WHEN fecha_pago < CURDATE() AND pagos.estado = 'C' THEN 'Pagado'
                    WHEN fecha_pago < CURDATE() AND pagos.estado = 'P' THEN concat('Vencido ' , ABS(DATEDIFF(fecha_pago, CURDATE())) , ' dias')
                    WHEN fecha_pago >= CURDATE() AND pagos.estado = 'C' THEN 'Pagado'
                    WHEN fecha_pago >= CURDATE() AND pagos.estado = 'P' THEN CONCAT('Vence ',
                        CASE 
                            WHEN DATEDIFF(fecha_pago, CURDATE()) = 0 THEN 'Hoy'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) = 1 THEN 'Mañana'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) = 2 THEN 'Pasado Mañana'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 3 AND 7 THEN 'en menos de 7 dias'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 8 AND 15 THEN 'en Menos 2 semanas'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 16 AND 30 THEN 'en Menos de 1 mes'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 31 AND 60 THEN 'en Menos de 2 mes'
                            WHEN DATEDIFF(fecha_pago, CURDATE()) > 60 THEN concat('en Mas de ', (DATEDIFF(fecha_pago, CURDATE()) DIV 30),' meses')
                            ELSE CONCAT('En ', DATEDIFF(fecha_pago, CURDATE()), ' días')
                        END)
                    ELSE 'Desconocido'
                END as estado_fecha,
                CASE 
                    WHEN fecha_pago < CURDATE() AND pagos.estado = 'C' THEN 'success'
                    WHEN fecha_pago < CURDATE() AND pagos.estado = 'P' THEN 'danger'
                    WHEN fecha_pago >= CURDATE() AND pagos.estado = 'C' THEN 'success'
                    WHEN fecha_pago >= CURDATE() AND pagos.estado = 'P' THEN 
                        CASE
                            WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 0 AND 15 THEN 'warning'
                            ELSE 'info'
                        END
                    ELSE 'info'
                END as estado_color,
                DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at,
                DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i:%s') as updated_at
            FROM pagos
            WHERE 1=1
        ";

        $params = [];

        if (!is_null($contrato_id)) {
            $query .= " AND contrato_id = ?";
            $params[] = $contrato_id;
        }

        if (!is_null($pago_id)) {
            $query .= " AND id = ?";
            $params[] = $pago_id;
        }

        if (!is_null($pago_id_parent)) {
            $query .= " AND pago_id = ?";
            $params[] = $pago_id_parent;
        }
        
        $query .= " order by pagos.fecha_pago asc";

        return DB::select($query, $params);
    }
}