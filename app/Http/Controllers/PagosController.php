<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PagosController extends Controller
{
    public function cargaAdminPagos(Request $request){
        $usuarioId = Auth::user()->id;

        $contratos = DB::select("SELECT *,CONCAT('#',RIGHT(CONCAT('00000000',id),5),' [',estado,'] [',p_desc,' - ',u_desc,'] [',nombres,' - ',apellidos,']') as text
            from (
                select
                    c.id
                    ,CONCAT('#',RIGHT(CONCAT('00000000',c.id),5)) as id_
                    ,case when c.fecha_fin < now() then 'VENCIDO' else 'VIGENTE' end as estado
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
                where p.propietario_id=?) as k order by fecha_finx asc", [$usuarioId]);

        return view('pagos', [
            'contratos' => $contratos
        ]);
    }

    public function listaPagos(Request $request){
        $query = "select
                    id
                    ,pago_id
                    ,DATE_FORMAT(fecha_pago, '%d/%m/%Y') as fecha_pago
                    ,renta
                    ,servicios
                    ,otros_pagos
                    ,renta_pagado
                    ,servicios_pagado
                    ,otros_pagos_pagado
                    ,(renta - IFNULL(renta_pagado, 0)) as renta_saldo
                    ,(servicios - IFNULL(servicios_pagado, 0)) as servicios_saldo
                    ,(otros_pagos - IFNULL(otros_pagos_pagado, 0)) as otros_pagos_saldo
                    ,comentario
                    ,case when estado='P' then 'Pendiente' when estado='C' then 'Cancelado' else 'Anulado' end as estado_
                    ,pagos.estado
                    ,CASE 
                        WHEN fecha_pago < CURDATE() AND pagos.estado = 'C' THEN 'Pagado'
                        WHEN fecha_pago < CURDATE() AND pagos.estado = 'P' THEN concat('Vencido ' , ABS(DATEDIFF(fecha_pago, CURDATE())) , ' dias')
                        WHEN fecha_pago >= CURDATE() AND pagos.estado = 'C' THEN 'Pagado'
                        WHEN fecha_pago >= CURDATE() AND pagos.estado = 'P' THEN CONCAT('Vence ',
                            case 
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
                    END as estado_fecha
                    ,CASE 
                        WHEN fecha_pago < CURDATE() AND pagos.estado = 'C' THEN 'success'
                        WHEN fecha_pago < CURDATE() AND pagos.estado = 'P' THEN 'danger'
                        WHEN fecha_pago >= CURDATE() AND pagos.estado = 'C' THEN 'success'
                        WHEN fecha_pago >= CURDATE() AND pagos.estado = 'P' THEN 
                            case
                                WHEN DATEDIFF(fecha_pago, CURDATE()) BETWEEN 0 AND 15 THEN 'warning'
                                else 'info'
                            end
                        ELSE 'info'
                    END as estado_color
                    ,DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as created_at
                    ,DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i:%s') as updated_at
                from pagos where 1=1"; 

        $params = [];
        if ($request->has('id') && !empty($request->input('id'))) {
            $query .= " and contrato_id = ?";
            $params[] = $request->input('id');
        }
        $query .= " order by pagos.fecha_pago asc";
        return DB::select($query, $params);
    }

    public function procesaPago(Request $request){
        try {
            // Validar datos del formulario
            $valores = $request->all();
            $generar = $request->input('generar');

            // Convertir "fecha_inicio" y "fecha_fin" al formato "Y-m-d" para MySQL
            $valores['fecha_pago'] = \Carbon\Carbon::createFromFormat('d/m/Y', $valores['fecha_pago'])->format('Y-m-d');

            unset($valores["_token"]); // Eliminar el token CSRF

            DB::beginTransaction();

            $id_pago_principal = 0;
            // Insertar pago
            if ($valores["id"] == 0 && $valores["pago_id"] != 0) {
                $id_pago_principal = $valores["pago_id"];
                $id = DB::table('pagos')->insertGetId([
                    'contrato_id' => $valores['contrato_id'],
                    'pago_id' => $valores['pago_id'],
                    'fecha_pago' => $valores['fecha_pago'],
                    'renta' => $valores['renta'],
                    'servicios' => $valores['servicios'],
                    'otros_pagos' => $valores['otros_pagos'],
                    'comentario' => $valores['comentario'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Modificar pago
            if ($valores["id"] != 0 && $valores["pago_id"] == 0) {
                $id_pago_principal = $valores["id"];
                DB::table('pagos')
                    ->where('id', $valores['id'])
                    ->update([
                        'fecha_pago' => $valores['fecha_pago'],
                        'renta' => $valores['renta'],
                        'servicios' => $valores['servicios'],
                        'otros_pagos' => $valores['otros_pagos'],
                        'comentario' => $valores['comentario'],
                        'updated_at' => now()
                    ]);
                $id = $valores['pago_id'];
            }

            // Commit the transaction
            DB::commit();
            // Actualizar los saldos del pago principal
            $this->actualizarSaldoPago($id_pago_principal);
            return response()->json([
                'success' => true,
                'data' => $id
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el Contrato',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    function borraPago(Request $request){
        try {
            DB::beginTransaction();
            $id = $request->input('id');

            // Obtener el pago_id antes de eliminar
            $pago = DB::table('pagos')->select('pago_id')->where('id', $id)->first();
            $pago_id = $pago ? $pago->pago_id : null;

            // Eliminar el pago
            DB::table('pagos')->where('id', $id)->delete();

            DB::commit();

            // Actualizar los saldos del pago principal si existe un pago_id
            if ($pago_id) {
                $this->actualizarSaldoPago($pago_id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pago eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el Pago',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function actualizarSaldoPago($id_pago_principal) {    
        // Obtener los valores del pago principal
        $pagoPrincipal = DB::table('pagos')
            ->select('renta', 'servicios', 'otros_pagos')
            ->where('id', $id_pago_principal)
            ->first();

        if (!$pagoPrincipal) {
            throw new \Exception('El pago principal no existe.');
        }

        // Calcular los saldos sumando los pagos relacionados
        $saldos = DB::table('pagos')
            ->selectRaw('
            SUM(renta) as renta_pagado,
            SUM(servicios) as servicios_pagado,
            SUM(otros_pagos) as otros_pagos_pagado
            ')
            ->where('pago_id', $id_pago_principal)
            ->first();

        // Determinar el nuevo estado
        $total_pagado = $saldos->renta_pagado + $saldos->servicios_pagado;
        $total_a_pagar = $pagoPrincipal->renta + $pagoPrincipal->servicios;

        $estado = ($total_pagado >= $total_a_pagar) ? 'C' : 'P'; // Cancelado o Pendiente

        // Actualizar los saldos y el estado en el pago principal
        DB::table('pagos')
            ->where('id', $id_pago_principal)
            ->update([
            'renta_pagado' => $saldos->renta_pagado ?? 0,
            'servicios_pagado' => $saldos->servicios_pagado ?? 0,
            'otros_pagos_pagado' => $saldos->otros_pagos_pagado ?? 0,
            'estado' => $estado,
            'updated_at' => now()
            ]);
    }
}
