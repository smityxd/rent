@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Titulo</h5>
                <textarea id="editor">
                    {{ $contenido }}
                </textarea>
            </div>
            <div class="card-footer text-end">
                <button type="button" id="btnGrabar" class="btn btn-lg btn-success">Guardar Cambios</button>
            </div>
        </div>
    </div>
    <div class="col-4">
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

        $('#btnGrabar').click(function(){
            fetchData('/formatos/editarContrato', { 
                metodo: 'post', 
                data: {
                    id: @json($id),
                    contenido: $('#editor').val()
                }, 
                button: 'btnGrabar',
                callback: function(error, response) {
                    if (!error) {
                        toastr.success("Datos guardados correctamente","Genial!");
                    }else{
                        toastr.error("Algo salió mal: " + error,"ohoh !");
                    }
                }
            });
        });
    });
</script>
@endsection