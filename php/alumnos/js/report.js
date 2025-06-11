document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('reportModal');
    const modalNombre = document.getElementById('modalNombre');
    const modalContacto = document.getElementById('modalContacto');
    const modalGrado = document.getElementById('modalGrado');
    const modalConcepto = document.getElementById('modalConcepto');
    const modalFechaPago = document.getElementById('modalFechaPago');
    const modalMonto = document.getElementById('modalMonto');
    const modalMetodo = document.getElementById('modalMetodo');
    const formIdEstudiante = document.getElementById('formIdEstudiante');
    const formIdPago = document.getElementById('formIdPago');
    const reportForm = document.getElementById('reportForm');
    const closeModal = document.querySelector('.close-modal');
    const cancelModal = document.getElementById('cancelModal');
    const modalPagosBody = document.getElementById('modalPagosBody');
    const modalTotalPagos = document.getElementById('modalTotalPagos');

    // Abrir modal con datos del pago específico
    document.querySelectorAll('.btn-validate').forEach(button => {
        button.addEventListener('click', function() {
            formIdEstudiante.value = this.dataset.id;
            formIdPago.value = this.dataset.pagoId;
            
            modalNombre.textContent = this.dataset.nombre;
            modalContacto.textContent = this.dataset.contacto;
            modalGrado.textContent = this.dataset.grado;
            modalConcepto.textContent = this.dataset.concepto;
            modalFechaPago.textContent = this.dataset.fechaPago;
            modalMonto.textContent = `S/ ${this.dataset.monto}`;
            modalMetodo.textContent = this.dataset.metodo;
            
            modal.style.display = 'block';
        });
    });
    
    // Cerrar modal
    function cerrarModal() {
        modal.style.display = 'none';
        if(modalPagosBody) modalPagosBody.innerHTML = '';
    }
    
    if(closeModal) closeModal.addEventListener('click', cerrarModal);
    if(cancelModal) cancelModal.addEventListener('click', cerrarModal);
    
    // Cerrar modal al hacer clic fuera de él
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });
    
    // Función para cargar los pagos del estudiante
    function cargarPagos(idEstudiante) {
        fetch('obtener_pagos.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_estudiante=${idEstudiante}&_token=${window.csrfToken}`
        })
        .then(response => response.json())
        .then(data => {
            // Limpiar tabla
            if(modalPagosBody) modalPagosBody.innerHTML = '';
            
            // Llenar tabla con los pagos
            if(data.pagos && modalPagosBody) {
                data.pagos.forEach(pago => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${pago.fecha}</td>
                        <td>${pago.concepto}</td>
                        <td>${pago.metodo}</td>
                        <td>S/ ${pago.monto}</td>
                        <td>${pago.estado}</td>
                    `;
                    modalPagosBody.appendChild(row);
                });
            }
            
            // Actualizar total
            if(modalTotalPagos) modalTotalPagos.textContent = `S/ ${data.total}`;
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudieron cargar los pagos', 'error');
        });
    }
    
    // Enviar formulario de reporte
    if(reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            
            Swal.fire({
                title: '¿Generar boleta individual?',
                text: 'Se creará una boleta para este pago específico',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('generar_boleta.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Mostrar boleta en la misma página
                            const printContainer = document.createElement('div');
                            printContainer.style.position = 'fixed';
                            printContainer.style.top = '0';
                            printContainer.style.left = '0';
                            printContainer.style.width = '100%';
                            printContainer.style.height = '100%';
                            printContainer.style.backgroundColor = 'white';
                            printContainer.style.zIndex = '9999';
                            printContainer.style.overflow = 'auto';
                            printContainer.style.padding = '20px';
                            printContainer.innerHTML = data.html;

                            // Agregar botones de acción
                            const printActions = document.createElement('div');
                            printActions.style.position = 'fixed';
                            printActions.style.bottom = '20px';
                            printActions.style.right = '20px';
                            printActions.style.zIndex = '10000';
                            printActions.style.display = 'flex';
                            printActions.style.gap = '10px';

                            const printBtn = document.createElement('button');
                            printBtn.textContent = 'Imprimir';
                            printBtn.className = 'btn btn-primary';
                            printBtn.onclick = function() {
                                window.print();
                            };

                            const closeBtn = document.createElement('button');
                            closeBtn.textContent = 'Cerrar';
                            closeBtn.className = 'btn btn-secondary';
                            closeBtn.onclick = function() {
                                document.body.removeChild(printContainer);
                                document.body.removeChild(printActions);
                            };

                            printActions.appendChild(printBtn);
                            printActions.appendChild(closeBtn);

                            // Mostrar en la página
                            document.body.appendChild(printContainer);
                            document.body.appendChild(printActions);

                            Swal.fire({
                                title: 'Boleta generada',
                                text: `Boleta ${data.numero_boleta} creada correctamente`,
                                icon: 'success',
                                timer: 2000
                            });
                            
                            modal.style.display = 'none';
                        } else {
                            Swal.fire('Error', data.error || 'Error al generar boleta', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Error al conectar con el servidor', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        });
    }
    
    // Manejar logout
    const logoutBtn = document.getElementById('logoutBtn');
    if(logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        });
    }

     $(document).ready(function() {
       // Inicializar contador de historial
       var historialCount = parseInt($("#historyCount").text()) || 0;

       // Mostrar/ocultar historial
       $("#toggleHistory").click(function () {
         $("#historyPanel").slideToggle(function () {
           var isVisible = $(this).is(":visible");
           var iconClass = isVisible ? "fa-times" : "fa-history";
           var buttonText = isVisible
             ? "Ocultar Historial"
             : "Mostrar Historial";

           $("#toggleHistory").html(
             `<i class="fas ${iconClass}"></i> ${buttonText} <span class="badge">${historialCount}</span>`
           );
         });
       });

       // Manejar clic en botones de generación
       $(document).on("click", ".btn-generate", function () {
         var estudianteId = $(this).data("id");
         var pagoId = $(this).data("pago-id");

         // Llenar datos en el modal
         $("#formIdEstudiante").val(estudianteId);
         $("#formIdPago").val(pagoId);
         $("#modalNombre").text($(this).data("nombre"));
         $("#modalContacto").text($(this).data("contacto"));
         $("#modalGrado").text($(this).data("grado"));
         $("#modalConcepto").text($(this).data("concepto"));
         $("#modalFechaPago").text($(this).data("fecha-pago"));
         $("#modalMonto").text("S/ " + $(this).data("monto"));
         $("#modalMetodo").text($(this).data("metodo"));

         // Mostrar modal
         $("#boletaModal").show();
       });

       // Cerrar modal al hacer clic en la X
       $(".close, .btn-cancel").click(function () {
         $("#boletaModal").hide();
       });

       // Cerrar modal al hacer clic fuera del contenido
       $(window).click(function (event) {
         if ($(event.target).is("#boletaModal")) {
           $("#boletaModal").hide();
         }
       });

       // Manejar envío del formulario
       $("#boletaForm").submit(function (e) {
         e.preventDefault();
         var form = this;
         var pagoId = $("#formIdPago").val();

         // Validar observaciones
         if ($("#observaciones").val().trim() === "") {
           Swal.fire({
             title: "¿Desea continuar sin observaciones?",
             text: "Puede dejar el campo de observaciones vacío si lo desea",
             icon: "question",
             showCancelButton: true,
             confirmButtonColor: "#28a745",
             cancelButtonColor: "#6c757d",
             confirmButtonText: "Sí, generar boleta",
             cancelButtonText: "No, volver",
           }).then((result) => {
             if (result.isConfirmed) {
               generarBoleta(form, pagoId);
             }
           });
         } else {
           generarBoleta(form, pagoId);
         }
       });

       function generarBoleta(form, pagoId) {
         $.ajax({
           url: $(form).attr("action"),
           type: "POST",
           data: $(form).serialize(),
           success: function (response) {
             // Actualizar interfaz
             $('tr[data-pago-id="' + pagoId + '"]').remove();

             // Actualizar contador de pagos pendientes
             var pendientes = $("#pagosTable tbody tr").not(
               '[style*="display:none"]'
             ).length;
             if ($("#pagosTable tbody tr td[colspan]").length > 0) {
               pendientes = 0;
             }

             if (pendientes <= 0) {
               $("#pagosTable tbody").html(
                 '<tr><td colspan="6">No hay pagos validados para mostrar</td></tr>'
               );
             }

             // Actualizar historial
             actualizarHistorial();

             // Actualizar ganancias (NUEVO)
             actualizarGanancias();

             // Cerrar modal y limpiar
             $("#boletaModal").hide();
             $("#observaciones").val("");

             // Mostrar mensaje de éxito
             Swal.fire({
               title: "¡Boleta generada!",
               text: "La boleta se ha generado correctamente.",
               icon: "success",
               confirmButtonText: "Aceptar",
             });
           },
           error: function (xhr, status, error) {
             Swal.fire({
               title: "Error",
               text: "Hubo un problema al generar la boleta: " + error,
               icon: "error",
               confirmButtonText: "Aceptar",
             });
           },
         });
       }

       // Función para actualizar ganancias (NUEVA)
       function actualizarGanancias() {
         $.ajax({
           url: "actualizar_ganancias.php",
           type: "GET",
           success: function (data) {
             // Suponiendo que actualizar_ganancias.php devuelve un JSON con {ganancias: "1234.56"}
             if (data.ganancias) {
               // Actualizar el elemento que muestra las ganancias
               // Necesitarías ajustar este selector según tu HTML
               $(".ganancias-value").text(data.ganancias);
             }
           },
           error: function (xhr, status, error) {
             console.error("Error al actualizar ganancias:", error);
           },
         });
       }
       function actualizarHistorial() {
         $.ajax({
           url: "obtener_historial.php",
           type: "GET",
           success: function (data) {
             $("#historialTable tbody").html(data);
             historialCount = $("#historialTable tbody tr").not(
               '[style*="display:none"]'
             ).length;

             // Si hay una fila de "no hay datos", el contador es 0
             if ($("#historialTable tbody tr td[colspan]").length > 0) {
               historialCount = 0;
             }

             $("#historyCount").text(historialCount);

             // Actualizar botón si el panel está visible
             if ($("#historyPanel").is(":visible")) {
               $("#toggleHistory").html(
                 '<i class="fas fa-times"></i> Ocultar Historial <span class="badge">' +
                   historialCount +
                   "</span>"
               );
             }
           },
           error: function (xhr, status, error) {
             console.error("Error al actualizar historial:", error);
           },
         });
       }
     });
});