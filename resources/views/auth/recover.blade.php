@extends('auth.layout')
@section('contenido')
<div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark">
    <div class="auth-box bg-dark border-top border-secondary">
        <div id="recoverform">
            <div class="text-center">
                <span class="text-white">Ingresa el Codigo enviado a tu correo</span>
            </div>
            <div class="row mt-3">
                <!-- Form -->
                <form class="col-12" action="/validate" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-email fs-4"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-lg" placeholder="Email Address" name="email"
                            aria-label="Username" aria-describedby="basic-addon1" value="{{ $email }}"/>
                    </div>
                    <!-- email -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-key fs-4"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-lg" placeholder="Codigo de verificacion" name="token" aria-describedby="basic-addon1" />
                    </div>
                    <!-- pwd -->
                    <div class="row mt-3 pt-3 border-top border-secondary">
                        <div class="col-12">
                            <a class="btn btn-success text-white" href="{{ asset('/login') }}" id="to-login" >Back To
                                Login</a>
                            <button class="btn btn-info float-end" type="submit" >
                                Validar
                            </button>
                        </div>
                    </div>
                    @if (session('status'))
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
    });
</script>
@endsection