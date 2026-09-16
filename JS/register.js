document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formRegistro');
    const mensaje = document.getElementById('mensajeRegistro');

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const nombreUsuario = document.getElementById('usuario').value.trim();
        const contrasena = document.getElementById('password').value;
        const confirmarContrasena = document.getElementById('confirmarPassword').value;
        const turnos = Array.from(document.querySelectorAll('input[name="turno"]:checked'))
            .map((checkbox) => Number(checkbox.value));

        mensaje.textContent = '';

        if (!nombreUsuario || !contrasena || !confirmarContrasena) {
            mensaje.textContent = 'Completá todos los campos';
            return;
        }

        if (contrasena !== confirmarContrasena) {
            mensaje.textContent = 'Las contraseñas no coinciden';
            return;
        }

        if (turnos.length === 0) {
            mensaje.textContent = 'Elegí al menos un turno';
            return;
        }

        try {
            const respuesta = await fetch('../register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre_usuario: nombreUsuario,
                    contrasena: contrasena,
                    confirmar_contrasena: confirmarContrasena,
                    turnos: turnos
                })
            });

            const resultado = await respuesta.json();

            if (resultado.error) {
                mensaje.textContent = resultado.error;
            } else {
                mensaje.textContent = resultado.exito;
                formulario.reset();
            }
        } catch (error) {
            mensaje.textContent = 'Error al conectar con el servidor';
        }
    });
});
