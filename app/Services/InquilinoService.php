<?php

namespace App\Services;

use App\Repositories\InquilinoRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InquilinoService
{
    public function __construct(private InquilinoRepository $repository) {}

    public function listar(int $usuarioId, ?int $id = null): array|object|null
    {
        if ($id !== null) {
            $inquilino = $this->repository->obtener($id, $usuarioId);
            if (!$inquilino) throw new InvalidArgumentException('Inquilino no encontrado');
            return $inquilino;
        }
        return $this->repository->listar($usuarioId);
    }

    public function procesar(array $datos, int $usuarioId): int
    {
        foreach (['id','nombres','apellidos','dni','observaciones','estado'] as $campo) if (!array_key_exists($campo, $datos)) throw new InvalidArgumentException("El campo {$campo} es requerido");
        if ((int)$datos['id'] < 0) throw new InvalidArgumentException('El identificador de inquilino es inválido');

        $id = (int) $datos['id']; unset($datos['id'], $datos['_token']);
        return DB::transaction(function () use ($datos, $id, $usuarioId) {
            if ($id === 0) {
                $datos['usuario_id']=$usuarioId; $datos['created_at']=now();
                return $this->repository->insertar($datos);
            }
            if (!$this->repository->obtener($id, $usuarioId)) throw new InvalidArgumentException('Inquilino no encontrado');
            $datos['updated_at']=now(); $this->repository->actualizar($id, $usuarioId, $datos);
            return $id;
        });
    }

    public function eliminar(int $id, int $usuarioId): int
    {
        if ($id <= 0) throw new InvalidArgumentException('ID requerido');
        return DB::transaction(function () use ($id, $usuarioId) {
            if (!$this->repository->obtener($id, $usuarioId)) throw new InvalidArgumentException('Inquilino no encontrado');
            if (!$this->repository->eliminar($id, $usuarioId)) throw new InvalidArgumentException('No se pudo eliminar el inquilino');
            return $id;
        });
    }
}
