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
        }
        $query .= " order by id desc";
        return DB::select($query, $params);
    }
    
    public function procesaPropiedad(Request $request){
        // Validar datos del formulario
        $valores = $request->all();
        unset($valores["_token"]);

        if ($valores["id"] == 0) {
            $valores['propietario_id'] = Auth::user()->id;
            $valores['created_at'] = now();
            // Insertar datos en la base de datos
            $idprop = DB::table('propiedades')->insertGetId($valores);
        }else{
            $idprop = $valores["id"];
            // Actualizar propiedad
            $valores['updated_at'] = now();
            $updated = DB::table('propiedades')
                            ->where('id', $valores["id"])
                            ->update($valores);
        }

        return response()->json([
            'success' => true,
            'data' => $idprop
        ]);
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
