// loginPedidoSuiza.js
// Ahora valida contra el backend (server.js), que consulta la base MySQL "pedidosuiza".

const URL_LOGIN = "../verificar_login.php"; // Está en la raíz del proyecto, como register.php

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const inputUsuario = document.getElementById("usuario");
    const inputPassword = document.getElementById("password");
    const botonIngresar = document.querySelector(".btn-ingresar");

    const mensaje = document.createElement("p");
    mensaje.classList.add("mensaje-login");
    form.appendChild(mensaje);

    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const usuario = inputUsuario.value.trim();
        const password = inputPassword.value.trim();

        if (usuario === "" || password === "") {
            mostrarMensaje("Por favor completá usuario y contraseña.", "error");
            return;
        }

        botonIngresar.disabled = true;
        botonIngresar.textContent = "Ingresando...";

        try {
            const respuesta = await fetch(URL_LOGIN, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ usuario, password }),
            });

            const datos = await respuesta.json();

            if (datos.ok) {
                mostrarMensaje("¡Ingreso exitoso! Redirigiendo...", "exito");
                localStorage.setItem("usuarioLogueado", datos.usuario);

                setTimeout(() => {
                    window.location.href = "inicio.html";
                }, 1000);
            } else {
                mostrarMensaje(datos.mensaje || "Usuario o contraseña incorrectos.", "error");
                botonIngresar.disabled = false;
                botonIngresar.textContent = "Ingresar";
            }

        } catch (error) {
            console.error("Error al conectar con el servidor:", error);
            mostrarMensaje("No se pudo conectar con el servidor. Intentá más tarde.", "error");
            botonIngresar.disabled = false;
            botonIngresar.textContent = "Ingresar";
        }
    });

    function mostrarMensaje(texto, tipo) {
        mensaje.textContent = texto;
        mensaje.classList.remove("error", "exito");
        mensaje.classList.add(tipo);
    }
});