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