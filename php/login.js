document.addEventListener("DOMContentLoaded", function() {
    const logoutBtn = document.getElementById("logoutBtn");

    if (logoutBtn) {
        logoutBtn.addEventListener("click", function(event) {
            event.preventDefault();

            fetch("logout.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                }
            })
            .catch(error => console.error("Error al cerrar sesión:", error));
        });
    } else {
        console.error("No se encontró el botón de cerrar sesión.");
    }
});
