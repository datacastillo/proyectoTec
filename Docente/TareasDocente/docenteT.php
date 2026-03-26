<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

// Obtener ID del docente
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'];

// Consulta de unidades para el selector de tareas
$query_unidades = "SELECT u.id, m.nombre as materia, g.nombre_grupo, u.numero_unit 
                   FROM unidades u 
                   JOIN grupos g ON u.grupo_id = g.id 
                   JOIN materias m ON g.materia_id = m.id 
                   WHERE g.docente_id = '$id_docente'";
$res_unidades = mysqli_query($conexion, $query_unidades);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tareas | ISIC</title>
    <link rel="stylesheet" href="../docente.css">
    <style>
        :root {
            --primary-blue: #1a3a4a;
            --dark-blue: #0d1b2a;
            --sidebar-blue: #142d3e;
            --accent-blue: #3e92cc;
            --text-light: #e0e1dd;
        }

        body { 
            background-color: var(--dark-blue); 
            color: var(--text-light); 
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        .hidden { display: none !important; }

        .add-btn {
            background-color: var(--accent-blue);
            color: white;
            border: none;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            font-size: 22px;
            cursor: pointer;
            margin-left: 20px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .add-btn:hover { background-color: #ffffff; color: var(--primary-blue); }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            padding: 25px;
        }

        .tarea-card {
            background: var(--sidebar-blue);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .tarea-card:hover {
            border-left: 5px solid var(--accent-blue);
            background: #1c3d52;
            transform: scale(1.02);
        }

        .tarea-card h3 { color: #fff; margin: 0 0 10px 0; font-weight: 600; font-size: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;}
        .tarea-card p { font-size: 14px; color: #adb5bd; margin: 4px 0; }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .modal-box {
            background: var(--primary-blue);
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 480px;
            color: #fff;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }

        .modal-box input, .modal-box textarea, .modal-box select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-submit {
            background: var(--accent-blue);
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="wrapper">
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../img/logoTec.png" alt="Logo" style="width: 90px; filter: brightness(0) invert(1);">
            <div style="margin-top: 15px; font-size: 14px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                <span>BIENVENIDO,<br><b><?php echo strtoupper($nombre_docente); ?></b></span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="../docente.php">🏠 INICIO</a></li>
                <li><a href="../MateriasDocente/docenteM.php">📘 MIS MATERIAS</a></li>
                <li><a href="../CalificacionesDocente/calificaciones.php">📝 CALIFICACIONES</a></li>
                <li class="active"><a href="docenteT.php">📂 GESTIÓN TAREAS</a></li>
                <li style="margin-top: 100px;"><a href="../../auth/logout.php" style="color: #ff4444;">🚪 SALIR</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div style="display:flex; align-items:center; padding: 0 25px; height: 70px;">
                <div class="isic-box">MODULO DE TAREAS</div>
                <button class="add-btn" onclick="abrirModal()" title="Nueva Tarea">+</button>
            </div>
        </header>

        <section class="content-section">
            <div id="cardsContainer" class="cards-grid">
                <?php
                $query_tareas = "SELECT t.id, t.titulo, m.nombre as materia, g.nombre_grupo 
                                 FROM tareas t 
                                 JOIN unidades u ON t.unidad_id = u.id 
                                 JOIN grupos g ON u.grupo_id = g.id 
                                 JOIN materias m ON g.materia_id = m.id 
                                 WHERE g.docente_id = '$id_docente' ORDER BY t.id DESC";
                $res_tareas = mysqli_query($conexion, $query_tareas);

                while($row = mysqli_fetch_assoc($res_tareas)): ?>
                    <div class="tarea-card" onclick="verEntregas(<?php echo $row['id']; ?>)">
                        <h3><?php echo strtoupper($row['titulo']); ?></h3>
                        <p>📘 <?php echo $row['materia']; ?></p>
                        <p>👥 <?php echo $row['nombre_grupo']; ?></p>
                    </div>
                <?php endwhile; ?>
            </div>

            <div id="vistaTarea" class="hidden" style="padding: 30px;">
                <button onclick="volver()" style="background:none; color:#3e92cc; border:1px solid #3e92cc; padding:8px 20px; border-radius:4px; cursor:pointer; margin-bottom:20px;">⬅ VOLVER AL LISTADO</button>
                <div id="entregasBody" style="background: var(--sidebar-blue); border-radius: 8px; overflow: hidden; padding: 20px;"></div>
            </div>
        </section>
    </main>
</div>

<div class="modal" id="modalTarea">
    <div class="modal-box">
        <form id="formNuevaTarea">
            <h2 style="text-align: center; font-weight: 300;">NUEVA ACTIVIDAD</h2>
            <input type="text" name="titulo" placeholder="Título de la tarea" required>
            <textarea name="descripcion" rows="4" placeholder="Instrucciones..." required></textarea>
            
            <label style="display:block; margin-top:10px; font-size:12px; color:#aaa;">FECHA LÍMITE:</label>
            <input type="date" name="fecha" required>
            
            <select name="unidad_id" required>
                <option value="">Seleccionar Grupo/Materia...</option>
                <?php mysqli_data_seek($res_unidades, 0); 
                while($u = mysqli_fetch_assoc($res_unidades)): ?>
                    <option value="<?php echo $u['id']; ?>">
                        <?php echo "U{$u['numero_unit']} - {$u['materia']} ({$u['nombre_grupo']})"; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" class="btn-submit">PUBLICAR TAREA</button>
            <button type="button" onclick="cerrarModal()" style="width:100%; background:none; border:none; color:#aaa; cursor:pointer; margin-top:10px;">Descartar</button>
        </form>
    </div>
</div>

<script>
    function abrirModal() { document.getElementById('modalTarea').style.display = 'flex'; }
    function cerrarModal() { document.getElementById('modalTarea').style.display = 'none'; }

    document.getElementById('formNuevaTarea').onsubmit = function(e) {
        e.preventDefault();
        fetch('guardar_tarea.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("Tarea registrada correctamente.");
                location.reload();
            } else {
                alert("Error: " + data.error);
            }
        })
        .catch(err => alert("Error en el servidor."));
    };

    function verEntregas(id) {
        document.getElementById('cardsContainer').classList.add('hidden');
        document.getElementById('vistaTarea').classList.remove('hidden');
        fetch('get_entregas.php?tarea_id=' + id)
        .then(res => res.text())
        .then(html => { document.getElementById('entregasBody').innerHTML = html; });
    }

    function volver() {
        document.getElementById('cardsContainer').classList.remove('hidden');
        document.getElementById('vistaTarea').classList.add('hidden');
    }
</script>
</body>
</html>