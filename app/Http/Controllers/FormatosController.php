<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Repositories\PagoRepository;

class FormatosController extends Controller
{
    function listaContrato(Request $request){
        $id = $request->input('id');
        //echo "hola {$id}";

        $contenido = $this->generaContenidoContrato($id);
        $modo = $request->input('modo');
        if($modo == 'simple'){
            echo $contenido;
            echo "<hr>";
            echo "<a href='".route('descargar.pdf',['id'=>$id])."' class='btn btn-primary'>Descargar PDF</a>";
        }else{
            return view('formatos.layout', compact('contenido'));
        }
    }
    
    public function descargarPDF(Request $request)
    {
        $id = $request->input('id');
        $contenido = $this->generaContenidoContrato($id);
        $pdf = Pdf::loadView('formatos.layout', compact('contenido'));
        return $pdf->download('Contrato.pdf');
    }
    
    public function descargarReciboPDF(Request $request)
    {
        $id = $request->input('id');
        $infoContrato =  $request->input('contrato');
        $rs = PagoRepository::obtenerReportePagos(null,$id);
        $pago = $rs[0];

        $rsp = PagoRepository::obtenerReportePagos(null,null,$id);
        $contenido = view('formatos.recibo', compact('pago','rsp','infoContrato','id'));

        if($request->input('modo')=="simple"){
            return $contenido . "<hr><p><a href='".route('descargar-recibo',['id'=>$id,'modo'=>'pdf','contrato'=>$infoContrato])."' class='btn btn-primary' targer='_blank'>Descargar Recibo PDF</a></p>";
        }else{
            $pdf = Pdf::loadView('formatos.layout', compact('contenido'));
            return $pdf->download('Recibo'.$id.'.pdf');
        }
    }

    private function numeroALetras($numero)
    {
        $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        
        // Separar parte entera y decimal
        $partes = explode('.', number_format($numero, 2, '.', ''));
    
        $entero = intval($partes[0]);
        $decimal = str_pad($partes[1], 2, '0', STR_PAD_RIGHT);
    
        $texto = ucfirst($formatter->format($entero));
        $texto .= " con {$decimal}/100";
    
        return $texto;
    }

    private function generaContenidoContrato($id){
        $pago = DB::table('formatos')->select('contenido')->where('id', 1)->first();

        //obtener info del contrato
        $query = "SELECT
                        CONCAT(us.nombres,', ',us.apellidos) as propietario_nombre
                        ,us.documento as propietario_dni
                        ,CONCAT(us.direccion,' ',ubius.distrito,' - ',ubius.provincia,' - ',ubius.departamento) as propietario_direccion
                        ,CONCAT(i.nombres,', ',i.apellidos) as inquilino_nombre
                        ,i.dni as inquilino_dni
                        ,CONCAT(pr.direccion,' ',ubipr.distrito,' - ',ubipr.provincia,' - ',ubipr.departamento) as propiedad_direccion
                        ,un.descripcion as unidad_nombre
                        ,c.duracion as meses
                        ,DATE_FORMAT(c.fecha_inicio, '%d/%m/%Y') as inicio
                        ,DATE_FORMAT(c.fecha_fin, '%d/%m/%Y') as fin

                        ,DATE_FORMAT(c.fecha_inicio, '%d') as dia_pago
                        ,c.renta
                        ,c.servicios
                        ,DATE_FORMAT(DATE_SUB(c.fecha_inicio, INTERVAL 1 DAY), '%d') as dia_servicio
                    from contratos c
                    inner join inquilinos i on c.inquilino_id=i.id
                    inner join unidades un on c.unidad_id=un.id
                    inner join propiedades pr on un.propiedad_id=pr.id
                    inner join usuarios us on pr.propietario_id=us.id
                    left outer join ubigeos ubius on us.ubigeo_id=ubius.codigo
                    left outer join ubigeos ubipr on us.ubigeo_id=ubipr.codigo
                    where c.id=?";
        $rs = DB::select($query,[$id]);



        $plantilla = $pago->contenido;

        \Carbon\Carbon::setLocale('es');
        $fecha_actual = \Carbon\Carbon::now()->translatedFormat('l, d \d\e F \d\e Y');

        $valores = [
            '[propietario_nombre]' => strtoupper($rs[0]->propietario_nombre),
            '[propietario_dni]' => $rs[0]->propietario_dni,
            '[propietario_direccion]' => strtoupper($rs[0]->propietario_direccion),
            '[propiedad_direccion]' => strtoupper($rs[0]->propiedad_direccion),
            '[unidad_nombre]' => strtoupper($rs[0]->unidad_nombre),
            '[inquilino_nombre]' => strtoupper($rs[0]->inquilino_nombre),
            '[inquilino_dni]' => $rs[0]->inquilino_dni,
            '[meses]' => $rs[0]->meses,
            '[inicio]' => $rs[0]->inicio,
            '[fin]' => $rs[0]->fin,
            '[dia_pago]' => $rs[0]->dia_pago,
            '[renta]' => $rs[0]->renta,
            '[renta_texto]' => $this->numeroALetras($rs[0]->renta),
            '[servicios]' => $rs[0]->servicios,
            '[servicios_texto]' => $this->numeroALetras($rs[0]->servicios),
            '[dia_servicio]' => $rs[0]->dia_servicio,
            '[fecha_actual]' => $fecha_actual,
        ];

        return str_replace(array_keys($valores), array_values($valores), $plantilla);
    }
}