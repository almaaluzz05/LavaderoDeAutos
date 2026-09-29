<?php
require "conexion.php";

$nombre   = $_POST['nombre'];
$telefono = $_POST['telefono'];
$patente  = $_POST['patente'];
$marca    = $_POST['marca'];
$modelo   = $_POST['modelo'];
$servicio = (int)$_POST['servicio'];
$fecha    = $_POST['fecha'];
$hora     = $_POST['hora'];

$conn->begin_transaction();
try {
    $st = $conn->prepare("INSERT INTO clientes (nombre, telefono) VALUES (?, ?)");
    $st->bind_param("ss", $nombre, $telefono);
    $st->execute();
    $id_cliente = $conn->insert_id;

    $st = $conn->prepare("INSERT INTO vehiculos (id_cliente, patente, marca, modelo) VALUES (?, ?, ?, ?)");
    $st->bind_param("isss", $id_cliente, $patente, $marca, $modelo);
    $st->execute();
    $id_vehiculo = $conn->insert_id;

    $st = $conn->prepare("INSERT INTO turnos (id_cliente, id_vehiculo, id_servicio, fecha, hora) VALUES (?, ?, ?, ?, ?)");
    $st->bind_param("iiiss", $id_cliente, $id_vehiculo, $servicio, $fecha, $hora);
    $st->execute();

    $conn->commit();
    echo "Turno reservado con éxito";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error al guardar el turno";
}