<?php

namespace App\Services\Alquileres;

use App\Repositories\Alquileres\ContratoRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContratoService
{
    public function __construct(private ContratoRepository $repository) {}

    public function obtenerDatosAdministracion(int $usuarioId): array
    {
        return ['inquilinos'=>$this->repository->listarInquilinos($usuarioId), 'unidades'=>$this->repository->listarUnidades($usuarioId)];
    }

    public function listar(int $usuarioId, ?int $id = null): array|object|null
    {
        if ($id !== null) {
            $contrato = $this->repository->obtener($id, $usuarioId);
            if (!$contrato) throw new InvalidArgumentException('Contrato no encontrado');
            return $contrato;
        }
        return $this->repository->listar($usuarioId);
    }

    public function procesar(array $datos, int $usuarioId): int
    {
        foreach (['id','inquilino_id','unidad_id','fecha_inicio','fecha_fin','duracion','renta','servicios','comentarios','estado'] as $campo) if (!array_key_exists($campo, $datos)) throw new InvalidArgumentException("El campo {$campo} es requerido");
        if ((int)$datos['id'] < 0) throw new InvalidArgumentException('El identificador de contrato es inválido');

        try {
            $datos['fecha_inicio']=Carbon::createFromFormat('d/m/Y',$datos['fecha_inicio'])->format('Y-m-d');
            $datos['fecha_fin']=Carbon::createFromFormat('d/m/Y',$datos['fecha_fin'])->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new InvalidArgumentException('Las fechas deben tener formato dd/mm/yyyy');
        }

        if (Carbon::parse($datos['fecha_inicio'])->gt(Carbon::parse($datos['fecha_fin']))) {
            throw new InvalidArgumentException('La fecha de inicio no puede ser posterior a la fecha de fin');
        }
        if ((int)$datos['duracion'] <= 0) throw new InvalidArgumentException('La duración debe ser mayor a cero');
        if ((float)$datos['renta'] < 0 || (float)$datos['servicios'] < 0) throw new InvalidArgumentException('Los importes no pueden ser negativos');
        if (!$this->repository->unidadPerteneceUsuario((int)$datos['unidad_id'], $usuarioId)) throw new InvalidArgumentException('La unidad seleccionada no pertenece al usuario');
        if (!$this->repository->inquilinoPerteneceUsuario((int)$datos['inquilino_id'], $usuarioId)) throw new InvalidArgumentException('El inquilino seleccionado no pertenece al usuario');

        $id=(int)$datos['id']; $generar=(bool)($datos['generar'] ?? false);
        unset($datos['id'],$datos['_token'],$datos['generar']);

        return DB::transaction(function () use ($datos,$id,$usuarioId,$generar) {
            if ($id===0) {
                $datos['created_at']=now(); $idContrato=$this->repository->insertar($datos);
            } else {
                if (!$this->repository->obtener($id,$usuarioId)) throw new InvalidArgumentException('Contrato no encontrado');
                $datos['updated_at']=now(); $this->repository->actualizar($id,$usuarioId,$datos); $idContrato=$id;
            }

            if ($generar) {
                $this->repository->eliminarPagos($idContrato);
                $fechaInicio=Carbon::createFromFormat('Y-m-d',$datos['fecha_inicio']);
                for ($i=0;$i<(int)$datos['duracion'];$i++) {
                    $this->repository->insertarPago([
                        'contrato_id'=>$idContrato,
                        'fecha_pago'=>$fechaInicio->copy()->addMonthsNoOverflow($i)->format('Y-m-d'),
                        'renta'=>$datos['renta'],
                        'servicios'=>$datos['servicios'],
                        'otros_pagos'=>0,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ]);
                }
            }
            return $idContrato;
        });
    }

    public function eliminar(int $id,int $usuarioId): int
    {
        if ($id<=0) throw new InvalidArgumentException('ID requerido');
        return DB::transaction(function () use ($id,$usuarioId) {
            if (!$this->repository->obtener($id,$usuarioId)) throw new InvalidArgumentException('Contrato no encontrado');
            if (!$this->repository->eliminar($id,$usuarioId)) throw new InvalidArgumentException('No se pudo eliminar el contrato');
            return $id;
        });
    }
}
