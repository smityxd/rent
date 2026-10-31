<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ContratoFormatoService;

class FormatosController extends Controller
{
    public function __construct(private ContratoFormatoService $formatoService) {}

    function listaContrato(Request $request){
        try {
            $id=(int)$request->input('id');
            $contenido=$this->formatoService->generar($id,(int)Auth::id());
            if($request->input('modo')==='simple'){
                return response($contenido."<hr><a href='".route('descargar.pdf',['id'=>$id])."' class='btn btn-primary mb-3'>Descargar PDF</a><br><a href='".route('editar-Contrato',['id'=>$id])."' target='_blank'>Editar contrato <i class='fa fa-external-link-alt'></i></a>");
            }
            return view('formatos.layout',compact('contenido'));
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
    }
    
    public function descargarPDF(Request $request)
    {
        try {
            $id=(int)$request->input('id');
            $contenido=$this->formatoService->generar($id,(int)Auth::id());
            return Pdf::loadView('formatos.layout',compact('contenido'))->setPaper('a4')->download("Contrato-".str_pad((string)$id,8,'0',STR_PAD_LEFT).".pdf");
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
    }
    
    public function descargarReciboPDF(Request $request)
    {
        try {
            $id=(int)$request->input('id');
            $datos=$this->formatoService->datosRecibo($id,(int)Auth::id());
            $pago=$datos['pago']; $rsp=$datos['rsp']; $infoContrato=$datos['infoContrato'];
            $contenido=view('formatos.recibo',compact('pago','rsp','infoContrato','id'));
            if($request->input('modo')==='simple') return $contenido."<hr><p><a href='".route('descargar-recibo',['id'=>$id,'modo'=>'pdf'])."' class='btn btn-primary' target='_blank'>Descargar Recibo PDF</a></p>";
            return Pdf::loadView('formatos.layout',compact('contenido'))->setPaper('a4')->download("Recibo-".str_pad((string)$id,8,'0',STR_PAD_LEFT).".pdf");
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
    }
    
    public function editarContratoVista(Request $request)
    {
        try {
            $id=(int)$request->input('id');
            $datos=$this->formatoService->contenidoEditor($id,(int)Auth::id());
            $contenido=$datos['contenido']; $personalizado=$datos['personalizado'];
            $campos=$this->formatoService->campos();
            return view('contratoEditor',compact('contenido','campos','id','personalizado'));
        } catch (\InvalidArgumentException $e) { abort(404,$e->getMessage()); }
    }
    
    public function editarContrato(Request $request)
    {
        try {
            $id=$this->formatoService->guardar((int)$request->input('id'),(int)Auth::id(),(string)$request->input('contenido',''));
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
        catch (\Throwable $e) { return response()->json(['success'=>false,'message'=>'Error al guardar el contrato','error'=>$e->getMessage()],500); }
    }

    public function vistaPrevia(Request $request)
    {
        try {
            $contenido=$this->formatoService->vistaPrevia((int)$request->input('id'),(int)Auth::id(),(string)$request->input('contenido',''));
            return response()->json(['success'=>true,'data'=>$contenido]);
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
    }

    public function restablecer(Request $request)
    {
        try {
            $id=$this->formatoService->restablecer((int)$request->input('id'),(int)Auth::id());
            return response()->json(['success'=>true,'data'=>$id]);
        } catch (\InvalidArgumentException $e) { return response()->json(['success'=>false,'message'=>$e->getMessage(),'error'=>$e->getMessage()],422); }
    }
}

