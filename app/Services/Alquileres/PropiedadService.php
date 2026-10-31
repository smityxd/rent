<?php

namespace App\Services\Alquileres;

use App\Repositories\Alquileres\PropiedadRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PropiedadService
{
    public function __construct(private PropiedadRepository $repository) {}

    public function obtenerDatosAdministracion(): array
    {
        return ['ubigeos' => $this->repository->ubigeos()];
    }

    public function listar(int $usuarioId, ?int $id = null): array
    {
        if ($id !== null) {
            $propiedad = $this->repository->obtener($id, $usuarioId);
            if (!$propiedad) throw new InvalidArgumentException('Propiedad no encontrada');
            return ['propiedad' => $propiedad, 'unidades' => $this->repository->unidades($id, $usuarioId)];
        }
        return $this->repository->listar($usuarioId);
    }

    public function procesar(array $datos, int $usuarioId): int
    {
        $this->validarDatosPropiedad($datos);
        $detalles = [];
        foreach ($datos as $clave => $valor) {
            if (str_starts_with($clave, 'det_')) $detalles[$clave] = $valor;
        }

        unset($datos['_token'], $datos['det_status'], $datos['det_descripcion'], $datos['det_costo'], $datos['det_piso'], $datos['det_estado'], $datos['det_tipo'], $datos['det_id']);
        foreach ($datos as $clave => $valor) if (str_starts_with($clave, 'det_')) unset($datos[$clave]);

        $id = (int) ($datos['id'] ?? 0);
        unset($datos['id']);
        $this->validarDetalles($detalles);

        return DB::transaction(function () use ($datos, $detalles, $id, $usuarioId) {
            if ($id === 0) {
                $datos['propietario_id'] = $usuarioId;
                $datos['created_at'] = now();
                $idPropiedad = $this->repository->insertar($datos);
            } else {
                $propiedad = $this->repository->obtener($id, $usuarioId);
                if (!$propiedad) throw new InvalidArgumentException('Propiedad no encontrada');
                $datos['updated_at'] = now();
                $this->repository->actualizar($id, $usuarioId, $datos);
                $idPropiedad = $id;
            }

            foreach ($detalles['det_status'] as $index => $status) {
                $unidad = ['descripcion'=>$detalles['det_descripcion'][$index], 'costo'=>$detalles['det_costo'][$index], 'piso'=>$detalles['det_piso'][$index], 'estado'=>$detalles['det_estado'][$index], 'propiedad_id'=>$idPropiedad, 'tipo'=>$detalles['det_tipo'][$index]];
                if ((int)$status === 1) { $unidad['created_at']=now(); $this->repository->insertarUnidad($unidad); }
                if ((int)$status === 2) {
                    $unidad['updated_at']=now(); $this->repository->actualizarUnidad((int)$detalles['det_id'][$index], $idPropiedad, $unidad);
                }
                if ((int)$status === 4) $this->repository->eliminarUnidad((int)$detalles['det_id'][$index], $idPropiedad);
            }
            return $idPropiedad;
        });
    }

    public function eliminar(int $id, int $usuarioId): int
    {
        if ($id <= 0) throw new InvalidArgumentException('ID requerido');
        return DB::transaction(function () use ($id, $usuarioId) {
            if (!$this->repository->obtener($id, $usuarioId)) throw new InvalidArgumentException('Propiedad no encontrada');
            $this->repository->eliminarUnidades($id);
            if (!$this->repository->eliminar($id, $usuarioId)) throw new InvalidArgumentException('No se pudo eliminar la propiedad');
            return $id;
        });
    }

    private function validarDatosPropiedad(array $datos): void
    {
        foreach (['id','nombre','direccion','descripcion','ubigeo_id','estado'] as $campo) if (!array_key_exists($campo,$datos)) throw new InvalidArgumentException("El campo {$campo} es requerido");
        if ((int)$datos['id'] < 0) throw new InvalidArgumentException('El identificador de propiedad es inválido');
    }

    private function validarDetalles(array $detalles): void
    {
        if (!isset($detalles['det_status']) || !is_array($detalles['det_status'])) return;
        foreach (['det_descripcion','det_costo','det_piso','det_estado','det_tipo'] as $campo) if (!isset($detalles[$campo]) || !is_array($detalles[$campo])) throw new InvalidArgumentException('Detalle de unidades incompleto');
        if (count($detalles['det_status']) !== count($detalles['det_descripcion'])) throw new InvalidArgumentException('Detalle de unidades inconsistente');
    }
}
