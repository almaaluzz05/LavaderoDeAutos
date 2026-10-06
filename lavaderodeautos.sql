CREATE DATABASE lavaderodeautos CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE lavaderodeautos;

-- CLIENTES
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL,
    telefono   VARCHAR(30)  NOT NULL
) ENGINE=InnoDB;

-- VEHÍCULOS (la patente no se puede repetir)
CREATE TABLE vehiculos (
    id_vehiculo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente  INT NOT NULL,
    patente     VARCHAR(20) NOT NULL UNIQUE,
    marca       VARCHAR(50) DEFAULT NULL,
    modelo      VARCHAR(50) NOT NULL,
    tipo        ENUM('auto','camioneta','suv','utilitario','otro') NOT NULL DEFAULT 'auto',
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente)
) ENGINE=InnoDB;

-- SERVICIOS
CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)  NOT NULL,
    precio      DECIMAL(10,2) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    duracion    VARCHAR(50)   DEFAULT NULL
) ENGINE=InnoDB;

-- TURNOS (no permite dos turnos en la misma fecha y hora)
CREATE TABLE turnos (
    id_turno    INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente  INT NOT NULL,
    id_vehiculo INT NOT NULL,
    id_servicio INT NOT NULL,
    fecha       DATE NOT NULL,
    hora        TIME NOT NULL,
    estado      ENUM('Pendiente','Confirmado','Cancelado','Realizado') NOT NULL DEFAULT 'Pendiente',
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY turno_unico (fecha, hora),
    FOREIGN KEY (id_cliente)  REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_vehiculo) REFERENCES vehiculos(id_vehiculo),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
) ENGINE=InnoDB;

-- DATOS DE LOS SERVICIOS (mismos precios que tu index.php)
INSERT INTO servicios (nombre, precio, descripcion, duracion) VALUES
('Lavado exterior',      45000.00,  'Lavado completo de carrocería, llantas y vidrios.', '45 minutos'),
('Lavado interior',      50000.00,  'Aspirado y limpieza de superficies interiores.',    '1 hora'),
('Lavado completo',      100000.00, 'Lavado exterior e interior completo.',              '1 hora 30 minutos'),
('Lavado de motor',      80000.00,  'Limpieza cuidadosa del compartimento del motor.',   '1 hora'),
('Limpieza profunda',    95000.00,  'Limpieza intensiva del interior del vehículo.',     '2 horas'),
('Limpieza de tapizados',440000.00, 'Limpieza e higienización de tapizados.',            '1 hora 30 minutos'),
('Pulido y Encerado',    155000.00, 'Tratamiento para mejorar el acabado de la pintura.','3 horas'),
('Detailing',            300000.00, 'Tratamiento premium y limpieza detallada.',        '4 horas');
