let nfilasUnidades = 0;
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

    $("#nuevoBtn").click(function(){
        document.getElementById('frm_id').value = 0;
        document.getElementById('frm_nombre').value = "";
        document.getElementById('frm_direccion').value = "";
        document.getElementById('frm_descripcion').value = "";
        document.getElementById("frm_estado").checked = true;
        $('#frm_ubigeo_id').val(null).trigger('change');
        nfilasUnidades = 0;
        $('table#tblunidades > tbody').empty();
        toggleModal('modal1');
    });

    $("#btnAddUnidad").click(function(){
        addfila([]);
    });

    function addfila(data) {
        var table = $('table#tblunidades > tbody');
        var fila = nfilasUnidades;
        var row = '<tr data-position="' + fila + '">';
        row += '<td><input type="hidden" id="det_status[' + fila + ']" name="det_status[' + fila + ']" value="' + (data.status || 1) + '">';
        row += '<input type="hidden" id="det_id[' + fila + ']" name="det_id[' + fila + ']" value="' + (data.id || 0) + '">';
        row += '<button class="btn btn-danger btn-sm btn-eliminar" type="button"><i class="fa fa-minus-circle" aria-hidden="true"></i></button></td>';
        row += '<td><select class="form-control form-control-sm det_tipo" id="det_tipo[' + fila + ']" name="det_tipo[' + fila + ']"></select></td>';
        row += '<td><input type="text" class="form-control form-control-sm" id="det_descripcion[' + fila + ']" name="det_descripcion[' + fila + ']" placeholder="Descripción" value="' + (data.descripcion || '') + '"></td>';
        row += '<td><input type="number" class="form-control form-control-sm" id="det_costo[' + fila + ']" name="det_costo[' + fila + ']" placeholder="Costo" value="' + (data.costo || 0) + '" min="0"></td>';
        row += '<td><input type="number" class="form-control form-control-sm" id="det_piso[' + fila + ']" name="det_piso[' + fila + ']" placeholder="# Piso" value="' + (data.piso || 1) + '" min="1"></td>';
        row += '<td><select class="form-control form-control-sm det_estado" id="det_estado[' + fila + ']" name="det_estado[' + fila + ']"></select></td>';
        row += '</tr>';
        table.append(row);

        // Populate det_tipo options
        var tipoOptions = [
            { "id": "CQ", "text": "Cuarto" },
            { "id": "CB", "text": "Cuarto + Baño" },
            { "id": "MD", "text": "Mini-departamento" },
            { "id": "DE", "text": "Departamento" },
            { "id": "CA", "text": "Casa" },
            { "id": "ED", "text": "Edificio" },
            { "id": "OT", "text": "Otro" }
        ];
        var tipoSelect = $('select[name="det_tipo[' + fila + ']"]');
        tipoOptions.forEach(function(option) {
            tipoSelect.append(new Option(option.text, option.id));
        });
        tipoSelect.val(data.tipo || "CQ");

        // Populate det_estado options
        var estadoOptions = [
            { "id": "1", "text": "Si" },
            { "id": "0", "text": "No" }
        ];
        var estadoSelect = $('select[name="det_estado[' + fila + ']"]');
        estadoOptions.forEach(function(option) {
            estadoSelect.append(new Option(option.text, option.id));
        });
        estadoSelect.val(data.estado || "1");

        nfilasUnidades++;
    }

    //eliminar fila
    $(document).on('click', 'button.btn-eliminar', function () {
        var boton = $(this);
        var fila = boton.parents('tr');
        var position = fila.data('position');
        var statusElement = document.getElementById('det_status[' + position + ']');
        var currentStatus = parseInt(statusElement.value);
        if (currentStatus === 1) statusElement.value = 3;
        else if (currentStatus === 2)statusElement.value = 4;
        fila.hide();
    });

    $("#actualizarBtn").click(function(){
        fetchData('propiedades/listado', { 
            button: 'actualizarBtn',
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    mostrarTabla("miTabla", response,["ubigeo_id"],{ubigeo_nom:"Ubigeo"},[{
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
        fetchData('propiedades/listado', { 
            data: { id: id },
            callback: function(error, response) {
                if (!error) {
                    let propiedad = response.propiedad;
                    let unidades = response.unidades;

                    document.getElementById('frm_id').value = id;
                    document.getElementById('frm_nombre').value = propiedad.nombre;
                    document.getElementById('frm_direccion').value = propiedad.direccion;
                    document.getElementById('frm_descripcion').value = propiedad.descripcion;
                    document.getElementById("frm_estado").checked = (propiedad.estado == 1);
                    $('#frm_ubigeo_id').val(propiedad.ubigeo_id).trigger('change');

                    // Clear existing rows in the table
                    $('table#tblunidades > tbody').empty();

                    // Reset the counter for nfilasUnidades
                    nfilasUnidades = 0;

                    // Add rows for each unidad using $.each
                    $.each(unidades, function(index, unidad) {
                        unidad.status = 2;
                        addfila(unidad);
                    });

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