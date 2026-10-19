<?php
require "conexion.php";
header("Content-Type: application/json; charset=utf-8");

$fecha = $_GET['fecha'] ?? '';
$d = DateTime::createFromFormat('Y-m-d', $fecha);

if (!$d || $d->format('Y-m-d') !== $fecha) {
    echo json_encode([]);
    exit;
}

$st = $conn->prepare("SELECT TIME_FORMAT(hora, '%H:%i') AS h FROM turnos WHERE fecha = ?");
$st->bind_param("s", $fecha);
$st->execute();
$ocupados = array_column($st->get_result()->fetch_all(MYSQLI_ASSOC), 'h');

echo json_encode($ocupados);