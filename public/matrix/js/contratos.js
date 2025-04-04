$(document).ready(function(){

    let inquilinoSelect = $("#frm_inquilino_id");
    inquilinos.forEach(function(inquilino) {
        inquilinoSelect.append(`<option value="${inquilino.id}">${inquilino.nom}</option>`);
    });

    let unidadSelect = $("#frm_unidad_id");
    unidades.forEach(function(unidad) {
        unidadSelect.append(`<option value="${unidad.id}">${unidad.nom}</option>`);
    });

    $("#frm_fecha_inicio").datepicker({
        format: "dd/mm/yyyy", // Formato de fecha
        autoclose: true,
        todayHighlight: true
    });

    $("#nuevoBtn").click(function(){
        document.getElementById('frm_id').value = 0;
        document.getElementById('frm_inquilino_id').value = "";
        document.getElementById('frm_unidad_id').value = "";

        let today = new Date();
        let formattedDate = today.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        $("#frm_fecha_inicio").datepicker("setDate", formattedDate);
        document.getElementById('frm_duracion').value = 3; // Duración por defecto 3 meses
        document.getElementById('frm_fecha_fin').value = "";

        document.getElementById('frm_renta').value = "";
        document.getElementById('frm_servicios').value = "";
        document.getElementById('frm_comentarios').value = "";
        document.getElementById("frm_generar").checked = true;
        document.getElementById("frm_estado").checked = true;

        //disparar el evento de la duracion
        $("#frm_duracion").trigger("input");
        toggleModal('modal1');
    });

    // Función para calcular la fecha final
    function calcularFechaFin() {
        let duracion = parseInt($("#frm_duracion").val()); // Obtener la duración en meses
        let fechaInicio = $("#frm_fecha_inicio").val(); // Obtener la fecha de inicio

        if (!isNaN(duracion) && fechaInicio) {
            // Convertir la fecha de inicio a un objeto Date
            let partesFecha = fechaInicio.split("/"); // Suponiendo formato dd/mm/yyyy
            let fechaInicioDate = new Date(partesFecha[2], partesFecha[1] - 1, partesFecha[0]);

            // Sumar la duración en meses
            fechaInicioDate.setMonth(fechaInicioDate.getMonth() + duracion);

            // Restar un día para obtener la fecha fin
            fechaInicioDate.setDate(fechaInicioDate.getDate() - 1);

            // Formatear la fecha final como dd/mm/yyyy
            let fechaFin = fechaInicioDate.toLocaleDateString("es-ES", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });

            // Establecer el valor en frm_fecha_fin
            $("#frm_fecha_fin").val(fechaFin);
        } else {
            // Si no hay duración o fecha de inicio, limpiar frm_fecha_fin
            $("#frm_fecha_fin").val("");
        }
    }

    // Escuchar cambios en el campo frm_duracion
    $("#frm_duracion").on("input", function () {
        calcularFechaFin();
    });

    // Escuchar cambios en el campo frm_fecha_inicio
    $("#frm_fecha_inicio").on("change", function () {
        calcularFechaFin();
    });

    $("#actualizarBtn").click(function(){
        fetchData('contratos/listado', { 
            button: 'actualizarBtn',
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    mostrarTabla("miTabla", data, ['inquilino_id','unidad_id'],{'fecha_inicio':'Inicio','fecha_fin':'Fin','duracion':'meses'},[{
                            "color": "primary",
                            "clase": "editarBtn",
                            "icono": "fa fa-edit",
                        },{
                            "color": "danger",
                            "clase": "eliminarBtn",
                            "icono": "fa fa-trash",
                        }])
                }else{
                    console.log(error);
                }
            }
        });
    }).trigger("click");

    $(document).on("click", ".editarBtn", function () {
        let id = $(this).data("id");
        fetchData('contratos/listado', { 
            data: { id: id },
            callback: function(error, response) {
                if (!error) {
                    let contrato = response;

                    document.getElementById('frm_id').value = contrato.id;
                    document.getElementById('frm_inquilino_id').value = contrato.inquilino_id;
                    document.getElementById('frm_unidad_id').value = contrato.unidad_id;
                    $("#frm_fecha_inicio").datepicker("setDate", contrato.fecha_inicio);
                    document.getElementById('frm_duracion').value = contrato.duracion;
                    document.getElementById('frm_fecha_fin').value = contrato.fecha_fin;
                    document.getElementById('frm_renta').value = contrato.renta;
                    document.getElementById('frm_servicios').value = contrato.servicios;
                    document.getElementById('frm_comentarios').value = contrato.comentarios;
                    document.getElementById("frm_generar").checked = false;
                    document.getElementById("frm_estado").checked = (contrato.estado == 1);

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
                fetchData('contratos/borrar', {
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
            inquilino_id:{
                required:true
            },
            unidad_id:{
                required:true
            },
            fecha_inicio:{
                required:true
            },
            duracion:{
                required:true,
                integer:true,
                min: 1
            },
            fecha_fin:{
                required:true
            },
            renta:{
                required:true,
                number:true,
                min: 0
            },
            servicios:{
                required:true,
                number:true,
                min: 0
            },
            comentarios:{
                required:true,
                maxlength: 254
            }
        },
        submitHandler: function (form) {
            fetchData('contratos/procesar', { 
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