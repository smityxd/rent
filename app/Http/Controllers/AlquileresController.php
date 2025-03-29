<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AlquileresController extends Controller
{
    public function cargaAdminPropiedades(Request $request){
        $ubigeos = DB::table('ubigeos')->get();
        return view('propiedades', [
            'ubigeo' => $ubigeos->toJson()
        ]);
    }
    public function listaPropiedad(Request $request){
        $query = "select
                    id
                    ,nombre
                    ,direccion
                    ,descripcion
                    ,ubigeo_id
                    ,concat(u.departamento,' - ',u.provincia,' - ',u.distrito) as ubigeo_nom
                    ,DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as Creado
                    ,DATE_FORMAT(updated_at, '%d/%m/%Y %H:%i:%s') as Modificado
                    ,estado
                from propiedades p
                left outer join ubigeos u on p.ubigeo_id = u.codigo
                where 1=1";
        $params = [];
        if($request->has('id')){
            $query .= " and id = ?";
            $params[] = $request->input('id');
            return response()->json([
                'propiedad' => DB::selectOne($query, $params),
                'unidades' => DB::select("SELECT * FROM `unidades` WHERE propiedad_id = ?", [$request->input('id')])
            ]);
        }
        $query .= " order by id desc";
        return DB::select($query, $params);
    }
    
    public function procesaPropiedad(Request $request){
        try {
            // Validar datos del formulario
            $valores = $request->all();

            // Separar los arrays de detalle
            $det_valores = [];
            foreach ($valores as $key => $value) {
                if (str_starts_with($key, 'det_')) {
                    $det_valores[$key] = $value;
                    unset($valores[$key]);
                }
            }
            unset($valores["_token"]);

            DB::beginTransaction();
            if ($valores["id"] == 0) {
                $valores['propietario_id'] = Auth::user()->id;
                $valores['created_at'] = now();
                // Insertar datos en la base de datos
                $idprop = DB::table('propiedades')->insertGetId($valores);
            } else {
                $idprop = $valores["id"];
                // Actualizar propiedad
                $valores['updated_at'] = now();
                $updated = DB::table('propiedades')
                        ->where('id', $valores["id"])
                        ->update($valores);
            }

            // Luego de insertar o actualizar, evaluamos detalle
            foreach ($det_valores['det_status'] as $index => $status) {
                $unidadData = [
                    'descripcion' => $det_valores['det_descripcion'][$index],
                    'costo' => $det_valores['det_costo'][$index],
                    'piso' => $det_valores['det_piso'][$index],
                    'estado' => $det_valores['det_estado'][$index],
                    'propiedad_id' => $idprop,
                    'tipo' => $det_valores['det_tipo'][$index]
                ];

                switch ($status) {
                    case 1: // Nuevo registro
                    $unidadData['created_at'] = now();
                    DB::table('unidades')->insert($unidadData);
                    break;

                    case 2: // Actualizar registro existente
                    $unidadData['updated_at'] = now();
                    DB::table('unidades')
                    ->where('id', $det_valores['det_id'][$index])
                    ->update($unidadData);
                    break;

                    case 4: // Eliminar registro
                    DB::table('unidades')
                    ->where('id', $det_valores['det_id'][$index])
                    ->delete();
                    break;

                    // Status 3 (no hacer nada) no requiere acción
                }
            }

            // Commit the transaction
            DB::commit();
                return response()->json([
                'success' => true,
                'data' => $idprop
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la propiedad',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function borraPropiedad(Request $request) {
        try {
            $id = $request->input('id');
    
            // Validar que el ID fue enviado
            if (!$id) {
                return response()->json(['success' => false, 'message' => 'ID requerido'], 400);
            }
    
            // Verificar si la propiedad existe
            $propiedad = DB::table('propiedades')->where('id', $id)->first();
            if (!$propiedad) {
                return response()->json(['success' => false, 'message' => 'Propiedad no encontrada'], 404);
            }
    
            // Intentar eliminar
            $borrado = DB::table('propiedades')->where('id', $id)->delete();
    
            if ($borrado) {
                return response()->json(['success' => true, 'message' => 'Propiedad eliminada', 'id' => $id]);
            } else {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error del servidor', 'error' => $e->getMessage()], 500);
        }
    }
    
}
