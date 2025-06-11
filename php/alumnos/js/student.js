  $(document).ready(function() {
                // Variables globales
                let currentStudentId = null;
                let studentPayments = [];
                
                // Abrir modal de validación
                $('.btn-validate').click(function() {
                    currentStudentId = $(this).data('id');
                    
                    // Llenar información del estudiante
                    $('#modalNombre').text($(this).data('nombre'));
                    $('#modalContacto').text($(this).data('contacto'));
                    $('#modalGrado').text($(this).data('grado'));
                    $('#modalFecha').text($(this).data('fecha'));
                    $('#modalPagos').text('S/ ' + $(this).data('pagos'));
                    
                    // Limpiar tabla de pagos
                    $('#modalPagosBody').empty();
                    
                    // Obtener pagos del estudiante via AJAX
                    $.ajax({
                        url: 'obtener_pagos.php',
                        method: 'POST',
                        data: { id_estudiante: currentStudentId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                studentPayments = response.pagos;
                                let total = 0;
                                
                                // Llenar tabla de pagos
                                response.pagos.forEach(function(pago) {
                                    $('#modalPagosBody').append(`
                                        <tr>
                                            <td>${formatDate(pago.fecha_pago)}</td>
                                            <td>${pago.concepto}</td>
                                            <td>${pago.metodo_pago}</td>
                                            <td>S/ ${parseFloat(pago.monto).toFixed(2)}</td>
                                            <td>${pago.estado}</td>
                                        </tr>
                                    `);
                                    total += parseFloat(pago.monto);
                                });
                                
                                $('#modalTotalPagos').text('S/ ' + total.toFixed(2));
                            } else {
                                alert('Error al obtener los pagos: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error al conectar con el servidor');
                        }
                    });
                    
                    // Mostrar modal
                    $('#reportModal').show();
                });
                
                // Cerrar modales
                $('.close, .btn-cancel').click(function() {
                    $('#reportModal').hide();
                });
                
                $('.btn-close-boleta').click(function() {
                    $('#boletaModal').hide();
                });
                
                // Confirmar validación
                $('#confirmarReporte').click(function() {
                    const observaciones = $('#observaciones').val();
                    
                    $.ajax({
                        url: 'report.php',
                        method: 'POST',
                        data: { 
                            confirmar_reporte: true,
                            id_estudiante: currentStudentId,
                            observaciones: observaciones
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                // Mostrar boleta con los datos
                                $('#boletaNumero').text(response.numero_boleta);
                                $('#boletaAlumno').text($('#modalNombre').text());
                                $('#boletaGrado').text($('#modalGrado').text());
                                $('#boletaObservaciones').text(observaciones);
                                
                                // Llenar detalles de la boleta
                                $('#boletaDetalleBody').empty();
                                let total = 0;
                                
                                studentPayments.forEach(function(pago) {
                                    $('#boletaDetalleBody').append(`
                                        <tr>
                                            <td>${pago.concepto}</td>
                                            <td>${formatDate(pago.fecha_pago)}</td>
                                            <td>${pago.metodo_pago}</td>
                                            <td>S/ ${parseFloat(pago.monto).toFixed(2)}</td>
                                        </tr>
                                    `);
                                    total += parseFloat(pago.monto);
                                });
                                
                                $('#boletaTotal').text('S/ ' + total.toFixed(2));
                                
                                // Cerrar modal de validación y abrir boleta
                                $('#reportModal').hide();
                                $('#boletaModal').show();
                            } else {
                                alert('Error al generar la boleta: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('Error al conectar con el servidor');
                        }
                    });
                });
                
                // Función para formatear fecha
                function formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('es-PE', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                }
            });