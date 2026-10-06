<?php
require "conexion.php";

$nombre   = trim($_POST['nombre']);
$telefono = trim($_POST['telefono']);
$patente  = strtoupper(trim($_POST['patente']));
$marca    = trim($_POST['marca'] ?? '');
$modelo   = trim($_POST['modelo']);
$tipo     = $_POST['tipo'];
$servicio = (int)$_POST['servicio'];
$fecha    = $_POST['fecha'];
$hora     = $_POST['hora'];

$conn->begin_transaction();
try {
    $st = $conn->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
    $st->bind_param("ss", $nombre, $telefono);
    $st->execute();
    $id_cliente = $conn->insert_id;

    // Si la patente ya existe, reutiliza ese vehículo
    $st = $conn->prepare("SELECT id_vehiculo FROM vehiculos WHERE patente = ?");
    $st->bind_param("s", $patente);
    $st->execute();
    $res = $st->get_result();

    if ($fila = $res->fetch_assoc()) {
        $id_vehiculo = $fila['id_vehiculo'];
    } else {
        $st = $conn->prepare("INSERT INTO vehiculos (id_cliente, patente, marca, modelo, tipo) VALUES (?, ?, ?, ?, ?)");
        $st->bind_param("issss", $id_cliente, $patente, $marca, $modelo, $tipo);
        $st->execute();
        $id_vehiculo = $conn->insert_id;
    }

    $st = $conn->prepare("INSERT INTO turnos (id_cliente, id_vehiculo, id_servicio, fecha, hora) VALUES (?, ?, ?, ?, ?)");
    $st->bind_param("iiiss", $id_cliente, $id_vehiculo, $servicio, $fecha, $hora);
    $st->execute();

    $conn->commit();
    echo "Turno reservado con éxito. <a href='index.php'>Volver</a>";
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    if ($e->getCode() == 1062) {
        echo "Ese horario ya está ocupado. <a href='index.php'>Elegí otro</a>";
    } else {
        echo "Error al guardar el turno.";
    }
}