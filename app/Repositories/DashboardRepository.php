<?php

namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    /**
     * Retorna los pagos con formato y filtros opcionales
     *
     * @param int|null $contrato_id
     * @param int|null $pago_id
     * @return array
     */
    public static function listaContratos($propietario_id)
    {
        $query = "
            SELECT *,CONCAT('#',RIGHT(CONCAT('00000000',id),5),' [',estado,'] [',p_desc,' - ',u_desc,'] [',nombres,' - ',apellidos,']') as text
                from (
                    select
                        c.id
                        ,CONCAT('#',RIGHT(CONCAT('00000000',c.id),5)) as id_
                        ,case c.estado
                            when 0 then 'INACTIVO'
                            else case when c.fecha_fin < now() then 'VENCIDO' else 'VIGENTE' end 
                        end as estado
                        ,DATE_FORMAT(c.fecha_inicio, '%d/%m/%Y') as fecha_inicio
                        ,DATE_FORMAT(c.fecha_fin, '%d/%m/%Y') as fecha_fin
                        ,i.nombres
                        ,i.apellidos
                        ,p.descripcion as p_desc
                        ,u.descripcion as u_desc
                        ,c.fecha_fin as fecha_finx
                    from contratos c
                        inner join unidades u on c.unidad_id=u.id
                        inner JOIN propiedades p on p.id=u.propiedad_id
                        inner join inquilinos i on i.id=c.inquilino_id
                    where p.propietario_id=?) as k order by fecha_finx asc
        ";

        return DB::select($query, [$propietario_id]);
    }
}