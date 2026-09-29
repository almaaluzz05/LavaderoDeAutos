<?php
$conn = new mysqli("localhost", "root", "", "lavaderodeautos");
if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");