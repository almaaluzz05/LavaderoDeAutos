document.addEventListener("DOMContentLoaded", () => {
    const $  = (s, c = document) => c.querySelector(s);
    const $$ = (s, c = document) => [...c.querySelectorAll(s)];
    const pesos = (n) => "$" + Number(n).toLocaleString("es-AR");

    const form = $("#form-reserva");

    // Después de reservar, limpiamos la URL para que al refrescar no se repita el aviso
    if (location.search.includes("reserva=")) {
        history.replaceState(null, "", location.pathname + location.hash);
    }

    // ---------- Menú en celulares ----------
    const menuBoton = $("#menu-boton");
    const menu = $("#menu");
    menuBoton.addEventListener("click", () => {
        const abierto = menu.classList.toggle("abierto");
        menuBoton.setAttribute("aria-expanded", abierto);
    });
    $$("a", menu).forEach((a) => a.addEventListener("click", () => {
        menu.classList.remove("abierto");
        menuBoton.setAttribute("aria-expanded", "false");
    }));

    // ---------- Servicios ----------
    const tarjetas = $$(".tarjeta-servicio");
    const selectServicio = $("#servicio");
    const listaCarrito = $("#lista-carrito");
    const totalCarrito = $("#total-carrito");

    const servicios = {};
    tarjetas.forEach((t) => {
        servicios[t.dataset.id] = {
            id: t.dataset.id,
            nombre: t.dataset.nombre,
            precio: Number(t.dataset.precio),
            duracion: t.dataset.duracion,
            desc: t.dataset.desc,
            img: t.dataset.img,
            incluye: JSON.parse(t.dataset.incluye || "[]"),
        };
    });

    function elegirServicio(id) {
        selectServicio.value = id || "";
        const s = servicios[id];

        tarjetas.forEach((t) => {
            const elegido = t.dataset.id === String(id);
            t.classList.toggle("elegido", elegido);
            $(".boton-elegir", t).textContent = elegido ? "ELEGIDO ✓" : "ELEGIR SERVICIO";
        });

        if (s) {
            listaCarrito.innerHTML = "";
            const item = document.createElement("div");
            item.className = "carrito-item";
            const nombre = document.createElement("span");
            nombre.textContent = s.nombre + " — " + pesos(s.precio);
            const quitar = document.createElement("button");
            quitar.type = "button";
            quitar.textContent = "Quitar";
            quitar.addEventListener("click", () => elegirServicio(""));
            item.append(nombre, quitar);
            listaCarrito.appendChild(item);
            totalCarrito.textContent = pesos(s.precio);
        } else {
            listaCarrito.innerHTML = '<p class="carrito-vacio">Todavía no elegiste ningún servicio</p>';
            totalCarrito.textContent = "$0";
        }
        actualizarResumen();
    }

    $$(".boton-elegir").forEach((b) => b.addEventListener("click", () => {
        elegirServicio(b.closest(".tarjeta-servicio").dataset.id);
        $(".carrito").scrollIntoView({ behavior: "smooth", block: "center" });
    }));
    selectServicio.addEventListener("change", () => elegirServicio(selectServicio.value));

    // ---------- Ventana de detalle ----------
    const dialogo = $("#detalle");
    let servicioDetalle = null;

    $$(".boton-ver").forEach((b) => b.addEventListener("click", () => {
        const s = servicios[b.closest(".tarjeta-servicio").dataset.id];
        servicioDetalle = s;
        $("#detalle-img").src = s.img;
        $("#detalle-img").alt = s.nombre;
        $("#detalle-num").textContent = "SERVICIO " + String(s.id).padStart(2, "0");
        $("#detalle-nombre").textContent = s.nombre;
        $("#detalle-precio").textContent = pesos(s.precio);
        $("#detalle-desc").textContent = s.desc;
        $("#detalle-duracion").textContent = s.duracion || "A coordinar";

        const ul = $("#detalle-incluye");
        ul.innerHTML = "";
        s.incluye.forEach((i) => {
            const li = document.createElement("li");
            li.textContent = i;
            ul.appendChild(li);
        });
        $("#detalle-incluye-wrap").hidden = s.incluye.length === 0;
        dialogo.showModal();
    }));

    $("#detalle-cerrar").addEventListener("click", () => dialogo.close());
    dialogo.addEventListener("click", (e) => { if (e.target === dialogo) dialogo.close(); });
    $("#detalle-reservar").addEventListener("click", () => {
        elegirServicio(servicioDetalle.id);
        dialogo.close();
        $("#reserva").scrollIntoView({ behavior: "smooth" });
    });

    // ---------- Fecha y horarios ----------
    const inputFecha = $("#fecha");
    const inputHora = $("#hora");
    const contenedorHoras = $("#horas");
    const horas = JSON.parse(contenedorHoras.dataset.horas);

    function fechaLocalHoy() {
        const d = new Date();
        const mes = String(d.getMonth() + 1).padStart(2, "0");
        const dia = String(d.getDate()).padStart(2, "0");
        return `${d.getFullYear()}-${mes}-${dia}`;
    }

    async function cargarHorarios() {
        inputHora.value = "";
        const fecha = inputFecha.value;

        if (!fecha) {
            contenedorHoras.innerHTML = '<span class="aviso-horarios">Elegí una fecha para ver los horarios disponibles.</span>';
            actualizarResumen();
            return;
        }

        let ocupados = [];
        try {
            const r = await fetch("horarios.php?fecha=" + encodeURIComponent(fecha));
            ocupados = await r.json();
        } catch (e) {
            ocupados = [];
        }

        const ahora = new Date();
        const hoy = fecha === fechaLocalHoy();
        contenedorHoras.innerHTML = "";

        horas.forEach((h) => {
            const b = document.createElement("button");
            b.type = "button";
            b.className = "hora";
            b.textContent = h;

            const [hh, mm] = h.split(":").map(Number);
            const pasada = hoy && (hh < ahora.getHours() || (hh === ahora.getHours() && mm <= ahora.getMinutes()));

            if (ocupados.includes(h) || pasada) {
                b.disabled = true;
                b.title = "No disponible";
            } else {
                b.addEventListener("click", () => {
                    inputHora.value = h;
                    $$(".hora", contenedorHoras).forEach((x) => x.classList.toggle("elegida", x === b));
                    actualizarResumen();
                });
            }
            contenedorHoras.appendChild(b);
        });
        actualizarResumen();
    }
    inputFecha.addEventListener("change", cargarHorarios);

    // ---------- Resumen del turno ----------
    const val = (name) => (form.elements[name] ? form.elements[name].value.trim() : "");
    const poner = (id, texto) => { $("#" + id).textContent = texto || "—"; };

    function actualizarResumen() {
        poner("res-cliente", val("nombre"));
        poner("res-telefono", val("telefono"));
        poner("res-vehiculo", [val("marca"), val("modelo")].filter(Boolean).join(" "));
        poner("res-patente", val("patente").toUpperCase());

        const s = servicios[selectServicio.value];
        poner("res-servicio", s ? s.nombre : "");
        poner("res-precio", s ? pesos(s.precio) : "");

        const f = inputFecha.value;
        poner("res-fecha", f ? f.split("-").reverse().join("/") : "");
        poner("res-hora", inputHora.value);
    }
    form.addEventListener("input", actualizarResumen);
    form.addEventListener("change", actualizarResumen);

    // ---------- Validación antes de enviar ----------
    const aviso = $("#aviso-form");
    const botonConfirmar = $("#boton-confirmar");

    function mostrarAviso(texto, destino) {
        aviso.textContent = texto;
        aviso.hidden = false;
        destino.scrollIntoView({ behavior: "smooth", block: "center" });
    }

    form.addEventListener("submit", (e) => {
        aviso.hidden = true;
        if (!selectServicio.value) {
            e.preventDefault();
            mostrarAviso("Elegí un servicio para continuar.", $("#servicios"));
            return;
        }
        if (!inputHora.value) {
            e.preventDefault();
            mostrarAviso("Elegí un horario disponible para tu turno.", $("#horarios"));
            return;
        }
        botonConfirmar.disabled = true;
        botonConfirmar.textContent = "ENVIANDO…";
    });

    // Si el usuario vuelve atrás con el navegador, reactivamos el botón
    window.addEventListener("pageshow", () => {
        botonConfirmar.disabled = false;
        botonConfirmar.textContent = "CONFIRMAR TURNO";
    });

    actualizarResumen();
});