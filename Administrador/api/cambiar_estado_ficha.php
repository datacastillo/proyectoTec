<?php
session_start();
header('Content-Type: application/json');
include '../../config/db.php'; // Aquí debe estar definido $pdo

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (isset($_POST['id']) && isset($_POST['estatus'])) {
    $id = intval($_POST['id']);
    $estatus = $_POST['estatus'];

    try {
        // 1. Obtener datos de la ficha
        $stmtFicha = $pdo->prepare("SELECT * FROM solicitudes_fichas WHERE id = ?");
        $stmtFicha->execute([$id]);
        $ficha = $stmtFicha->fetch(PDO::FETCH_ASSOC);

        if (!$ficha) {
            echo json_encode(['success' => false, 'message' => 'Ficha no encontrada']);
            exit;
        }

        $pdo->beginTransaction();

        // 2. Actualizar estado de la ficha
        $stmtUpdate = $pdo->prepare("UPDATE solicitudes_fichas SET estatus = ? WHERE id = ?");
        $stmtUpdate->execute([$estatus, $id]);

        // 3. SI SE APRUEBA, SE INSCRIBE AUTOMÁTICAMENTE
        if ($estatus === 'aprobada') {
            $nombre_completo = $ficha['nombre'] . " " . $ficha['apellido'];
            // Matrícula: 26 + ID Carrera + 4 números aleatorios
            $matricula = "26" . $ficha['carrera_id'] . str_pad(rand(1, 9999), 4, "0", STR_PAD_LEFT);
            $correo_oficial = strtolower($ficha['nombre'] . "." . $ficha['apellido'] . rand(10,99) . "@tecsanpedro.edu.mx");
            $password_encriptado = password_hash($ficha['curp'], PASSWORD_DEFAULT);

            // Insertar en tabla usuarios
            $stmtUser = $pdo->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, 'alumno')");
            $stmtUser->execute([$nombre_completo, $correo_oficial, $password_encriptado]);
            $usuario_id = $pdo->lastInsertId();

            // Insertar en tabla alumnos
            $stmtAlu = $pdo->prepare("INSERT INTO alumnos (usuario_id, carrera_id, matricula) VALUES (?, ?, ?)");
            $stmtAlu->execute([$usuario_id, $ficha['carrera_id'], $matricula]);
            $alumno_id = $pdo->lastInsertId();

            // 4. INSCRIBIR A GRUPOS '1 A' (Primer Semestre)
            $sql_grupos = "SELECT g.id FROM grupos g
                           INNER JOIN materias m ON g.materia_id = m.id
                           WHERE m.carrera_id = ? AND m.semestre = 1 AND g.nombre_grupo = '1 A'";
            
            $stmtGrupos = $pdo->prepare($sql_grupos);
            $stmtGrupos->execute([$ficha['carrera_id']]);
            $grupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

            $stmtInscripcion = $pdo->prepare("INSERT INTO inscripciones (alumno_id, grupo_id) VALUES (?, ?)");
            foreach ($grupos as $grupo) {
                $stmtInscripcion->execute([$alumno_id, $grupo['id']]);
            }

            // Cambiar a estatus final 'inscrito'
            $pdo->prepare("UPDATE solicitudes_fichas SET estatus = 'inscrito' WHERE id = ?")->execute([$id]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Ficha aprobada e inscrito: ' . $matricula]);
        } else {
            // Si solo es rechazada o pendiente, solo guardamos el cambio de estatus
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Estado actualizado a ' . $estatus]);
        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error en proceso: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
}