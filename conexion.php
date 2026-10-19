<?php
// =====================================================
// CONEXIÓN A LA BASE DE DATOS (XAMPP por defecto)
// =====================================================
$servidor      = "localhost";
$usuario       = "root";
$password      = "";
$base_de_datos = "lavaderodeautos";

// Horarios que se pueden reservar (los usan la página y el guardado)
$HORAS_TURNO = ["09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servidor, $usuario, $password, $base_de_datos);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die("No se pudo conectar con la base de datos. Revisá que MySQL esté encendido en XAMPP y que hayas importado lavaderodeautos.sql.");
}