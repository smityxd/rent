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
                <form class="col-12" id="formid" action="/changepassword" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-email fs-4"></i></span>
                        </div>
                        <input type="text" class="form-control form-control-lg" placeholder="Email Address" name="email"
                            aria-label="Email" aria-describedby="basic-addon1" value="{{ $email }}" />
                    </div>
                    <!-- email -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-key fs-4"></i></span>
                        </div>
                        <input type="password" class="form-control form-control-lg" placeholder="Password" name="password"
                            aria-label="Password" aria-describedby="basic-addon1" />
                    </div>
                    <!-- password -->
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-danger text-white h-100" id="basic-addon1"><i
                                    class="mdi mdi-key fs-4"></i></span>
                        </div>
                        <input type="password" class="form-control form-control-lg" placeholder="Confirm Password" name="password_confirmation"
                            aria-label="Confirm Password" aria-describedby="basic-addon1" />
                    </div>
                    <div class="input-group mb-3">
                        <small class="text-danger">Recuerda que la contraseña debe tener al menos menos 3 letras, 3 números y 1 símbolo.</small>
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
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize form validation on the registration form.
        $("#formid").validate({
            rules: {
                email: {
                    required: true,
                    pattern:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
                },
                password: {
                    required: true,
                    pattern:/^(?=(?:.*[A-Za-z]){3,})(?=(?:.*\d){3,})(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).+$/
                },
                password_confirmation: {
                    required: true,
                    equalTo: "[name='password']"
                }
            },
            messages: {
                pattern: "Por favor ingresa un correo electrónico válido.",
                password: {
                    required: "Por favor ingresa una contraseña.",
                    pattern: "<span class='text-danger'>Debe tener almenos 3 letras, 3 numeros y 1 Simbolo</span>"
                },
                password_confirmation: {
                    required: "Por favor confirma tu contraseña.",
                    equalTo: "Las contraseñas no coinciden."
                }
            },
            submitHandler: function (form) {
                fetchData('resetpass', { 
                    metodo: 'post', 
                    data: getFormData('formid2'), 
                    button: 'resetpassBtn',
                    callback: function(error, response) {
                        if (!error) {
                            if(response.success){
                                toastr.success("Contraseña cambiada correctamente!","Genial!");
                                document.getElementById('frm_updated_at').value = response.updated_at;
                                document.getElementById('frm_updated_at2').value = response.updated_at;
                                document.getElementById('frm_password').value = '';
                                document.getElementById('frm_password1').value = '';
                                document.getElementById('frm_password0').value = '';
                            }else{
                                toastr.error("Algo salió mal: " + response.error,"ohoh !");
                            }
                        }else{
                            toastr.error("Algo salió mal: " + error,"ohoh !");
                        }
                    }
                });
            }
        });
    });
</script>
@endsection