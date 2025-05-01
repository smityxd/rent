
$(document).ready(function(){
    $("#nuevoBtn").click(function(){
        document.getElementById('frm_id').value = 0;
        document.getElementById('frm_nombres').value = "";
        document.getElementById('frm_apellidos').value = "";
        document.getElementById('frm_dni').value = "";
        document.getElementById('frm_observaciones').value = "";
        document.getElementById("frm_estado").checked = true;
        toggleModal('modal1');
    });

    $("#actualizarBtn").click(function(){
        fetchData('inquilinos/listado', { 
            button: 'actualizarBtn',
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    mostrarTabla("miTabla", response,[],{},[{
                            "color": "primary",
                            "clase": "editarBtn",
                            "icono": "fa fa-edit",
                        },{
                            "color": "danger",
                            "clase": "eliminarBtn",
                            "icono": "fa fa-trash",
                        }]);
                }else{
                    console.log(error);
                }
            }
        });
    }).trigger("click");

    $(document).on("click", ".editarBtn", function () {
        let id = $(this).data("id");
        fetchData('inquilinos/listado', { 
            data: { id: id },
            callback: function(error, response) {
                if (!error) {
                    document.getElementById('frm_id').value = id;
                    document.getElementById('frm_nombres').value = response.nombres;
                    document.getElementById('frm_apellidos').value = response.apellidos;
                    document.getElementById('frm_dni').value = response.dni;
                    document.getElementById('frm_observaciones').value = response.observaciones;
                    document.getElementById("frm_estado").checked = (response.estado == 1);
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
                fetchData('inquilinos/borrar', {
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
            nombres:{
                required: true,
                maxlength: 100
            },
            apellidos:{
                required: true,
                maxlength: 100
            },
            dni:{
                required: true,
                number: true,
                maxlength: 8
            },
            observaciones:{
                maxlength: 255
            },
        },
        submitHandler: function (form) {
            fetchData('inquilinos/procesar', { 
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