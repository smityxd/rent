<?php

namespace App\Repositories\Alquileres;

use Illuminate\Support\Facades\DB;

class ContratoRepository
{
    public function listarInquilinos(int $usuarioId): array
    {
        return DB::select("SELECT id,CONCAT(apellidos,', ',nombres) as nom FROM inquilinos WHERE usuario_id=? AND estado=1", [$usuarioId]);
    }

    public function listarUnidades(int $usuarioId): array
    {
        return DB::select("SELECT U.id,CONCAT(P.nombre,': ',U.descripcion,' (',CASE U.tipo WHEN 'CQ' THEN 'Cuarto' WHEN 'CB' THEN 'Cuarto + Baño' WHEN 'MD' THEN 'Mini-departamento' WHEN 'DE' THEN 'Departamento' WHEN 'CA' THEN 'Casa' WHEN 'ED' THEN 'Edificio' WHEN 'OT' THEN 'Otro' END,') Piso: ',U.piso,' - S/.',U.costo) as nom FROM propiedades P INNER JOIN unidades U ON U.propiedad_id=P.id WHERE propietario_id=? AND P.estado=1 AND U.estado=1", [$usuarioId]);
    }

    public function listar(int $usuarioId): array
    {
        return DB::select("SELECT C.id,CONCAT('#',RIGHT(CONCAT('00000000',C.id),5)) as Contrato,C.inquilino_id,C.unidad_id,I.nombres,I.apellidos,P.nombre as Propiedad,U.descripcion as Unidad,DATE_FORMAT(C.fecha_inicio,'%d/%m/%Y') as fecha_inicio,DATE_FORMAT(C.fecha_fin,'%d/%m/%Y') as fecha_fin,C.duracion,C.renta,C.servicios,C.comentarios,DATE_FORMAT(C.created_at,'%d/%m/%Y %H:%i:%s') as Creado,DATE_FORMAT(C.updated_at,'%d/%m/%Y %H:%i:%s') as Modificado,C.estado FROM contratos C INNER JOIN unidades U ON U.id=C.unidad_id INNER JOIN inquilinos I ON I.id=C.inquilino_id INNER JOIN propiedades P ON P.id=U.propiedad_id WHERE P.propietario_id=? ORDER BY C.id DESC", [$usuarioId]);
    }

    public function obtener(int $id, int $usuarioId): ?object
    {
        return DB::selectOne("SELECT C.*,I.nombres,I.apellidos,P.nombre as Propiedad,U.descripcion as Unidad FROM contratos C INNER JOIN unidades U ON U.id=C.unidad_id INNER JOIN inquilinos I ON I.id=C.inquilino_id INNER JOIN propiedades P ON P.id=U.propiedad_id WHERE C.id=? AND P.propietario_id=?", [$id, $usuarioId]);
    }

    public function insertar(array $datos): int
    {
        return (int) DB::table('contratos')->insertGetId($datos);
    }

    public function actualizar(int $id, int $usuarioId, array $datos): bool
    {
        $contrato = DB::table('contratos')->join('unidades','unidades.id','=','contratos.unidad_id')->join('propiedades','propiedades.id','=','unidades.propiedad_id')->where('contratos.id',$id)->where('propiedades.propietario_id',$usuarioId)->exists();
        if (!$contrato) return false;
        return DB::table('contratos')->where('id',$id)->update($datos) >= 0;
    }

    public function unidadPerteneceUsuario(int $unidadId, int $usuarioId): bool
    {
        return DB::table('unidades')->join('propiedades','propiedades.id','=','unidades.propiedad_id')->where('unidades.id',$unidadId)->where('propiedades.propietario_id',$usuarioId)->exists();
    }

    public function inquilinoPerteneceUsuario(int $inquilinoId, int $usuarioId): bool
    {
        return DB::table('inquilinos')->where('id',$inquilinoId)->where('usuario_id',$usuarioId)->exists();
    }

    public function eliminarPagos(int $contratoId): int
    {
        return DB::table('pagos')->where('contrato_id',$contratoId)->delete();
    }

    public function insertarPago(array $datos): int
    {
        return (int) DB::table('pagos')->insertGetId($datos);
    }

    public function eliminar(int $id, int $usuarioId): bool
    {
        $contrato = DB::table('contratos')->join('unidades','unidades.id','=','contratos.unidad_id')->join('propiedades','propiedades.id','=','unidades.propiedad_id')->where('contratos.id',$id)->where('propiedades.propietario_id',$usuarioId)->first();
        if (!$contrato) return false;
        DB::table('pagos')->where('contrato_id',$id)->delete();
        return DB::table('contratos')->where('id',$id)->delete() > 0;
    }
}
