$(document).ready(function(){
    // Recorre el array y agrega opciones al select
    $.each(ubigeos, function (index, ubigeo) {
        $('#frm_ubigeo_id').append(
            $('<option>', {
                value: ubigeo.codigo,
                text: `${ubigeo.departamento} - ${ubigeo.provincia} - ${ubigeo.distrito}`
            })
        ).select2({
            width: '100%',
            dropdownParent: $('#modal1')
        });
    });

    $("#nuevoBtn").click(function(){
        document.getElementById('frm_id').value=0;
        document.getElementById('frm_nombre').value = "";
        document.getElementById('frm_direccion').value = "";
        document.getElementById('frm_descripcion').value = "";
        document.getElementById("frm_estado").checked = true;
        $('#frm_ubigeo_id').val(null).trigger('change');
        toggleModal('modal1');
    });

    $("#actualizarBtn").click(function(){
        fetchData('propiedades/listado', { 
            button: 'actualizarBtn',
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    mostrarTabla("miTabla", response,["ubigeo_id"],{ubigeo_nom:"Ubigeo"})
                }else{
                    console.log(error);
                }
            }
        });
    }).trigger("click");

    $(document).on("click", ".editarBtn", function () {
        let id = $(this).data("id");
        fetchData('propiedades/listado', { 
            data: { id: id },
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    document.getElementById('frm_id').value=id;
                    document.getElementById('frm_nombre').value = data[0].nombre;
                    document.getElementById('frm_direccion').value = data[0].direccion;
                    document.getElementById('frm_descripcion').value = data[0].descripcion;
                    document.getElementById("frm_estado").checked = (data[0].estado == 1);
                    $('#frm_ubigeo_id').val(data[0].ubigeo_id).trigger('change');
                    toggleModal('modal1');
                }else{
                    console.log(error);
                }
            }
        });
    });

    $(document).on("click", ".eliminarBtn", function () {

        Swal.fire({
            title: "¿Estás seguro?",
            text: "No podrás revertir esta acción",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "No, cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                let id = $(this).data("id");
                fetchData('propiedades/borrar', {
                    metodo: 'DELETE',
                    data: { id: id },
                    callback: function(error, response) {
                        if (!error) {
                            $("#actualizarBtn").trigger("click");
                            toastr.success(response.message,"Genial!");
                        }else{
                            toastr.error("Algo salió mal: " + error,"ohoh !");
                        }
                    }
                });
            }
        });
        


    });

    $("#formid").validate({
        rules: {
            nombre:{
                required: true,
                maxlength: 100
            },
            direccion:{
                required: true,
                maxlength: 255
            },
            descripcion:{
                maxlength: 2000
            },
            ubigeo_id:{
                required: true
            }
        },
        submitHandler: function (form) {
            fetchData('propiedades/procesar', { 
                metodo: 'post', 
                data: getFormData('formid'), 
                button: 'guardarBtn',
                callback: function(error, response) {
                    if (!error) {
                        toggleModal('modal1');
                        $("#actualizarBtn").trigger("click");
                        toastr.success("Datos guardados correctamente","Genial!");
                    }else{
                        toastr.error("Algo salió mal: " + error,"ohoh !");
                    }
                }
            });
        }
    });
});