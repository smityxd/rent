<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PropiedadService;
use App\Services\InquilinoService;
use App\Services\ContratoService;

class AlquileresController extends Controller
{
    public function __construct(
        private PropiedadService $propiedadService,
        private InquilinoService $inquilinoService,
        private ContratoService $contratoService
    ) {}

    public function cargaAdminPropiedades(Request $request){
        $datos = $this->propiedadService->obtenerDatosAdministracion();
        return view('propiedades', ['ubigeo' => collect($datos['ubigeos'])->toJson()]);
    }

    public function listaPropiedad(Request $request){
        try {
            $id = $request->filled('id') ? (int)$request->input('id') : null;
            $resultado = $this->propiedadService->listar((int)Auth::id(), $id);
            return response()->json($resultado);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al consultar propiedades');
        }
    }
    
    public function procesaPropiedad(Request $request){
        try {
            $id = $this->propiedadService->procesar($request->all(), (int)Auth::id());
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al procesar la propiedad');
        }
    }
    
    public function borraPropiedad(Request $request) {
        try {
            $id = $this->propiedadService->eliminar((int)$request->input('id'), (int)Auth::id());
            return response()->json(['success'=>true,'message'=>'Propiedad eliminada','id'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al eliminar la propiedad');
        }
    }
    
    public function cargaAdminContratos(Request $request){
        $datos = $this->contratoService->obtenerDatosAdministracion((int)Auth::id());
        return view('contratos', $datos);
    }

    public function listaContrato(Request $request){
        try {
            $id = $request->filled('id') ? (int)$request->input('id') : null;
            return response()->json($this->contratoService->listar((int)Auth::id(), $id));
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al consultar contratos');
        }
    }

    public function procesaContrato(Request $request){
        try {
            $id = $this->contratoService->procesar($request->all(), (int)Auth::id());
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al procesar el contrato');
        }
    }
    
    public function borraContrato(Request $request) {
        try {
            $id = $this->contratoService->eliminar((int)$request->input('id'), (int)Auth::id());
            return response()->json(['success'=>true,'message'=>'Contrato eliminado','id'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al eliminar el contrato');
        }
    }
    public function cargaAdminInquilinos(Request $request){
        return view('inquilinos');
    }

    public function listaInquilino(Request $request){
        try {
            $id = $request->filled('id') ? (int)$request->input('id') : null;
            return response()->json($this->inquilinoService->listar((int)Auth::id(), $id));
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al consultar inquilinos');
        }
    }
    
    public function procesaInquilino(Request $request){
        try {
            $id = $this->inquilinoService->procesar($request->all(), (int)Auth::id());
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al procesar el inquilino');
        }
    }
    
    public function borraInquilino(Request $request) {
        try {
            $id = $this->inquilinoService->eliminar((int)$request->input('id'), (int)Auth::id());
            return response()->json(['success'=>true,'message'=>'Inquilino eliminado','id'=>$id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al eliminar el inquilino');
        }
    }

    private function responderError(\Throwable $e, string $mensaje)
    {
        $status = $e instanceof \InvalidArgumentException ? 422 : 500;
        return response()->json([
            'success'=>false,
            'message'=>$status === 422 ? $e->getMessage() : $mensaje,
            'error'=>$e->getMessage()
        ], $status);
    }
}
