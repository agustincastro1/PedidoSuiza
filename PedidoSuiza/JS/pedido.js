document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.getElementById('formPedido');
    if (!formulario) return;

    const mensaje = document.getElementById('mensajePedido');

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();
        mensaje.textContent = '';

        const productos = Array.from(document.querySelectorAll('.cantidad-producto'))
            .map((input) => ({
                id_producto: Number(input.dataset.idProducto),
                cantidad: Number(input.value)
            }))
            .filter((item) => item.cantidad > 0);

        if (productos.length === 0) {
            mensaje.textContent = 'Elegí al menos un producto';
            return;
        }

        try {
            const respuesta = await fetch('../hacer_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ productos })
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
    });
});
