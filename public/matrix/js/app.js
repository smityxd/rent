$(document).ready(function(){
    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // });
    jQuery.extend(jQuery.validator.messages, {
        required: "<span class='text-danger'>*Este campo es Obligatorio.</span>",
        remote: "<span class='text-danger'>*Ingrear un Dato válido.</span>",
        email: "<span class='text-danger'>*Ingresar una direccion válida.</span>",
        url: "<span class='text-danger'>*Ingresar una direccion URL válida.</span>",
        date: "<span class='text-danger'>*Ingresar una Fecha Válida.</span>",
        dateISO: "<span class='text-danger'>*Ingresar una Fecha Válida (ISO).</span>",
        number: "<span class='text-danger'>*Ingresar un Número Válido.</span>",
        digits: "<span class='text-danger'>*Ingresar solo Dígitos.</span>",
        creditcard: "<span class='text-danger'>*Ingresar un numero de Tarjeta Válido.</span>",
        equalTo: "<span class='text-danger'>*Ingresar el Mismo Valor.</span>",
        accept: "<span class='text-danger'>*Ingresar un valor con la misma Extensión.</span>",
        maxlength: jQuery.validator.format("<span class='text-danger'>*El largo no debe ser mas de {0} caracteres.</span>"),
        minlength: jQuery.validator.format("<span class='text-danger'>*El minimo debe ser de {0} caracteres.</span>"),
        rangelength: jQuery.validator.format("<span class='text-danger'>*Ingresar un valor de {0} y {1} caracteres de largo.</span>"),
        range: jQuery.validator.format("<span class='text-danger'>*Ingresar un varlo entre {0} y {1}.</span>"),
        max: jQuery.validator.format("<span class='text-danger'>*Ingresar un valor menor o igual a {0}.</span>"),
        min: jQuery.validator.format("<span class='text-danger'>*Ingrear un valor mayor o igual a {0}.</span>"),
        time: jQuery.validator.format("<span class='text-danger'>*Ingresar una Hora valida, entre 00:00 y 23:59.</span>"),
        alphanumeric: jQuery.validator.format("<span class='text-danger'>*Solo Numeros, Letras o guión bajo _.</span>"),
        integer: jQuery.validator.format("<span class='text-danger'>*Solo ingresar numeros enteros (sin decimales)</span>")
    });
});

function logout() {
    if (confirm("¿Seguro que quieres cerrar sesión?")) {
        document.getElementById('logout-form').submit();
    }
}

function toggleModal(modalId, action = "toggle", options = {}) {
    let modalElement = document.getElementById(modalId);

    if (!modalElement) {
        console.error(`El modal con ID '${modalId}' no existe.`);
        return;
    }

    let modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement, options);

    if (action === "open") {
        modal.show();
    } else if (action === "close") {
        modal.hide();
    } else if (action === "toggle") {
        modalElement.classList.contains("show") ? modal.hide() : modal.show();
    } else {
        console.error("Acción no válida. Usa 'open', 'close' o 'toggle'.");
    }
}

function getFormData(formId) {
    let formData = {};
    
    $(`#${formId}`).find("input, select, textarea").each(function() {
        if (this.name) {
            if ($(this).is(":checkbox")) {
                formData[this.name] = $(this).is(":checked") ? 1 : 0; // Si está marcado, devuelve 1, si no, 0
            } else {
                formData[this.name] = $(this).val();
            }
        }
    });
    return formData;
}

function fetchData(url, options = {}, destination = '') {
    let metodo = options.metodo || 'GET';
    let data = options.data || {};
    let buttonId = options.button || null;
    let callback = options.callback || null;
    let tipo = options.tipo || 'json';

    if (metodo.toUpperCase() != 'get') {
        data._token = $('meta[name="csrf-token"]').attr('content');
    }
    //console.log(data);
    let defaultOptions = {
        type: metodo,  // Ahora usa la variable "metodo" correctamente
        data: data,
        dataType: tipo,
        beforeSend: function() {
            if (buttonId) {
                let btn = $("#" + buttonId);
                btn.attr("data-original-text", btn.html()); // Guarda el texto original
                btn.html('<span class="spinner-border spinner-border-sm"></span> Cargando...').prop("disabled", true);
            }
            $('.crm').attr('disabled',true);
        },
        success: function(response) {
            if(destination!="") $(destination).html(response);
            if (callback && typeof callback === "function") {
                callback(null, response); // Llamamos al callback pasando el resultado
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la solicitud AJAX:", error);
            if (callback && typeof callback === "function") {
                callback(error, null); // Llamamos al callback con el error
            }
        },
        complete: function() {
            if (buttonId) {
                let btn = $("#" + buttonId);
                btn.html(btn.attr("data-original-text")).prop("disabled", false);
            }
            $('.crm').attr('disabled',false);
        }
    };

    $.ajax(url, defaultOptions);
}

function mostrarTabla(tableId, data, columnasIgnorar = [], mapeoColumnas = {}, botones = []) {
    let tableElement = $("#" + tableId);

    if (!tableElement.length) {
        console.error(`La tabla con ID '${tableId}' no existe.`);
        return;
    }

    // Si no hay datos, mostrar mensaje en la tabla
    if (!data || data.length === 0) {
        tableElement.html(`
            <div class="alert alert-warning text-center" role="alert">
                Aún no existen registros
            </div>
        `);
        return;
    }

    // Si la tabla ya tiene un DataTable inicializado, lo destruimos antes de volver a inicializarlo
    if ($.fn.DataTable.isDataTable("#" + tableId)) {
        tableElement.DataTable().destroy();
    }

    // Filtrar columnas a ignorar y aplicar mapeo de nombres en encabezados
    let columnas = Object.keys(data[0]).filter(col => !columnasIgnorar.includes(col));
    let tableHeaders = columnas
        .map(col => `<th>${mapeoColumnas[col] || col}</th>`) // Aplica mapeo si existe
        .join("");

    tableElement.html(`<thead><tr>${tableHeaders.toUpperCase()}</tr></thead><tbody></tbody>`);

    // Inicializar DataTable con los datos filtrados
    tableElement.DataTable({
        data: data.map(row => {
            return columnas.map(key => {
                if (key === "estado") {
                    return row[key] == "1"
                        ? '<span class="badge rounded-pill bg-success">Activo</span>'
                        : '<span class="badge rounded-pill bg-secondary">Inactivo</span>';
                } else if (key === "id") {
                    return `<div class="btn-group" role="group">
                        ${botones
                            .map(boton => `
                                <button data-id="${row[key]}" class="btn btn-${boton.color} ${boton.clase}">
                                    <i class="${boton.icono}"></i>
                                </button>
                            `)
                            .join("")}
                    </div>`;
                }
                return row[key];
            });
        }),
        destroy: true,
        responsive: true,
        autoWidth: false,
    });
}
