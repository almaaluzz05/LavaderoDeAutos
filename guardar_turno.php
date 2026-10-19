<?php
require_once __DIR__ . "/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

function volver($estado, $extra = "")
{
    header(
        "Location: index.php?reserva=" .
        urlencode($estado) . $extra . "#reserva-estado"
    );
    exit;
}

// Verificar la conexión
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("No existe una conexión mysqli válida.");
    volver("error");
}

// Horarios permitidos
// Deben coincidir con los definidos en index.php
$HORAS_TURNO = [
    "09:00", "10:00", "11:00", "12:00",
    "14:00", "15:00", "16:00", "17:00"
];

// Datos del formulario
$nombre   = trim($_POST["nombre"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");
$patente  = strtoupper(
    preg_replace('/[\s\-]+/', '', trim($_POST["patente"] ?? ""))
);
$marca    = trim($_POST["marca"] ?? "");
$modelo   = trim($_POST["modelo"] ?? "");
$tipo     = $_POST["tipo"] ?? "";
$servicio = filter_var(
    $_POST["servicio"] ?? null,
    FILTER_VALIDATE_INT
);
$fecha    = $_POST["fecha"] ?? "";
$hora     = $_POST["hora"] ?? "";

$tipos_validos = [
    "auto",
    "camioneta",
    "suv",
    "utilitario",
    "otro"
];

// Validar fecha
$f = DateTime::createFromFormat("!Y-m-d", $fecha);

$fecha_valida = $f !== false
    && $f->format("Y-m-d") === $fecha
    && $fecha >= date("Y-m-d");

// Validar campos
if (
    $nombre === "" ||
    mb_strlen($nombre) > 100 ||
    $telefono === "" ||
    mb_strlen($telefono) > 30 ||
    $patente === "" ||
    mb_strlen($patente) > 20 ||
    $modelo === "" ||
    mb_strlen($modelo) > 50 ||
    mb_strlen($marca) > 50 ||
    !in_array($tipo, $tipos_validos, true) ||
    $servicio === false ||
    $servicio === null ||
    $servicio < 1 ||
    !$fecha_valida ||
    !in_array($hora, $HORAS_TURNO, true)
) {
    volver("datos");
}

// La marca puede ser opcional
if ($marca === "") {
    $marca = null;
}

$transaccion_abierta = false;

try {
    $conn->begin_transaction();
    $transaccion_abierta = true;

    // Comprobar que el servicio exista
    $st = $conn->prepare(
        "SELECT id_servicio
         FROM servicios
         WHERE id_servicio = ?"
    );
    $st->bind_param("i", $servicio);
    $st->execute();

    if (!$st->get_result()->fetch_assoc()) {
        volver("datos");
    }

    // Buscar vehículo por patente
    $st = $conn->prepare(
        "SELECT id_vehiculo, id_cliente
         FROM vehiculos
         WHERE patente = ?
         LIMIT 1"
    );
    $st->bind_param("s", $patente);
    $st->execute();

    $vehiculo = $st->get_result()->fetch_assoc();

    if ($vehiculo) {
        $id_vehiculo = (int)$vehiculo["id_vehiculo"];
        $id_cliente = (int)$vehiculo["id_cliente"];

    } else {
        // Buscar cliente existente
        $st = $conn->prepare(
            "SELECT id_cliente
             FROM clientes
             WHERE nombre = ? AND telefono = ?
             LIMIT 1"
        );
        $st->bind_param("ss", $nombre, $telefono);
        $st->execute();

        $cliente = $st->get_result()->fetch_assoc();

        if ($cliente) {
            $id_cliente = (int)$cliente["id_cliente"];

        } else {
            // Crear cliente
            $st = $conn->prepare(
                "INSERT INTO clientes (nombre, telefono)
                 VALUES (?, ?)"
            );
            $st->bind_param("ss", $nombre, $telefono);
            $st->execute();

            $id_cliente = $conn->insert_id;
        }

        // Crear vehículo
        $st = $conn->prepare(
            "INSERT INTO vehiculos
             (id_cliente, patente, marca, modelo, tipo)
             VALUES (?, ?, ?, ?, ?)"
        );
        $st->bind_param(
            "issss",
            $id_cliente,
            $patente,
            $marca,
            $modelo,
            $tipo
        );
        $st->execute();

        $id_vehiculo = $conn->insert_id;
    }

    // Registrar turno.
    // La base de datos debe tener una restricción UNIQUE
    // para impedir reservas duplicadas del mismo horario.
    $st = $conn->prepare(
        "INSERT INTO turnos
         (id_cliente, id_vehiculo, id_servicio, fecha, hora)
         VALUES (?, ?, ?, ?, ?)"
    );
    $st->bind_param(
        "iiiss",
        $id_cliente,
        $id_vehiculo,
        $servicio,
        $fecha,
        $hora
    );
    $st->execute();

    $id_turno = $conn->insert_id;

    $conn->commit();
    $transaccion_abierta = false;

    volver("ok", "&n=" . $id_turno);

} catch (mysqli_sql_exception $e) {

    if ($transaccion_abierta) {
        $conn->rollback();
    }

    if ((int)$e->getCode() === 1062) {
        volver("ocupado");
    }

    error_log("Error al guardar turno: " . $e->getMessage());
    volver("error");

} catch (Throwable $e) {

    if ($transaccion_abierta) {
        $conn->rollback();
    }

    error_log("Error inesperado al guardar turno: " . $e->getMessage());
    volver("error");
}