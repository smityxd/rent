<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class PropiedadRepository
{
    public function ubigeos(): array
    {
        return DB::table('ubigeos')->get()->all();
    }

    public function listar(int $usuarioId): array
    {
        return DB::select("SELECT id,nombre,direccion,descripcion,ubigeo_id,concat(u.departamento,' - ',u.provincia,' - ',u.distrito) as ubigeo_nom,DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as Creado,DATE_FORMAT(updated_at,'%d/%m/%Y %H:%i:%s') as Modificado,estado FROM propiedades p LEFT OUTER JOIN ubigeos u ON p.ubigeo_id=u.codigo WHERE propietario_id=? ORDER BY id DESC", [$usuarioId]);
    }

    public function obtener(int $id, int $usuarioId): ?object
    {
        return DB::selectOne("SELECT id,nombre,direccion,descripcion,ubigeo_id,concat(u.departamento,' - ',u.provincia,' - ',u.distrito) as ubigeo_nom,DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as Creado,DATE_FORMAT(updated_at,'%d/%m/%Y %H:%i:%s') as Modificado,estado FROM propiedades p LEFT OUTER JOIN ubigeos u ON p.ubigeo_id=u.codigo WHERE p.id=? AND propietario_id=?", [$id, $usuarioId]);
    }

    public function unidades(int $id, int $usuarioId): array
    {
        return DB::select("SELECT unidades.* FROM unidades INNER JOIN propiedades ON propiedades.id=unidades.propiedad_id WHERE unidades.propiedad_id=? AND propiedades.propietario_id=?", [$id, $usuarioId]);
    }

    public function insertar(array $datos): int
    {
        return (int) DB::table('propiedades')->insertGetId($datos);
    }

    public function actualizar(int $id, int $usuarioId, array $datos): bool
    {
        return DB::table('propiedades')->where('id', $id)->where('propietario_id', $usuarioId)->update($datos) >= 0;
    }

    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::table('propiedades')->where('id', $id)->where('propietario_id', $usuarioId)->delete() > 0;
    }

    public function insertarUnidad(array $datos): int
    {
        return (int) DB::table('unidades')->insertGetId($datos);
    }

    public function actualizarUnidad(int $id, int $propiedadId, array $datos): bool
    {
        return DB::table('unidades')->where('id', $id)->where('propiedad_id', $propiedadId)->update($datos) >= 0;
    }

    public function eliminarUnidad(int $id, int $propiedadId): bool
    {
        return DB::table('unidades')->where('id', $id)->where('propiedad_id', $propiedadId)->delete() > 0;
    }

    public function eliminarUnidades(int $propiedadId): int
    {
        return DB::table('unidades')->where('propiedad_id', $propiedadId)->delete();
    }
}
