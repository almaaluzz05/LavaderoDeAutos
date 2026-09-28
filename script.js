let carrito = [];

const botonesCarrito = document.querySelectorAll (".boton-carrito");
const listaCarrito = document.getElementById("lista-carrito");
const totalCarrito = document.getElementById("total-carrito");

botonesCarrito.forEach(boton => {
    boton.addEventListener("Click", () => {

        const nombre = boton.dataset.nombre;
        const precio = Number(boton.dataset.precio);

        carrito.push ({
            nombre: nombre,
            precio: precio
        });

        actualizarcarrito();
    });
});

function actualizarCarrito(){

    listaCarrito.innerHTML = "";

    if (carrito.length === 0) {
        listaCarrito.innerHTML = `
        <p class= "carrito-vacio">
               Todavia no agregaste ningún servicio al carrito
        </p> 
    `;
    totalCarrito.textContent = "$0";

    return;
}
let total = 0;

carrito.forEach((servicio, index) => {
    total += servicio.precio;
    const item = document.createElement("div");
    item.classList.add("item-carrito");

    item.innerHTML = `
            <span>${servicio.nombre}</span>

            <strong>
             $${servicio.precio.toLocaleString("es-AR")}
             </strong>

            <button 
                 class="eliminar-carrito"
                 onclick="eliminarServicio(${index})"
                 >
                     ELIMINAR
            </button>
        `;

    listaCarrito.appendChild(item);
    });

    totalCarrito.textContent =
         "$" + total.toLocaleString("es-AR");
}

function eliminarServicio(index) {
    carrito.splice(index, 1);
    actualizarCarrito()