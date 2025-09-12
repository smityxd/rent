@extends('auth.layout')
@section('contenido')
<div class="auth-wrapper d-flex no-block justify-content-center align-items-center bg-dark">
    <div class="auth-box bg-dark border-top border-secondary">
        <div id="loginform">
            <div class="text-center pt-3 pb-3">
                <span class="db"><img src="{{ asset('matrix/assets/images/logo.png') }}" alt="logo" /></span>
            </div>
            <!-- Form -->
            <form class="form-horizontal mt-3" id="loginform" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="row pb-4">
                    <div class="col-12">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-success text-white h-100" id="basic-addon1"><i
                                        class="mdi mdi-account fs-4"></i></span>
                            </div>
                            <input type="email" class="form-control form-control-lg" placeholder="Correo"
                                aria-label="Correo" aria-describedby="basic-addon1" required="required"
                                value="{{ old('email') }}" name="email" />
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-warning text-white h-100" id="basic-addon2"><i
                                        class="mdi mdi-lock fs-4"></i></span>
                            </div>
                            <input type="password" class="form-control form-control-lg" placeholder="Password"
                                aria-label="Password" aria-describedby="basic-addon1" required=""
                                name="password" />
                        </div>
                        @if ($errors->has('email'))
                        <div class="alert alert-danger">
                            {{ $errors->first('email') }}
                        </div>
                        @endif
                        @if (session('status'))
                            <div class="row mt-3 pt-3 border-top">
                                <div class="col-12">
                                    <div class="alert alert-danger">
                                        {{ session('status') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row border-top border-secondary">
                    <div class="col-12">
                        <div class="form-group">
                            <div class="pt-3">
                                <button class="btn btn-info" id="to-recover" type="button">
                                    <i class="mdi mdi-lock fs-4 me-1"></i> Lost password?
                                </button>
                                <button class="btn btn-success float-end text-white" type="submit">
                                    Login
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="recoverform">
            <div class="text-center">
                <span class="text-white">Ingresa tu correo y te enviaremos un codigo de recuperacion</span>
            </div>
            <div class="row mt-3">
                <!-- Form -->
                <form class="col-12" action="/recover" method="POST">
                    @csrf
                    <!-- email -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-email fs-4"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-lg" placeholder="Email Address" name="email"
                            aria-label="Username" aria-describedby="basic-addon1" />
                    </div>
                    <!-- pwd -->
                    <div class="row mt-3 pt-3 border-top border-secondary">
                        <div class="col-12">
                            <a class="btn btn-success text-white" href="javascript:void(*);" id="to-login" name="action">Regresar a Login</a>
                            <button class="btn btn-info float-end" type="submit">
                                Recuperar Contraseña
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $("#to-login").trigger('click');
    });
    $("#to-recover").on("click", function() {
        $("#loginform").slideUp();
        $("#recoverform").fadeIn();
    });
    $("#to-login").click(function() {
        $("#recoverform").hide();
        $("#loginform").fadeIn();
    });
</script>
@endsection