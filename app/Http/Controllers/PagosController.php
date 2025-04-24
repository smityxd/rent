<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\PagoRepository;

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
        $contrato_id = null;
        if ($request->has('id') && !empty($request->input('id'))) {
            $contrato_id = $request->input('id');
        }
        return PagoRepository::obtenerReportePagos($contrato_id);
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
