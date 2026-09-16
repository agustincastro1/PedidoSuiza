document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formLogin');
    if (!formulario) return;

    const mensaje = document.getElementById('mensajeLogin');
    const botonIngresar = formulario.querySelector('.btn-ingresar');

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        const nombreUsuario = document.getElementById('usuario').value.trim();
        const contrasena = document.getElementById('password').value;

        mensaje.textContent = '';

        if (!nombreUsuario || !contrasena) {
            mensaje.textContent = 'Completá todos los campos';
            return;
        }

        botonIngresar.disabled = true;
        botonIngresar.textContent = 'Ingresando...';

        try {
            const respuesta = await fetch('../login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre_usuario: nombreUsuario,
                    contrasena: contrasena
                })
            });

            const resultado = await respuesta.json();

            if (resultado.error) {
                mensaje.textContent = resultado.error;
                botonIngresar.disabled = false;
                botonIngresar.textContent = 'Ingresar';
            } else {
                window.location.href = 'pedidos.php';
            }
        } catch (error) {
            mensaje.textContent = 'Error al conectar con el servidor';
            botonIngresar.disabled = false;
            botonIngresar.textContent = 'Ingresar';
        }
    });
});
