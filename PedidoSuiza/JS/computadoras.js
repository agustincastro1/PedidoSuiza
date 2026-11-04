document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formComputadoras');
    if (!formulario) return;

    const mensaje = document.getElementById('mensajeComputadoras');

    const enviar = async (url, datos) => {
        mensaje.textContent = '';

        try {
            const respuesta = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });

            const resultado = await respuesta.json();

            if (resultado.error) {
                mensaje.textContent = resultado.error;
            } else {
                window.location.reload();
            }
        } catch (error) {
            mensaje.textContent = 'Error al conectar con el servidor';
        }
    };

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();

        const cantidad = Number(document.getElementById('cantidadComputadoras').value);
        const aula = document.getElementById('aulaComputadoras').value.trim();
        const horario = document.getElementById('horarioComputadoras').value;

        if (cantidad <= 0) {
            mensaje.textContent = 'Elegí cuántas computadoras querés reservar';
            return;
        }

        if (!aula) {
            mensaje.textContent = 'Escribí el aula';
            return;
        }

        if (!horario) {
            mensaje.textContent = 'Elegí la fecha y la hora';
            return;
        }

        enviar('../reservar_computadoras.php', { cantidad, aula, horario });
    });

    document.querySelectorAll('.btn-devolver').forEach((boton) => {
        boton.addEventListener('click', () => {
            enviar('../devolver_computadoras.php', { id_reserva: Number(boton.dataset.idReserva) });
        });
    });
});
