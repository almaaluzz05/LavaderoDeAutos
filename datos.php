<?php
require "conexion.php";

$sql = "SELECT c.id_cliente, c.nombre, c.telefono,
               v.patente, v.marca, v.modelo, v.tipo
        FROM clientes c
        LEFT JOIN vehiculos v ON v.id_cliente = c.id_cliente
        ORDER BY c.id_cliente DESC";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Datos de clientes y vehículos | ALMA Detailing</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
h1 { font-size: 22px; }
.tabla-scroll { overflow-x: auto; }
table { border-collapse: collapse; width: 100%; background: #fff; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; white-space: nowrap; }
th { background: #222; color: #fff; }
</style>
</head>
<body>

<h1>Datos del cliente y del vehículo</h1>

<div class="tabla-scroll">
<table>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Teléfono</th>
<th>Patente</th>
<th>Marca</th>
<th>Modelo</th>
<th>Tipo</th>
</tr>

<?php if ($resultado && $resultado->num_rows > 0): ?>
<?php while ($fila = $resultado->fetch_assoc()): ?>
<tr>
<td><?php echo (int)$fila['id_cliente']; ?></td>
<td><?php echo htmlspecialchars($fila['nombre']); ?></td>
<td><?php echo htmlspecialchars($fila['telefono']); ?></td>
<td><?php echo htmlspecialchars($fila['patente'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($fila['marca'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($fila['modelo'] ?? '-'); ?></td>
<td><?php echo htmlspecialchars($fila['tipo'] ?? '-'); ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="7">Todavía no hay clientes registrados.</td></tr>
<?php endif; ?>

</table>
</div>

<p><a href="index.php">← Volver al inicio</a></p>

</body>
</html>