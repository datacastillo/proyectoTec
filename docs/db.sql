CREATE DATABASE IF NOT EXISTS control_escolar;
USE control_escolar;


CREATE TABLE carreras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    abreviatura VARCHAR(10),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    clave VARCHAR(20) UNIQUE NOT NULL
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'docente', 'alumno') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_login DATETIME
) ENGINE=InnoDB;

CREATE TABLE alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    carrera_id INT NOT NULL,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    fecha_ingreso DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (carrera_id) REFERENCES carreras(id)
) ENGINE=InnoDB;

CREATE TABLE docentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    numero_empleado VARCHAR(20) UNIQUE NOT NULL,
    especialidad VARCHAR(100),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;


CREATE TABLE grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia_id INT NOT NULL,
    docente_id INT NOT NULL,
    nombre_grupo VARCHAR(20) NOT NULL, 
    semestre INT NOT NULL,
    ciclo_escolar VARCHAR(20) NOT NULL,
    FOREIGN KEY (materia_id) REFERENCES materias(id),
    FOREIGN KEY (docente_id) REFERENCES docentes(id)
) ENGINE=InnoDB;

CREATE TABLE inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    grupo_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id),
    FOREIGN KEY (grupo_id) REFERENCES grupos(id)
) ENGINE=InnoDB;


CREATE TABLE unidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    numero_unit INT NOT NULL, 
    nombre_unidad VARCHAR(100),
    ponderacion DECIMAL(5,2) NOT NULL, 
    FOREIGN KEY (grupo_id) REFERENCES grupos(id)
) ENGINE=InnoDB;

CREATE TABLE tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unidad_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    archivo_docente VARCHAR(255), 
    puntos_maximos INT NOT NULL, 
    fecha_entrega_limite DATETIME,
    FOREIGN KEY (unidad_id) REFERENCES unidades(id)
) ENGINE=InnoDB;

CREATE TABLE entregas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarea_id INT NOT NULL,
    alumno_id INT NOT NULL,
    archivo_alumno VARCHAR(255), 
    puntos_obtenidos DECIMAL(5,2) DEFAULT 0.00, 
    comentario_docente TEXT,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    estatus ENUM('pendiente', 'entregado', 'calificado') DEFAULT 'pendiente',
    FOREIGN KEY (tarea_id) REFERENCES tareas(id),
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
) ENGINE=InnoDB;

CREATE TABLE solicitudes_fichas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folio VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    curp VARCHAR(18) UNIQUE NOT NULL,
    correo VARCHAR(150) NOT NULL,
    carrera_id INT NOT NULL,
    estatus ENUM('pendiente', 'pagada', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (carrera_id) REFERENCES carreras(id)
) ENGINE=InnoDB;

CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emisor_id INT NOT NULL,
    receptor_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emisor_id) REFERENCES usuarios(id),
    FOREIGN KEY (receptor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;


CREATE TABLE log_calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entrega_id INT NOT NULL,
    usuario_modifico_id INT NOT NULL,
    puntos_anteriores DECIMAL(5,2),
    puntos_nuevos DECIMAL(5,2),
    motivo_cambio VARCHAR(255),
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (entrega_id) REFERENCES entregas(id),
    FOREIGN KEY (usuario_modifico_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE calificaciones_unidades (
    alumno_id INT NOT NULL,
    unidad_id INT NOT NULL,
    nota_final DECIMAL(5,2) DEFAULT 0.00,
    PRIMARY KEY (alumno_id, unidad_id),
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id),
    FOREIGN KEY (unidad_id) REFERENCES unidades(id)
) ENGINE=InnoDB;

DELIMITER //

CREATE TRIGGER tr_actualizar_nota_unidad
AFTER UPDATE ON entregas
FOR EACH ROW
BEGIN
    DECLARE v_unidad_id INT;
    DECLARE v_puntos_ganados DECIMAL(10,2);
    DECLARE v_puntos_posibles INT;
    DECLARE v_resultado DECIMAL(5,2);

    SELECT unidad_id INTO v_unidad_id 
    FROM tareas 
    WHERE id = NEW.tarea_id;

    SELECT 
        SUM(e.puntos_obtenidos), 
        SUM(t.puntos_maximos)
    INTO v_puntos_ganados, v_puntos_posibles
    FROM entregas e
    JOIN tareas t ON e.tarea_id = t.id
    WHERE e.alumno_id = NEW.alumno_id 
      AND t.unidad_id = v_unidad_id
      AND e.estatus = 'calificado';

    IF v_puntos_posibles > 0 THEN
        SET v_resultado = (v_puntos_ganados / v_puntos_posibles) * 100;
    ELSE
        SET v_resultado = 0;
    END IF;

    INSERT INTO calificaciones_unidades (alumno_id, unidad_id, nota_final)
    VALUES (NEW.alumno_id, v_unidad_id, v_resultado)
    ON DUPLICATE KEY UPDATE nota_final = v_resultado;

END //

DELIMITER ;


INSERT INTO carreras (nombre, abreviatura) VALUES 
('Ingeniería en Sistemas Computacionales', 'ISIC'),
('Ingeniería en Logística', 'ILOG'),
('Ingeniería Industrial', 'IND'),
('Ingeniería en Gestión Empresarial', 'IGEM');


INSERT INTO usuarios (nombre_completo, correo, password, rol, activo) VALUES 
('Admin', 'yzx1585@gmail.com', '$2y$10$7rLSvRl19ZWhS.uT9aH2U./v.m/M2X0aH.fN2.v6Y6p/8f/L/6nO', 'admin', 1),
('Profe Nombre Pendiente', 'chukanflur7@gmail.com', '$2y$10$7rLSvRl19ZWhS.uT9aH2U./v.m/M2X0aH.fN2.v6Y6p/8f/L/6nO', 'docente', 1),
('Nahomi Cepeda Jimenez', 'nahomicepedaj1418@gmail.com', '$2y$10$7rLSvRl19ZWhS.uT9aH2U./v.m/M2X0aH.fN2.v6Y6p/8f/L/6nO', 'alumno', 1);

INSERT INTO docentes (usuario_id, numero_empleado, especialidad) 
VALUES (2, 'EMP-001', 'Ingeniería de Software');


INSERT INTO alumnos (usuario_id, carrera_id, matricula, fecha_ingreso) 
VALUES (3, 1, '221000118', CURDATE());

ALTER TABLE usuarios 
ADD COLUMN foto_perfil VARCHAR(255) DEFAULT 'default.png' AFTER rol;