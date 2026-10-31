<?php

namespace App\Services;

use App\Repositories\FormatoRepository;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContratoFormatoService
{
    public function __construct(private FormatoRepository $repository) {}

    public function campos(): array
    {
        return [
            '[contrato_codigo]'=>'Código del contrato',
            '[propietario_nombre]'=>'Nombre completo del propietario',
            '[propietario_dni]'=>'DNI del propietario',
            '[propietario_direccion]'=>'Dirección completa del propietario',
            '[propiedad_nombre]'=>'Nombre de la propiedad',
            '[propiedad_direccion]'=>'Dirección completa de la propiedad',
            '[unidad_nombre]'=>'Nombre de la unidad alquilada',
            '[inquilino_nombre]'=>'Nombre completo del inquilino',
            '[inquilino_dni]'=>'DNI del inquilino',
            '[meses]'=>'Duración del contrato en meses',
            '[inicio]'=>'Fecha de inicio',
            '[fin]'=>'Fecha de fin',
            '[dia_pago]'=>'Día mensual de pago',
            '[renta]'=>'Monto mensual de renta',
            '[renta_texto]'=>'Monto de renta en letras',
            '[servicios]'=>'Monto de servicios',
            '[servicios_texto]'=>'Monto de servicios en letras',
            '[dia_servicio]'=>'Día mensual de servicios',
            '[total_mensual]'=>'Renta más servicios',
            '[fecha_actual]'=>'Fecha actual del sistema',
        ];
    }

    public function contenidoEditor(int $contratoId, int $usuarioId): array
    {
        $this->validarContrato($contratoId, $usuarioId);
        $personalizado = $this->repository->formatoPersonalizado($contratoId);
        $predeterminado = $this->repository->formatoPredeterminado();
        if (!$predeterminado && !$personalizado) throw new InvalidArgumentException('No existe una plantilla de contrato configurada');
        return [
            'contenido' => $personalizado?->contenido ?? $predeterminado->contenido,
            'personalizado' => (bool)$personalizado,
            'id' => $contratoId,
        ];
    }

    public function generar(int $contratoId, int $usuarioId): string
    {
        $this->validarContrato($contratoId, $usuarioId);
        $datos = $this->repository->datosContrato($contratoId);
        if (!$datos) throw new InvalidArgumentException('Contrato no encontrado');
        $personalizado = $this->repository->formatoPersonalizado($contratoId);
        $predeterminado = $this->repository->formatoPredeterminado();
        $plantilla = $personalizado?->contenido ?? $predeterminado?->contenido;
        if (!$plantilla) throw new InvalidArgumentException('No existe una plantilla de contrato configurada');

        \Carbon\Carbon::setLocale('es');
        $valores = [
            '[contrato_codigo]' => '#'.str_pad((string)$contratoId, 8, '0', STR_PAD_LEFT),
            '[propietario_nombre]' => strtoupper($datos->propietario_nombre ?? ''),
            '[propietario_dni]' => $datos->propietario_dni ?? '',
            '[propietario_direccion]' => strtoupper($datos->propietario_direccion ?? ''),
            '[propiedad_nombre]' => strtoupper($datos->propiedad_nombre ?? ''),
            '[propiedad_direccion]' => strtoupper($datos->propiedad_direccion ?? ''),
            '[unidad_nombre]' => strtoupper($datos->unidad_nombre ?? ''),
            '[inquilino_nombre]' => strtoupper($datos->inquilino_nombre ?? ''),
            '[inquilino_dni]' => $datos->inquilino_dni ?? '',
            '[meses]' => $datos->meses,
            '[inicio]' => $datos->inicio,
            '[fin]' => $datos->fin,
            '[dia_pago]' => $datos->dia_pago,
            '[renta]' => number_format((float)$datos->renta, 2, '.', ','),
            '[renta_texto]' => $this->numeroALetras((float)$datos->renta),
            '[servicios]' => number_format((float)$datos->servicios, 2, '.', ','),
            '[servicios_texto]' => $this->numeroALetras((float)$datos->servicios),
            '[dia_servicio]' => $datos->dia_servicio,
            '[total_mensual]' => number_format((float)$datos->renta + (float)$datos->servicios, 2, '.', ','),
            '[fecha_actual]' => \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y'),
        ];
        $contenido = str_replace(array_keys($valores), array_values($valores), $plantilla);
        return $this->limpiarHtml($contenido);
    }

    public function guardar(int $contratoId, int $usuarioId, string $contenido): int
    {
        $this->validarContrato($contratoId, $usuarioId);
        $contenido = trim($contenido);
        if ($contenido === '') throw new InvalidArgumentException('El contenido del contrato no puede estar vacío');
        if (strlen($contenido) > 50000) throw new InvalidArgumentException('El contenido excede el tamaño permitido');
        $contenido = $this->limpiarHtml($contenido);
        return DB::transaction(fn() => $this->repository->guardarPersonalizado($contratoId, $contenido) ? $contratoId : throw new \RuntimeException('No se pudo guardar el formato'));
    }

    public function restablecer(int $contratoId, int $usuarioId): int
    {
        $this->validarContrato($contratoId, $usuarioId);
        return DB::transaction(fn() => $this->repository->restaurarPredeterminado($contratoId) ? $contratoId : $contratoId);
    }

    public function vistaPrevia(int $contratoId, int $usuarioId, string $contenido): string
    {
        $this->validarContrato($contratoId, $usuarioId);
        $generado = $this->generar($contratoId, $usuarioId);
        if (trim($contenido) === '') throw new InvalidArgumentException('El contenido de vista previa no puede estar vacío');
        $datos = $this->repository->datosContrato($contratoId);
        \Carbon\Carbon::setLocale('es');
        $valores = $this->valoresPlantilla((int)$contratoId, $datos);
        return $this->limpiarHtml(str_replace(array_keys($valores), array_values($valores), $contenido));
    }

    public function datosRecibo(int $pagoId, int $usuarioId): array
    {
        if (!$this->repository->pagoPerteneceUsuario($pagoId, $usuarioId)) throw new InvalidArgumentException('Pago no encontrado o sin permisos');
        $pago = $this->repository->datosRecibo($pagoId);
        if (!$pago) throw new InvalidArgumentException('Pago no encontrado');
        $contratoId = (int)$pago->contrato_id;
        $contrato = $this->repository->datosContrato($contratoId);
        return ['pago'=>$pago, 'rsp'=>$this->repository->pagosRealizados($contratoId), 'infoContrato'=>[
            'p_desc'=>$contrato->propiedad_nombre ?? '', 'u_desc'=>$contrato->unidad_nombre ?? '',
            'fecha_inicio'=>$contrato->inicio ?? '', 'fecha_fin'=>$contrato->fin ?? '',
            'nombres'=>$contrato->inquilino_nombre ?? '', 'apellidos'=>'', 'estado'=>'VIGENTE'
        ], 'id'=>$pagoId, 'contratoId'=>$contratoId];
    }

    private function validarContrato(int $contratoId, int $usuarioId): void
    {
        if ($contratoId <= 0) throw new InvalidArgumentException('ID de contrato requerido');
        if (!$this->repository->contratoPerteneceUsuario($contratoId, $usuarioId)) throw new InvalidArgumentException('No tienes permiso para este contrato');
    }

    private function valoresPlantilla(int $contratoId, object $datos): array
    {
        \Carbon\Carbon::setLocale('es');
        return [
            '[contrato_codigo]' => '#'.str_pad((string)$contratoId, 8, '0', STR_PAD_LEFT),
            '[propietario_nombre]' => strtoupper($datos->propietario_nombre ?? ''), '[propietario_dni]'=>$datos->propietario_dni ?? '',
            '[propietario_direccion]'=>strtoupper($datos->propietario_direccion ?? ''), '[propiedad_nombre]'=>strtoupper($datos->propiedad_nombre ?? ''),
            '[propiedad_direccion]'=>strtoupper($datos->propiedad_direccion ?? ''), '[unidad_nombre]'=>strtoupper($datos->unidad_nombre ?? ''),
            '[inquilino_nombre]'=>strtoupper($datos->inquilino_nombre ?? ''), '[inquilino_dni]'=>$datos->inquilino_dni ?? '',
            '[meses]'=>$datos->meses, '[inicio]'=>$datos->inicio, '[fin]'=>$datos->fin, '[dia_pago]'=>$datos->dia_pago,
            '[renta]'=>number_format((float)$datos->renta,2,'.',','), '[renta_texto]'=>$this->numeroALetras((float)$datos->renta),
            '[servicios]'=>number_format((float)$datos->servicios,2,'.',','), '[servicios_texto]'=>$this->numeroALetras((float)$datos->servicios),
            '[dia_servicio]'=>$datos->dia_servicio, '[total_mensual]'=>number_format((float)$datos->renta+(float)$datos->servicios,2,'.',','),
            '[fecha_actual]'=>\Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y'),
        ];
    }

    private function numeroALetras(float $numero): string
    {
        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$partes[0]; $decimal = str_pad($partes[1], 2, '0', STR_PAD_RIGHT);
        if (class_exists('\NumberFormatter')) {
            $texto = ucfirst((new \NumberFormatter('es', \NumberFormatter::SPELLOUT))->format($entero));
        } else {
            $texto = (string)$entero;
        }
        return $texto." con {$decimal}/100 soles";
    }

    private function limpiarHtml(string $contenido): string
    {
        $contenido = preg_replace('/<(script|style|iframe|object|embed)[^>]*>.*?<\/\\1\\s*>/is', '', $contenido) ?? $contenido;
        $contenido = preg_replace('/\\s(on\\w+)\\s*=\\s*("[^"]*"|\'[^\']*\'|[^\\s>]+)/i', '', $contenido) ?? $contenido;
        $contenido = preg_replace('/javascript\\s*:/i', '', $contenido) ?? $contenido;
        return trim($contenido);
    }
}
