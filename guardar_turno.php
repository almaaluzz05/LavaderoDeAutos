<?php
require "conexion.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

function volver($estado, $extra = '')
{
    header("Location: index.php?reserva=" . $estado . $extra . "#reserva-estado");
    exit;
}

// ---------- Datos recibidos ----------
$nombre   = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$patente  = strtoupper(preg_replace('/[\s\-]+/', '', trim($_POST['patente'] ?? '')));
$marca    = trim($_POST['marca'] ?? '');
$modelo   = trim($_POST['modelo'] ?? '');
$tipo     = $_POST['tipo'] ?? '';
$servicio = (int)($_POST['servicio'] ?? 0);
$fecha    = $_POST['fecha'] ?? '';
$hora     = $_POST['hora'] ?? '';

$tipos_validos = ['auto', 'camioneta', 'suv', 'utilitario', 'otro'];

// ---------- Validaciones ----------
$f = DateTime::createFromFormat('Y-m-d', $fecha);
$fecha_valida = $f && $f->format('Y-m-d') === $fecha && $fecha >= date('Y-m-d');

if (
    $nombre === ''   || mb_strlen($nombre) > 100 ||
    $telefono === '' || mb_strlen($telefono) > 30 ||
    $patente === ''  || mb_strlen($patente) > 20 ||
    $modelo === ''   || mb_strlen($modelo) > 50 ||
    mb_strlen($marca) > 50 ||
    !in_array($tipo, $tipos_validos, true) ||
    $servicio < 1 ||
    !$fecha_valida ||
    !in_array($hora, $HORAS_TURNO, true)
) {
    volver('datos');
}

if ($marca === '') {
    $marca = null;
}

// ---------- Guardado ----------
try {
    $conn->begin_transaction();

    // El servicio tiene que existir
    $st = $conn->prepare("SELECT id_servicio FROM servicios WHERE id_servicio = ?");
    $st->bind_param("i", $servicio);
    $st->execute();
    if ($st->get_result()->num_rows === 0) {
        $conn->rollback();
        volver('datos');
    }

    // ¿La patente ya está registrada? Reutilizamos vehículo y cliente
    $st = $conn->prepare("SELECT id_vehiculo, id_cliente FROM vehiculos WHERE patente = ?");
    $st->bind_param("s", $patente);
    $st->execute();
    $vehiculo = $st->get_result()->fetch_assoc();

    if ($vehiculo) {
        $id_vehiculo = (int)$vehiculo['id_vehiculo'];
        $id_cliente  = (int)$vehiculo['id_cliente'];
    } else {
        // ¿El cliente ya existe (mismo nombre y teléfono)?
        $st = $conn->prepare("SELECT id_cliente FROM clientes WHERE nombre = ? AND telefono = ? LIMIT 1");
        $st->bind_param("ss", $nombre, $telefono);
        $st->execute();
        $cliente = $st->get_result()->fetch_assoc();

        if ($cliente) {
            $id_cliente = (int)$cliente['id_cliente'];
        } else {
            $st = $conn->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
            $st->bind_param("ss", $nombre, $telefono);
            $st->execute();
            $id_cliente = $conn->insert_id;
        }

        $st = $conn->prepare("INSERT INTO vehiculos (id_cliente, patente, marca, modelo, tipo) VALUES (?, ?, ?, ?, ?)");
        $st->bind_param("issss", $id_cliente, $patente, $marca, $modelo, $tipo);
        $st->execute();
        $id_vehiculo = $conn->insert_id;
    }

    // El turno (si fecha y hora ya están ocupadas, MySQL lo rechaza con el error 1062)
    $st = $conn->prepare("INSERT INTO turnos (id_cliente, id_vehiculo, id_servicio, fecha, hora) VALUES (?, ?, ?, ?, ?)");
    $st->bind_param("iiiss", $id_cliente, $id_vehiculo, $servicio, $fecha, $hora);
    $st->execute();
    $id_turno = $conn->insert_id;

    $conn->commit();
    volver('ok', '&n=' . $id_turno);

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    if ((int)$e->getCode() === 1062) {
        volver('ocupado');
    }
    error_log("Error al guardar turno: " . $e->getMessage());
    volver('error');
}