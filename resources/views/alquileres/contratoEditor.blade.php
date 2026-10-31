@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Titulo</h5>
                <textarea id="editor">
                    {{ $contenido }}
                </textarea>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" id="btnRestablecer" class="btn btn-warning" data-personalizado="@json($personalizado)"><i class="mdi mdi-refresh"></i> Restablecer predeterminada</button>
                <div>
                    <button type="button" id="btnVistaPrevia" class="btn btn-info text-white">Vista previa</button>
                    <button type="button" id="btnGrabar" class="btn btn-success">Guardar cambios</button>
                </div>
            </div>
            <div class="card-body border-top" id="contenedorVistaPrevia" style="display:none">
                <h5>Vista previa</h5>
                <div id="vistaPrevia" class="border p-3 bg-white"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Campos</h5>
                <ul>
                    @foreach($campos as $indice => $campo)
                        <li><b>{{ $indice }}:</b> {{ $campo }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function(){
        
        $('#editor').summernote({
            placeholder: 'Escribe aquí...',
            tabsize: 2,
            height: 450,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']],
                ['misc', ['undo', 'redo']]
            ]
        });

        $('#btnVistaPrevia').click(function(){
            fetchData('{{ route('alquileres.formatos.vista-previa') }}', {
                metodo: 'post', data: {id: @json($id), contenido: $('#editor').val()}, button: 'btnVistaPrevia',
                callback: function(error, response) {
                    if (!error) {
                        $('#contenedorVistaPrevia').show(); $('#vistaPrevia').html(response.data);
                    } else toastr.error(error,'Vista previa');
                }
            });
        });

        $('#btnGrabar').click(function(){
            fetchData('{{ route('alquileres.formatos.editar') }}', {
                metodo: 'post', data: {id: @json($id), contenido: $('#editor').val()}, button: 'btnGrabar',
                callback: function(error, response) {
                    if (!error) { $('#btnRestablecer').data('personalizado', true); toastr.success('Datos guardados correctamente','Genial!'); }
                    else toastr.error('Algo salió mal: '+error,'Error');
                }
            });
        });

        $('#btnRestablecer').click(function(){
            Swal.fire({title:'Restablecer plantilla',text:'Se eliminará el formato personalizado de este contrato.',icon:'warning',showCancelButton:true,confirmButtonText:'Restablecer',cancelButtonText:'Cancelar'}).then(function(result){
                if (result.isConfirmed) fetchData('{{ route('alquileres.formatos.restablecer') }}',{metodo:'post',data:{id:@json($id)},button:'btnRestablecer',callback:function(error){
                    if (!error) location.reload(); else toastr.error(error,'Error');
                }});
            });
        });
    });
</script>
@endsection