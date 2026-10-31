$(document).ready(function(){
    let selectedData;
    $("#frm_fecha_pago").datepicker({
        format: "dd/mm/yyyy", // Formato de fecha
        autoclose: true,
        todayHighlight: true
    });

    $("#contratos").select2({
        data:contratos
    }).on("select2:select", function (data) {
        selectedData = data.params.data;
        let id = data.params.data.id;

        if (!id) {
            $("#datosContrato").html('');
            $("#miTablaPagos thead").empty();
            $("#miTablaPagos tbody").empty();
            $(".detalle").addClass('d-none');
            return;
        }
        $(".detalle").removeClass('d-none');

        let cardHtml = `
            <div class="card text-center">
            <div class="card-header bg-info text-white">
                Contrato ID: ${data.params.data.id_}
            </div>
            <div class="card-body">
                <h5 class="card-title">${data.params.data.p_desc}</h5>
                <p class="card-text">
                <strong>Unidad Habitacional:</strong> ${data.params.data.u_desc}<br>
                <strong>Fecha Inicio:</strong> ${data.params.data.fecha_inicio}<br>
                <strong>Fecha Fin:</strong> ${data.params.data.fecha_fin}<br>
                <strong>Inquilino:</strong> ${data.params.data.nombres} ${data.params.data.apellidos}
                </p>
            </div>
            <div class="card-footer text-white ${data.params.data.estado === 'VENCIDO' ? 'bg-danger' : 'bg-success'}">
                Estado: ${data.params.data.estado}
            </div>
            </div>
        `;
        $("#datosContrato").html(cardHtml);

        fetchData('/alquileres/pagos/listado', {
            data: { id: id },
            callback: function(error, response) {
                if (!error) {
                    let data = response;
                    let thead = $("#miTablaPagos thead");
                    thead.empty(); // Limpiar el contenido existente
                    thead.append(`
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Fecha Vencimiento</th>
                            <th>Renta</th>
                            <th>Otros</th>
                            <th>Adicionales</th>
                            <th>Comentarios</th>
                        </tr>
                    `);
                    let tbody = $("#miTablaPagos tbody");
                    tbody.empty(); // Limpiar el contenido existente
                
                    data.forEach(function (item) {
                        // Solo usar los elementos donde pago_id es null
                        if (item.pago_id === null) {
                            // Crear una fila con los valores requeridos
                            let fila = `
                                <tr class='table-${item.estado_color}'>
                                    <td>
                                        <button class='btn btn-primary addBtn' data-id='${item.id}'>Agregar Pago</button>
                                        <button class='btn btn-warning editarBtn' data-id='${item.id}'>Editar Pago</button>
                                        <button class='btn btn-warning reciboBtn' data-id='${item.id}'>Recibo</button>
                                    </td>
                                    <td>${item.id}</td>
                                    <td>
                                        <strong>Fecha de Pago:</strong> ${item.fecha_pago ? item.fecha_pago.toUpperCase() : "NO REGISTRADA"}<br>
                                        <strong class='text-${item.estado_color}'>${item.estado_fecha.toUpperCase()}</strong><br>
                                        <input type="hidden" class="fecha_pago" value='${item.fecha_pago || ""}'>
                                    </td>
                                    <td>
                                        <strong>Renta:</strong> ${parseFloat(item.renta).toFixed(2)}<br>
                                        <strong>Pendiente:</strong> ${parseFloat(item.renta_saldo).toFixed(2)}
                                        <input type="hidden" class="renta" value='${item.renta}'>
                                        <input type="hidden" class="renta_saldo" value='${item.renta_saldo}'>
                                    </td>
                                    <td>
                                        <strong>Servicios:</strong> ${parseFloat(item.servicios).toFixed(2)}<br>
                                        <strong>Pendiente:</strong> ${parseFloat(item.servicios_saldo).toFixed(2)}
                                        <input type="hidden" class="servicios" value='${item.servicios}'>
                                        <input type="hidden" class="servicios_saldo" value='${item.servicios_saldo}'>
                                    </td>
                                    <td>
                                        <strong>Otros Pagos:</strong> ${parseFloat(item.otros_pagos).toFixed(2)}<br>
                                        <strong>Pendiente:</strong> ${parseFloat(item.otros_pagos_saldo).toFixed(2)}
                                        <input type="hidden" class="otros_pagos" value='${item.otros_pagos}'>
                                        <input type="hidden" class="otros_pagos_saldo" value='${item.otros_pagos_saldo}'>
                                    </td>
                                    <td>
                                        <strong>Comentario:</strong> ${item.comentario || "Sin comentarios"}
                                        <input type="hidden" class="comentario" value='${item.comentario}'>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="7"><div class="card-container">`;
                                    data.forEach(function (item2) {
                                        if (item2.pago_id == item.id) {
                                            fila += `
                                                
                                                    <div class="card mb-3 text-white bg-primary">
                                                        <div class="card-body">
                                                            <h5 class="card-title">${item2.comentario || "Pago Relacionado"}</h5>
                                                            <p class="card-text">
                                                                <strong>Fecha de pago:</strong> ${item2.fecha_pago}<br>
                                                                <strong>Renta:</strong> ${parseFloat(item2.renta).toFixed(2)}<br>
                                                                <strong>Servicios:</strong> ${parseFloat(item2.servicios).toFixed(2)}<br>
                                                                <strong>Otros Pagos:</strong> ${parseFloat(item2.otros_pagos).toFixed(2)}<br>
                                                                <button class='btn btn-danger eliminarBtn' data-id='${item2.id}'>Eliminar Pago</button>
                                                            </p>
                                                        </div>
                                                    </div>
                                                
                                            `;
                                        }
                                    });
                            fila += `</div></td></tr>`;
                            tbody.append(fila); // Agregar la fila al tbody
                        }
                    });
                }else{
                    console.log(error);
                }
            }
        });
    });

    $(document).on("click", ".addBtn", function () {
        let id = $(this).data("id");
        document.getElementById("frm_id").value = 0;
        document.getElementById("frm_pago_id").value = id;
        document.getElementById("frm_contrato_id").value = document.getElementById("contratos").value;

        let row = $(this).closest("tr");
        let currentDate = new Date();
        let formattedDate = currentDate.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
        $("#frm_fecha_pago").datepicker("update", formattedDate);
        document.getElementById("frm_renta").value = row.find(".renta_saldo").val();
        document.getElementById("frm_servicios").value = row.find(".servicios_saldo").val();
        document.getElementById("frm_otros_pagos").value = row.find(".otros_pagos_saldo").val();

        toggleModal('modal1');
    });

    $(document).on("click", ".editarBtn", function () {
        let id = $(this).data("id");
        document.getElementById("frm_id").value = id;
        document.getElementById("frm_pago_id").value = 0;
        document.getElementById("frm_contrato_id").value = document.getElementById("contratos").value;

        let row = $(this).closest("tr");
        let currentDate = new Date();
        $("#frm_fecha_pago").datepicker("update", row.find(".fecha_pago").val());
        document.getElementById("frm_renta").value = row.find(".renta").val();
        document.getElementById("frm_servicios").value = row.find(".servicios").val();
        document.getElementById("frm_otros_pagos").value = row.find(".otros_pagos").val();
        document.getElementById("frm_comentario").value = row.find(".comentario").val();;

        toggleModal('modal1');
    });

    $(document).on("click", ".reciboBtn", function () {
        let id = $(this).data("id");
        fetchData('/alquileres/formatos/descargar-recibo', {
            metodo: 'get',
            tipo: 'text',
            data: {
                id: id,
                modo: 'simple',
                contrato: {
                    apellidos: selectedData.apellidos,
                    estado: selectedData.estado,
                    fecha_fin: selectedData.fecha_fin,
                    fecha_finx: selectedData.fecha_finx,
                    fecha_inicio: selectedData.fecha_inicio,
                    id: selectedData.id,
                    id_: selectedData.id_,
                    nombres: selectedData.nombres,
                    p_desc: selectedData.p_desc,
                    selected: selectedData.selected,
                    u_desc: selectedData.u_desc
                }
            },
            callback: function(error, response) {
                console.log('ok');
                $('#recibo').html(response);
                toggleModal('modal2');
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
                fetchData('/alquileres/pagos/borrar', {
                    metodo: 'DELETE',
                    data: { id: id },
                    callback: function(error, response) {
                        if (!error) {
                            toastr.success(response.message,"Genial!");
                            $("#contratos").trigger({
                                type: 'select2:select',
                                params: {
                                    data: selectedData
                                }
                            });
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
            fecha_pago:{
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
            otros_pagos:{
                required:true,
                number:true
            },
            comentarios:{
                maxlength: 254
            }
        },
        submitHandler: function (form) {
            fetchData('/alquileres/pagos/procesar', { 
                metodo: 'post', 
                data: getFormData('formid'), 
                button: 'guardarBtn',
                callback: function(error, response) {
                    if (!error) {
                        toggleModal('modal1');
                        // Limpiar el formulario
                        document.getElementById("formid").reset();
                        toastr.success("Datos guardados correctamente","Genial!");
                        $("#contratos").trigger({
                            type: 'select2:select',
                            params: {
                                data: selectedData
                            }
                        });
                    }else{
                        toastr.error("Algo salió mal: " + error,"ohoh !");
                    }
                }
            });
        }
    });
});