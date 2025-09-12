$(document).ready(function(){
    // Recorre el array y agrega opciones al select
    $.each(ubigeos, function (index, ubigeo) {
        $('#frm_ubigeo_id').append(
            $('<option>', {
                value: ubigeo.codigo,
                text: `${ubigeo.departamento} - ${ubigeo.provincia} - ${ubigeo.distrito}`
            })
        );
    });
    $('#frm_ubigeo_id').select2({
        width: '100%'
    })

    cargaData(usuario);

    $("#formid").validate({
        rules: {
            nombres:{
                required: true,
                maxlength: 200
            },
            apellidos:{
                required: true,
                maxlength: 200
            },
            documento:{
                required: true,
                pattern:/^\d{8}$/
            },
            direccion:{
                required: true,
                maxlength: 255
            },
            ubigeo_id:{
                required: true
            },
            email:{
                required: true,
                maxlength: 100,
                pattern:/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/
            },
            telefono:{
                pattern:/^9\d{8}$/
            }
        },
        messages:{
            email:{
                pattern: "<span class='text-danger'>Ingresar un correo válido</span>"
            },
            telefono:{
                pattern: "<span class='text-danger'>Ingresar un numero de celular valido</span>"
            },
            documento:{
                pattern: "<span class='text-danger'>Ingresar un DNI valido</span>"
            }
        },
        submitHandler: function (form) {
            fetchData('perfil', { 
                metodo: 'post', 
                data: getFormData('formid'), 
                button: 'guardarBtn',
                callback: function(error, response) {
                    if (!error) {
                        if(response.success){
                            toastr.success("Datos guardados correctamente","Genial!");
                            cargaData(response.usuario);
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

    $("#formid2").validate({
        rules: {
            password0:{
                required: true,
                maxlength: 200
            },
            password:{
                required: true,
                minlength: 6,
                maxlength: 20,
                pattern:/^(?=(?:.*[A-Za-z]){3,})(?=(?:.*\d){3,})(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).+$/
            },
            password1:{
                required: true,
                equalTo:"#frm_password"
            }
        },
        messages:{
            password:{
                pattern: "<span class='text-danger'>Debe tener almenos 3 letras, 3 numeros y 1 Simbolo</span>"
            },
            password1:{
                equalTo: "<span class='text-danger'>Contraseña no coincide</span>"
            }
        },
        submitHandler: function (form) {
            $("#formid2").submit();
        }
    });
});

function cargaData(usuario){
    //asignar valores
    document.getElementById('frm_updated_at').value = usuario.updated_at;
    document.getElementById('frm_updated_at2').value = usuario.updated_at;
    document.getElementById('frm_nombres').value = usuario.nombres;
    document.getElementById('frm_apellidos').value = usuario.apellidos;
    document.getElementById('frm_documento').value = usuario.documento;
    document.getElementById('frm_direccion').value = usuario.direccion;
    //document.getElementById('frm_ubigeo_id').value = usuario.ubigeo_id;
    document.getElementById('frm_email').value = usuario.email;
    document.getElementById('frm_telefono').value = usuario.telefono;
    $('#frm_ubigeo_id').val(usuario.ubigeo_id).trigger('change');
}