<?php

namespace App\Services\Alquileres;

use App\Repositories\Alquileres\PagoRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PagoService
{
    public function __construct(private PagoRepository $repository) {}

    public function obtenerContratosUsuario(int $usuarioId): array
    {
        return $this->repository->listarContratosUsuario($usuarioId);
    }

    public function listar(?int $contratoId = null): array
    {
        return $this->repository->obtenerReportePagos($contratoId);
    }

    public function procesar(array $datos): int
    {
        if (empty($datos['fecha_pago'])) {
            throw new \InvalidArgumentException('La fecha de pago es obligatoria');
        }

        $datos['fecha_pago'] = Carbon::createFromFormat('d/m/Y', $datos['fecha_pago'])->format('Y-m-d');
        unset($datos['_token']);

        return DB::transaction(function () use ($datos) {
            $idPagoPrincipal = 0;

            if ((int)$datos['id'] === 0 && (int)$datos['pago_id'] !== 0) {
                $idPagoPrincipal = (int)$datos['pago_id'];
                $id = $this->repository->insertarPago([
                    'contrato_id' => $datos['contrato_id'],
                    'pago_id' => $datos['pago_id'],
                    'fecha_pago' => $datos['fecha_pago'],
                    'renta' => $datos['renta'],
                    'servicios' => $datos['servicios'],
                    'otros_pagos' => $datos['otros_pagos'],
                    'comentario' => $datos['comentario'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $idPagoPrincipal = (int)$datos['id'];
                $this->repository->actualizarPago((int)$datos['id'], [
                    'fecha_pago' => $datos['fecha_pago'],
                    'renta' => $datos['renta'],
                    'servicios' => $datos['servicios'],
                    'otros_pagos' => $datos['otros_pagos'],
                    'comentario' => $datos['comentario'],
                    'updated_at' => now(),
                ]);
                $id = (int)$datos['pago_id'];
            }

            $this->actualizarSaldoPago($idPagoPrincipal);
            return $id;
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $pago = $this->repository->obtenerPago($id);
            $pagoIdPrincipal = $pago ? (int)$pago->pago_id : null;
            $this->repository->eliminarPago($id);
            if ($pagoIdPrincipal) {
                $this->actualizarSaldoPago($pagoIdPrincipal);
            }
        });
    }

    private function actualizarSaldoPago(int $idPagoPrincipal): void
    {
        $principal = $this->repository->obtenerPagoPrincipal($idPagoPrincipal);
        if (!$principal) {
            throw new \InvalidArgumentException('El pago principal no existe');
        }

        $saldos = $this->repository->saldosPagos($idPagoPrincipal);
        $totalPagado = ($saldos->renta_pagado ?? 0) + ($saldos->servicios_pagado ?? 0);
        $totalAPagar = (float)$principal->renta + (float)$principal->servicios;

        $this->repository->actualizarSaldoPago($idPagoPrincipal, [
            'renta_pagado' => $saldos->renta_pagado ?? 0,
            'servicios_pagado' => $saldos->servicios_pagado ?? 0,
            'otros_pagos_pagado' => $saldos->otros_pagos_pagado ?? 0,
            'estado' => $totalPagado >= $totalAPagar ? 'C' : 'P',
            'updated_at' => now(),
        ]);
    }
}
