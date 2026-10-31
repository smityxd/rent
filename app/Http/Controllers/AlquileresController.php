<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\Alquileres\PropiedadService;
use App\Services\Alquileres\InquilinoService;
use App\Services\Alquileres\ContratoService;
use App\Services\Alquileres\DashboardService;
use App\Services\Alquileres\PagoService;
use App\Services\Alquileres\ContratoFormatoService;
use Barryvdh\DomPDF\Facade\Pdf;

class AlquileresController extends Controller
{
    public function __construct(
        private PropiedadService $propiedadService,
        private InquilinoService $inquilinoService,
        private ContratoService $contratoService,
        private DashboardService $dashboardService,
        private PagoService $pagoService,
        private ContratoFormatoService $formatoService
    ) {}

    public function dashboard()
    {
        return view('alquileres.dashboard', $this->dashboardService->obtener((int)Auth::id()));
    }

    public function dashboardDatos()
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->obtener((int)Auth::id()),
        ]);
    }

    public function cargaAdminPropiedades(Request $request){
        $datos = $this->propiedadService->obtenerDatosAdministracion();
        return view('alquileres.propiedades', ['ubigeo' => collect($datos['ubigeos'])->toJson()]);
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
        return view('alquileres.contratos', $datos);
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
        return view('alquileres.inquilinos');
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

    public function cargaAdminPagos()
    {
        return view('alquileres.pagos', [
            'contratos' => $this->pagoService->obtenerContratosUsuario((int)Auth::id()),
        ]);
    }

    public function listaPagos(Request $request)
    {
        $id = $request->filled('id') ? (int)$request->input('id') : null;
        return response()->json($this->pagoService->listar($id));
    }

    public function procesaPago(Request $request)
    {
        try {
            $id = $this->pagoService->procesar($request->all());
            return response()->json(['success' => true, 'data' => $id]);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al procesar el pago');
        }
    }

    public function borraPago(Request $request)
    {
        try {
            $this->pagoService->eliminar((int)$request->input('id'));
            return response()->json(['success' => true, 'message' => 'Pago eliminado correctamente']);
        } catch (\Throwable $e) {
            return $this->responderError($e, 'Error al eliminar el pago');
        }
    }

    public function listaContratoFormato(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $contenido = $this->formatoService->generar($id, (int)Auth::id());
            if ($request->input('modo') === 'simple') {
                return response($contenido."<hr><a href='".route('alquileres.formatos.descargar-pdf',['id'=>$id])."' class='btn btn-primary mb-3'>Descargar PDF</a><br><a href='".route('alquileres.formatos.editar',['id'=>$id])."' target='_blank'>Editar contrato <i class='fa fa-external-link-alt'></i></a>");
            }
            return view('alquileres.formatos.layout', compact('contenido'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
        }
    }

    public function descargarContratoPDF(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $contenido = $this->formatoService->generar($id, (int)Auth::id());
            return Pdf::loadView('alquileres.formatos.layout', compact('contenido'))->setPaper('a4')->download("Contrato-".str_pad((string)$id,8,'0',STR_PAD_LEFT).".pdf");
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
        }
    }

    public function descargarReciboPDF(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $datos = $this->formatoService->datosRecibo($id, (int)Auth::id());
            $pago = $datos['pago'];
            $rsp = $datos['rsp'];
            $infoContrato = $datos['infoContrato'];
            $contenido = view('alquileres.formatos.recibo', compact('pago','rsp','infoContrato','id'));
            if ($request->input('modo') === 'simple') {
                return $contenido."<hr><p><a href='".route('alquileres.formatos.descargar-recibo',['id'=>$id,'modo'=>'pdf'])."' class='btn btn-primary' target='_blank'>Descargar Recibo PDF</a></p>";
            }
            return Pdf::loadView('alquileres.formatos.layout', compact('contenido'))->setPaper('a4')->download("Recibo-".str_pad((string)$id,8,'0',STR_PAD_LEFT).".pdf");
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
        }
    }

    public function editarContratoVista(Request $request)
    {
        try {
            $id = (int)$request->input('id');
            $datos = $this->formatoService->contenidoEditor($id, (int)Auth::id());
            return view('alquileres.contratoEditor', [
                'contenido' => $datos['contenido'],
                'campos' => $this->formatoService->campos(),
                'id' => $id,
                'personalizado' => $datos['personalizado'],
            ]);
        } catch (\InvalidArgumentException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function editarContrato(Request $request)
    {
        try {
            $id = $this->formatoService->guardar((int)$request->input('id'), (int)Auth::id(), (string)$request->input('contenido',''));
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
        } catch (\Throwable $e) {
            return response()->json(['success'=>false,'message'=>'Error al guardar el contrato','error'=>$e->getMessage()],500);
        }
    }

    public function vistaPrevia(Request $request)
    {
        try {
            $contenido = $this->formatoService->vistaPrevia((int)$request->input('id'), (int)Auth::id(), (string)$request->input('contenido',''));
            return response()->json(['success'=>true,'data'=>$contenido]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
        }
    }

    public function restablecerContrato(Request $request)
    {
        try {
            $id = $this->formatoService->restablecer((int)$request->input('id'), (int)Auth::id());
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422);
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
