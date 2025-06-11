document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const estudiantesElement = document.getElementById('total-estudiantes');
    const gananciasElement = document.getElementById('total-ganancias');
    const logoutBtn = document.getElementById('logoutBtn');

    // Función principal para actualizar el dashboard
    function actualizarDashboard() {
        fetch('get_dashboard_data.php?nocache=' + new Date().getTime())
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Actualizar contadores
                if (data.estudiantes !== undefined) {
                    animateCounter('total-estudiantes', data.estudiantes);
                }
                
                if (data.ganancias !== undefined) {
                    gananciasElement.textContent = data.ganancias;
                }
            })
            .catch(error => {
                console.error('Error al actualizar dashboard:', error);
                // Opcional: Mostrar mensaje de error al usuario
                mostrarError('Error al cargar datos. Intentando nuevamente...');
            });
    }

    // Función para animar contador numérico
    function animateCounter(elementId, target) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const current = parseInt(element.textContent) || 0;
        const increment = target > current ? 1 : -1;
        const duration = 1000; // 1 segundo de duración
        const stepTime = Math.max(20, Math.min(100, Math.abs(Math.floor(duration / (target - current)))));

        if (current === target) return;

        const timer = setInterval(() => {
            const newValue = parseInt(element.textContent) + increment;
            element.textContent = newValue;
            
            if ((increment > 0 && newValue >= target) || 
                (increment < 0 && newValue <= target)) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, stepTime);
    }

    // Función para mostrar errores (opcional)
    function mostrarError(mensaje) {
        // Puedes implementar un sistema de notificaciones aquí
        console.error(mensaje);
    }

    // Configurar actualización periódica cada 30 segundos
    const intervaloActualizacion = 30000;
    let intervalo = setInterval(actualizarDashboard, intervaloActualizacion);

    // Cargar datos inmediatamente al cargar la página
    actualizarDashboard();

    // Manejar el logout
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Mostrar confirmación
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                return;
            }

            fetch("logout.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: "action=logout"
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect || 'login.php';
                } else {
                    throw new Error(data.message || 'Error al cerrar sesión');
                }
            })
            .catch(error => {
                console.error("Error al cerrar sesión:", error);
                mostrarError('Error al cerrar sesión. Por favor intenta nuevamente.');
            });
        });
    }

    // Limpiar intervalo cuando la página no está visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(intervalo);
        } else {
            intervalo = setInterval(actualizarDashboard, intervaloActualizacion);
            actualizarDashboard(); // Actualizar inmediatamente al volver
        }
    });
});