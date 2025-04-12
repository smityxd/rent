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
                where propietario_id = ?";
        $params = [Auth::user()->id];
        
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
    
    public function cargaAdminContratos(Request $request){
        $usuarioId = Auth::user()->id;

        $inquilinos = DB::select("select id, CONCAT(apellidos, ', ', nombres) as nom from inquilinos where usuario_id = ? and estado = 1", [$usuarioId]);

        $unidades = DB::select("select U.id, CONCAT(P.nombre, ': ', U.descripcion, ' (',
            (case U.tipo
                when 'CQ' then 'Cuarto'
                when 'CB' then 'Cuarto + Baño'
                when 'MD' then 'Mini-departamento'
                when 'DE' then 'Departamento'
                when 'CA' then 'Casa'
                when 'ED' then 'Edificio'
                when 'OT' then 'Otro' end),
            ')',' Piso: ',U.piso,' - S/.',U.costo) as nom from propiedades P
        INNER JOIN unidades U on U.propiedad_id = P.id
        where propietario_id = ? and P.estado = 1 and U.estado = 1", [$usuarioId]);

        return view('contratos', [
            'inquilinos' => $inquilinos,
            'unidades' => $unidades
        ]);
    }

    public function listaContrato(Request $request){
        $query = "select
                    C.id,
                    CONCAT('#',RIGHT(CONCAT('00000000',C.id),5)) as Contrato,
                    C.inquilino_id,
                    C.unidad_id,
                    I.nombres,
                    I.apellidos,
                    P.nombre as Propiedad,
                    U.descripcion as Unidad,
                    DATE_FORMAT(C.fecha_inicio, '%d/%m/%Y') as fecha_inicio,
                    DATE_FORMAT(C.fecha_fin, '%d/%m/%Y') as fecha_fin,
                    C.duracion as duracion,
                    C.renta,
                    C.servicios,
                    C.comentarios,
                    DATE_FORMAT(C.created_at, '%d/%m/%Y %H:%i:%s') as Creado,
                    DATE_FORMAT(C.updated_at, '%d/%m/%Y %H:%i:%s') as Modificado,
                    C.estado
                from contratos C
                INNER JOIN unidades U on U.id = C.unidad_id
                INNER JOIN inquilinos I on I.id = C.inquilino_id
                INNER JOIN propiedades P on P.id = U.propiedad_id
                where P.propietario_id = ?";
        $params = [Auth::user()->id];
        
        if($request->has('id')){
            $query .= " and C.id = ?";
            $params[] = $request->input('id');
            return response()->json(DB::selectOne($query, $params));
        }
        $query .= " order by id desc";
        return DB::select($query, $params);
    }

    public function procesaContrato(Request $request){
        try {
            // Validar datos del formulario
            $valores = $request->all();
            $generar = $request->input('generar');

            // Convertir "fecha_inicio" y "fecha_fin" al formato "Y-m-d" para MySQL
            $valores['fecha_inicio'] = \Carbon\Carbon::createFromFormat('d/m/Y', $valores['fecha_inicio'])->format('Y-m-d');
            $valores['fecha_fin'] = \Carbon\Carbon::createFromFormat('d/m/Y', $valores['fecha_fin'])->format('Y-m-d');

            unset($valores["_token"]); // Eliminar el token CSRF
            unset($valores["generar"]); // Eliminar el campo "generar" del array

            DB::beginTransaction();
            if ($valores["id"] == 0) {
                $valores['created_at'] = now();
                // Insertar datos en la base de datos
                $id = DB::table('contratos')->insertGetId($valores);
            } else {
                $id = $valores["id"];
                // Actualizar propiedad
                $valores['updated_at'] = now();
                $updated = DB::table('contratos')
                        ->where('id', $valores["id"])
                        ->update($valores);
            }

            //luego grabar, generamos el cronograma de pagos
            if($generar){
                // Antes de generar cronograma, eliminamos el anterior
                DB::table('pagos')->where('contrato_id', $id)->delete();

                // Obtener los valores de fecha_inicio, duracion, renta, servicios
                $fecha_inicio = \Carbon\Carbon::createFromFormat('Y-m-d', $valores['fecha_inicio']);
                $duracion = $valores['duracion'];
                $renta = $valores['renta'];
                $servicios = $valores['servicios'];

                // Generar el cronograma de pagos
                for ($i = 0; $i < $duracion; $i++) {
                    DB::table('pagos')->insert([
                        'contrato_id' => $id,
                        'fecha_pago' => $fecha_inicio->copy()->addMonths($i)->format('Y-m-d'),
                        'renta' => $renta,
                        'servicios' => $servicios,
                        'otros_pagos' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            // Commit the transaction
            DB::commit();
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
    
    public function borraContrato(Request $request) {
        try {
            $id = $request->input('id');
    
            // Validar que el ID fue enviado
            if (!$id) {
                return response()->json(['success' => false, 'message' => 'ID requerido'], 400);
            }
    
            // Verificar si el contrato existe
            $contrato = DB::table('contratos')->where('id', $id)->first();
            if (!$contrato) {
                return response()->json(['success' => false, 'message' => 'Contrato no encontrado'], 404);
            }
    
            // Eliminar los pagos asociados al contrato
            DB::table('pagos')->where('contrato_id', $id)->delete();
    
            // Intentar eliminar el contrato
            $borrado = DB::table('contratos')->where('id', $id)->delete();
    
            if ($borrado) {
                return response()->json(['success' => true, 'message' => 'Contrato eliminado', 'id' => $id]);
            } else {
                return response()->json(['success' => false, 'message' => 'No se pudo eliminar el contrato'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error del servidor', 'error' => $e->getMessage()], 500);
        }
    }
}
