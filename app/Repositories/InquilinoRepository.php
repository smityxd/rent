<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class InquilinoRepository
{
    public function listar(int $usuarioId): array
    {
        return DB::select("SELECT id,nombres,apellidos,dni,observaciones,DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as Creado,DATE_FORMAT(updated_at,'%d/%m/%Y %H:%i:%s') as Modificado,estado FROM inquilinos WHERE usuario_id=? ORDER BY id DESC", [$usuarioId]);
    }

    public function obtener(int $id, int $usuarioId): ?object
    {
        return DB::selectOne("SELECT id,nombres,apellidos,dni,observaciones,DATE_FORMAT(created_at,'%d/%m/%Y %H:%i:%s') as Creado,DATE_FORMAT(updated_at,'%d/%m/%Y %H:%i:%s') as Modificado,estado FROM inquilinos WHERE id=? AND usuario_id=?", [$id, $usuarioId]);
    }

    public function insertar(array $datos): int
    {
        return (int) DB::table('inquilinos')->insertGetId($datos);
    }

    public function actualizar(int $id, int $usuarioId, array $datos): bool
    {
        return DB::table('inquilinos')->where('id', $id)->where('usuario_id', $usuarioId)->update($datos) >= 0;
    }

    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::table('inquilinos')->where('id', $id)->where('usuario_id', $usuarioId)->delete() > 0;
    }
}
