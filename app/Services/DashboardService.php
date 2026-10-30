<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Carbon\Carbon;

class DashboardService
{
    public function __construct(private DashboardRepository $repository) {}

    public function obtener(int $usuarioId): array
    {
        $resumen = $this->repository->resumen($usuarioId);
        $ganancias = $this->repository->gananciasPorPropiedad($usuarioId);
        $cobranza = $this->repository->cobranzaDelMes($usuarioId);
        $ingresosDb = $this->repository->ingresosMensuales($usuarioId, 12);

        $ingresos = [];
        for ($i = 11; $i >= 0; $i--) {
            $periodo = Carbon::now()->subMonths($i);
            $clave = $periodo->format('Y-m');
            $fila = collect($ingresosDb)->firstWhere('periodo', $clave);
            $ingresos[] = [
                'periodo' => ucfirst($periodo->translatedFormat('M Y')),
                'total' => $fila ? (float)$fila->total : 0,
            ];
        }

        $porCobrar = (float)($cobranza->por_cobrar ?? 0);
        $cobrado = (float)($cobranza->cobrado ?? 0);
        $pendiente = max($porCobrar - $cobrado, 0);

        $unidades = (int)$resumen->unidades;
        $ocupadas = (int)$resumen->unidades_ocupadas;
        $porcentajeOcupacion = $unidades > 0 ? round(($ocupadas / $unidades) * 100, 1) : 0;

        return [
            'resumen' => $resumen,
            'porcentaje_ocupacion' => $porcentajeOcupacion,
            'contratos_por_vencer' => $this->repository->contratosPorVencer($usuarioId, 30),
            'contratos_vencidos' => $this->repository->contratosVencidos($usuarioId, 10),
            'ganancias_por_propiedad' => $ganancias,
            'ingresos_mensuales' => $ingresos,
            'cobranza_mes' => [
                'por_cobrar' => $porCobrar,
                'cobrado' => $cobrado,
                'pendiente' => $pendiente,
                'porcentaje_cobrado' => $porCobrar > 0 ? round(($cobrado / $porCobrar) * 100, 1) : 0,
            ],
            'pagos_recientes' => $this->repository->pagosRecientes($usuarioId, 8),
        ];
    }
}
