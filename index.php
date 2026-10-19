<?php
require_once __DIR__ . "/conexion.php";

// Verificar que exista la conexión
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error: no se pudo establecer la conexión con la base de datos.");
}

// Horarios disponibles.
$HORAS_TURNO = [
    "09:00",
    "10:00",
    "11:00",
    "12:00",
    "13:00",
    "14:00",
    "15:00",
    "16:00",
    "17:00",
    "18:00"
];

// Obtener servicios de la base de datos
$sql = "SELECT id_servicio, nombre, precio, descripcion, duracion
        FROM servicios
        ORDER BY id_servicio";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error al consultar los servicios: " . $conn->error);
}

$servicios = $resultado->fetch_all(MYSQLI_ASSOC);

// Imágenes de los servicios
$imagenes = [
    1 => "img/limpieza-exterior.jpg",
    2 => "img/interior.jpg",
    3 => "img/lavado completo.png",
    4 => "img/motor lavado.png",
    5 => "img/limpieza profunda.png",
    6 => "img/tapizados.png",
    7 => "img/pulido y encerado.png",
    8 => "img/detailing.jpg",
];

// Elementos incluidos en algunos servicios
$incluye = [
    3 => [
        "Lavado exterior",
        "Limpieza de llantas",
        "Limpieza de vidrios",
        "Aspirado interior",
        "Limpieza de tablero",
        "Limpieza de alfombras"
    ]
];

// Mensajes de respuesta de la reserva
$estado = $_GET["reserva"] ?? "";
$turno_n = (int)($_GET["n"] ?? 0);

$avisos = [
    "ok" => [
        "exito",
        "¡Turno reservado con éxito!" .
        ($turno_n ? " Tu número de turno es #$turno_n." : "") .
        " Te esperamos."
    ],
    "ocupado" => [
        "error",
        "Ese horario ya fue reservado por otra persona. Elegí otro horario."
    ],
    "datos" => [
        "error",
        "Revisá los datos: falta algún campo o alguno no es válido."
    ],
    "error" => [
        "error",
        "No pudimos guardar tu turno. Intentá de nuevo."
    ]
];

// Funciones auxiliares
function e($t) {
    return htmlspecialchars(
        (string)$t,
        ENT_QUOTES,
        "UTF-8"
    );
}

function ruta($r) {
    return e(str_replace(" ", "%20", $r));
}

function pesos($n) {
    return "$" . number_format((float)$n, 0, ",", ".");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ALMA Detailing | Lavadero de Autos</title>
<meta name="description" content="ALMA Detailing: lavado, limpieza y detailing para tu auto. Reservá tu turno online.">
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- =========================
ENCABEZADO
========================== -->
<header>
<nav id="nav">
<a href="#inicio" class="logo">
<img src="img/Logo.png" alt="Logo ALMA Detailing">
</a>

<button type="button" class="menu-boton" id="menu-boton" aria-label="Abrir menú" aria-expanded="false">☰</button>

<div class="menu" id="menu">
<a href="#inicio">Inicio</a>
<a href="#servicios">Servicios</a>
<a href="#reserva">Reservar turno</a>
<a href="#contacto">Contacto</a>
</div>

<a href="#reserva" class="boton-nav">Reservar</a>
</nav>
</header>

<main>

<!-- =========================
HERO
========================== -->
<section id="inicio" class="hero">
<img src="img/fotoprincipal.jpg" class="foto-fondo" alt="">
<div class="hero-contenido">
<p class="subtitulo">LAVADO · DETAILING · CUIDADO PREMIUM</p>
<h1>TU AUTO,<br><span>COMO NUEVO.</span></h1>
<p class="descripcion">Devolvemos el brillo que tu auto merece.</p>
<a href="#reserva" class="boton-principal">RESERVAR TURNO →</a>
</div>
</section>

<!-- =========================
INTRODUCCIÓN
========================== -->
<section class="introduccion">
<div class="contenedor">
<div>
<p class="subtitulo">CUIDAMOS CADA DETALLE</p>
<h2>Más que un lavado.<br><span>Una experiencia.</span></h2>
</div>
<p>
Trabajamos cada vehículo con dedicación y productos pensados para devolverle
su mejor aspecto. Elegí el servicio que necesita tu auto y dejalo en nuestras manos.
</p>
</div>
</section>

<!-- =========================
FORMULARIO ÚNICO DE RESERVA
========================== -->
<form id="form-reserva" method="POST" action="guardar_turno.php">

<!-- PASO 1: DATOS DEL CLIENTE -->
<section id="reserva" class="seccion reserva">
<div class="contenedor">

<?php if (isset($avisos[$estado])): ?>
<div id="reserva-estado" class="aviso <?= $avisos[$estado][0] ?>" role="alert">
<?= e($avisos[$estado][1]) ?>
</div>
<?php endif; ?>

<div class="titulo-seccion">
<p class="subtitulo">PRIMER PASO</p>
<h2>Reservá tu turno</h2>
<p>Completá tus datos para preparar tu reserva.</p>
</div>

<div class="paso">
<div class="numero-paso">01</div>
<div class="contenido-paso">
<h3>Datos del cliente</h3>

<div class="formulario-grid">
<label>
Nombre y apellido
<input type="text" name="nombre" placeholder="Ej: Lucas Diaz" maxlength="100" required>
</label>

<label>
Número de teléfono
<input type="tel" name="telefono" placeholder="Ej. 11 1234-5678" maxlength="30" required>
</label>

<label>
Patente
<input type="text" name="patente" placeholder="Ej. AA 123 AA" maxlength="20" required>
</label>
</div>

<a href="#vehiculo" class="boton-principal">CONTINUAR →</a>
</div>
</div>

</div>
</section>

<!-- PASO 2: DATOS DEL VEHÍCULO -->
<section id="vehiculo" class="seccion vehiculo">
<div class="contenedor">

<div class="titulo-seccion">
<p class="subtitulo">SEGUNDO PASO</p>
<h2>Datos del vehículo</h2>
<p>Contanos un poco más sobre el vehículo que vamos a cuidar.</p>
</div>

<div class="paso">
<div class="numero-paso">02</div>
<div class="contenido-paso">
<h3>Información del vehículo</h3>

<div class="formulario-grid">
<label>
Marca <small>(opcional)</small>
<input type="text" name="marca" placeholder="Ej. Toyota" maxlength="50">
</label>

<label>
Modelo
<input type="text" name="modelo" placeholder="Ej. Corolla" maxlength="50" required>
</label>

<label>
Tipo de vehículo
<select name="tipo" required>
<option value="">Seleccionar</option>
<option value="auto">Auto</option>
<option value="camioneta">Camioneta</option>
<option value="suv">SUV</option>
<option value="utilitario">Utilitario</option>
<option value="otro">Otro</option>
</select>
</label>
</div>

<a href="#servicios" class="boton-principal">CONTINUAR →</a>
</div>
</div>
</div>
</section>

<!-- SERVICIOS -->
<section id="servicios" class="seccion servicios">
<div class="contenedor">

<div class="titulo-seccion centrado">
<p class="subtitulo">CALIDAD EN CADA SERVICIO</p>
<h2>NUESTROS SERVICIOS</h2>
<p>Elegí el cuidado ideal para que tu vehículo vuelva a brillar.</p>
</div>

<div class="servicios-grid">
<?php foreach ($servicios as $s):
    $id  = (int)$s['id_servicio'];
    $img = $imagenes[$id] ?? "img/fotoprincipal.jpg";
    $inc = $incluye[$id] ?? [];
    $destacado = ($id === 3);
?>
<article class="tarjeta-servicio<?= $destacado ? ' destacado' : '' ?>"
    data-id="<?= $id ?>"
    data-nombre="<?= e($s['nombre']) ?>"
    data-precio="<?= (int)$s['precio'] ?>"
    data-duracion="<?= e($s['duracion']) ?>"
    data-desc="<?= e($s['descripcion']) ?>"
    data-img="<?= ruta($img) ?>"
    data-incluye="<?= e(json_encode($inc, JSON_UNESCAPED_UNICODE)) ?>">

<div class="imagen-servicio">
<img src="<?= ruta($img) ?>" alt="<?= e($s['nombre']) ?>" loading="lazy">
<?php if ($destacado): ?><b class="insignia">RECOMENDADO</b><?php endif; ?>
</div>
<div class="contenido-servicio">
<h3><?= e($s['nombre']) ?></h3>
<p><?= e($s['descripcion']) ?></p>
<strong class="precio-servicio"><?= pesos($s['precio']) ?></strong>

 <div class="botones-servicio">
<button type="button" class="boton-secundario boton-ver">VER SERVICIO</button>
<button type="button" class="boton-carrito boton-elegir">ELEGIR SERVICIO</button>
 </div>
</div>
</article>
<?php endforeach; ?>
</div>

<div class="carrito">
<div class="carrito-titulo">
<p class="subtitulo">TU SELECCIÓN</p>
<h2>Servicio elegido</h2>
</div>

<div id="lista-carrito">
<p class="carrito-vacio">Todavía no elegiste ningún servicio</p>
</div>

<div class="carrito-total">
<span>Total</span>
<strong id="total-carrito">$0</strong>
</div>
</div>

</div>
</section>

<!-- PASO 3: TURNO -->
<section id="horarios" class="seccion horarios">
<div class="contenedor">

<div class="titulo-seccion centrado">
<p class="subtitulo">TERCER PASO</p>
<h2>Elegí tu turno</h2>
<p>Seleccioná una fecha, un horario y el servicio.</p>
</div>

<div class="formulario-turno">

<label>
Fecha
<input type="date" name="fecha" id="fecha" min="<?= date('Y-m-d') ?>" required>
</label>

<label>
Servicio seleccionado
<select name="servicio" id="servicio" required>
<option value="">Seleccionar servicio</option>
<?php foreach ($servicios as $s): ?>
<option value="<?= (int)$s['id_servicio'] ?>"><?= e($s['nombre']) ?></option>
<?php endforeach; ?>
</select>
</label>

<div class="horarios-disponibles">
<p>Horarios disponibles</p>
<div id="horas" class="horas-grid" data-horas="<?= e(json_encode($HORAS_TURNO)) ?>">
<span class="aviso-horarios">Elegí una fecha para ver los horarios disponibles.</span>
</div>
<input type="hidden" name="hora" id="hora">
</div>

</div>
</div>
</section>

<!-- CONFIRMACIÓN -->
<section id="confirmacion" class="seccion confirmacion">
<div class="contenedor">
<div class="tarjeta-confirmacion">

<div class="encabezado-confirmacion">
<div>
<p class="subtitulo">ÚLTIMO PASO</p>
<h2>CONFIRMÁ TU TURNO</h2>
</div>
<span>RESERVA PREVIA</span>
</div>

<div class="resumen">
<div><span>Cliente</span><strong id="res-cliente">—</strong></div>
<div><span>Teléfono</span><strong id="res-telefono">—</strong></div>
<div><span>Vehículo</span><strong id="res-vehiculo">—</strong></div>
<div><span>Patente</span><strong id="res-patente">—</strong></div>
<div><span>Servicio</span><strong id="res-servicio">—</strong></div>
<div><span>Fecha</span><strong id="res-fecha">—</strong></div>
<div><span>Hora</span><strong id="res-hora">—</strong></div>
<div><span>Precio</span><strong id="res-precio">—</strong></div>
</div>

<div id="aviso-form" class="aviso error" role="alert" hidden></div>

<div class="acciones-confirmacion">
<button type="submit" class="boton-principal" id="boton-confirmar">CONFIRMAR TURNO</button>
<a href="#reserva" class="boton-secundario">MODIFICAR DATOS</a>
</div>

</div>
</div>
</section>

</form>

</main>

<!-- =========================
DETALLE DE SERVICIO (ventana)
========================== -->
<dialog id="detalle">
<button type="button" class="cerrar" id="detalle-cerrar" aria-label="Cerrar">×</button>
<div class="detalle-grid">
<img id="detalle-img" src="" alt="">
<div class="detalle-contenido">
<p class="subtitulo" id="detalle-num"></p>
<h2 id="detalle-nombre"></h2>
<strong class="precio" id="detalle-precio"></strong>
<p id="detalle-desc"></p>
<div id="detalle-incluye-wrap">
<h3>Incluye</h3>
<ul id="detalle-incluye"></ul>
</div>
<p><strong>Duración aproximada:</strong> <span id="detalle-duracion"></span></p>
<button type="button" class="boton-principal" id="detalle-reservar">RESERVAR ESTE SERVICIO →</button>
</div>
</div>
</dialog>

<!-- =========================
FOOTER
========================== -->
<footer id="contacto">
<div class="contenedor footer-grid">

<div>
<div class="logo-texto">
<span>ALMA</span>
<small>DETAILING</small>
</div>
<p>Lavado, limpieza y detailing para tu auto.</p>
</div>

<div>
<h4>Turnos</h4>
<p>Reservá online de <?= e($HORAS_TURNO[0]) ?> a <?= e(end($HORAS_TURNO)) ?> h.</p>
<a href="#reserva" class="boton-secundario">RESERVAR TURNO</a>
</div>

</div>
<p class="copy">© <?= date('Y') ?> ALMA Detailing. Todos los derechos reservados.</p>
</footer>

<script src="script.js"></script>
</body>
</html>