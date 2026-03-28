<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'docente') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre_docente = $_SESSION['nombre'] ?? 'Docente';

// Obtener ID del docente
$res_doc = mysqli_query($conexion, "SELECT id FROM docentes WHERE usuario_id = '$id_usuario'");
$doc = mysqli_fetch_assoc($res_doc);
$id_docente = $doc['id'] ?? 0;

// Consulta de Grupos
$query = "SELECT g.id AS grupo_id, g.nombre_grupo, m.nombre AS materia_nombre 
          FROM grupos g 
          INNER JOIN materias m ON g.materia_id = m.id 
          WHERE g.docente_id = '$id_docente'";
$res_grupos = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Materias | ISIC</title>
    <style>
        :root {
            --bg-principal: #0f3145;
            --bg-secundario: #255b68;
            --accent: #2b6671;
            --texto: #ffffff;
            --exito: #2ecc71;
        }

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-principal);
            color: var(--texto);
            font-family: 'Segoe UI', sans-serif;
        }

        /* BARRA LATERAL FIJA */
        .sidebar {
            width: 250px;
            min-width: 250px;
            background-color: var(--bg-secundario);
            border-right: 1px solid var(--accent);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-menu { list-style: none; padding: 0; margin: 0; }

        .sidebar-menu li a {
            display: block;
            padding: 15px 25px;
            color: #adb5bd;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            background: var(--bg-principal);
            color: white;
            border-left: 4px solid var(--exito);
        }

        /* CONTENIDO */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .card-materia {
            background: var(--bg-secundario);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--accent);
            cursor: pointer;
            transition: 0.3s;
            width: 280px;
            text-align: center;
        }

        .card-materia:hover {
            transform: translateY(-5px);
            border-color: var(--exito);
        }

        .tabla-alumnos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--bg-secundario);
            border-radius: 10px;
            overflow: hidden;
        }

        .tabla-alumnos th { background: var(--accent); padding: 15px; text-align: left; }
        .tabla-alumnos td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        
        .btn-accion {
            background: var(--accent);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        .btn-accion:hover { background: var(--exito); color: black; }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            justify-content: center; align-items: center;
            z-index: 2000;
        }
        
        .modal-content {
            background: var(--bg-secundario);
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            border: 1px solid var(--accent);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .modal-content input {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            background: var(--bg-principal);
            border: 1px solid var(--accent);
            color: white;
            border-radius: 5px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <h2 style="color: var(--exito); margin:0;">ISIC</h2>
            <small>Docente: <?php echo htmlspecialchars($nombre_docente); ?></small>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../docente.php">🏠 INICIO</a></li>
            <li><a href="docenteM.php" class="active">📚 MIS MATERIAS</a></li>
            <li><a href="../Calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
            <li><a href="../TareasDocente/docenteT.php">📂 TAREAS</a></li>
        </ul>
        <a href="../../auth/logout.php" style="margin-top:auto; padding:20px; text-align:center; color:#ffb3b3; text-decoration:none; font-weight:bold;">CERRAR SESIÓN</a>
    </aside>

    <main class="main-content">
        <div id="vista_principal">
            <h1>Mis Materias</h1>
            <p style="color: #adb5bd;">Selecciona un grupo para ver alumnos y agregar unidades.</p>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <?php while($g = mysqli_fetch_assoc($res_grupos)): ?>
                    <div class="card-materia" onclick="verAlumnos(<?php echo $g['grupo_id']; ?>, '<?php echo addslashes($g['materia_nombre']); ?>')">
                        <div style="font-size: 2rem; margin-bottom: 10px;">📚</div>
                        <small style="color: var(--exito); font-weight:bold;">Grupo: <?php echo htmlspecialchars($g['nombre_grupo']); ?></small>
                        <h3 style="margin: 10px 0;"><?php echo htmlspecialchars($g['materia_nombre']); ?></h3>
                        <p style="font-size: 12px; color: #adb5bd; margin:0;">Click para gestionar</p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="vista_alumnos" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 id="titulo_materia" style="margin:0;"></h2>
                <div>
                    <button class="btn-accion" onclick="abrirModalUnidad()" style="background: var(--exito); color: black; margin-right: 10px;">+ Nueva Unidad</button>
                    <button class="btn-accion" onclick="regresar()" style="background: transparent; border: 1px solid #adb5bd; color: white;">⬅ Volver</button>
                </div>
            </div>
            <div id="tabla_alumnos_res"></div>
        </div>
    </main>

    <div id="modalUnidad" class="modal">
        <div class="modal-content">
            <h2 style="margin-top:0; color: var(--exito);">Agregar Nueva Unidad</h2>
            <form id="formNuevaUnidad">
                <p style="color: #adb5bd; font-size: 14px;">Ingresa el nombre del tema o unidad (Ej: "Unidad 1: Fundamentos").</p>
                
                <label style="font-weight: bold;">Nombre de la Unidad:</label>
                <input type="text" name="nombre_unidad" required placeholder="Ej: Unidad 1...">

                <button type="submit" class="btn-accion" style="width:100%; background: var(--exito); color: black;">💾 Guardar Unidad</button>
                <button type="button" onclick="cerrarModalUnidad()" style="width:100%; background:none; border:none; color:#adb5bd; margin-top:10px; cursor:pointer;">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
    let grupoActualId = 0; // Guardamos el ID del grupo que estamos viendo

    function verAlumnos(grupoId, materia) {
        grupoActualId = grupoId;
        document.getElementById('vista_principal').style.display = 'none';
        document.getElementById('vista_alumnos').style.display = 'block';
        document.getElementById('titulo_materia').innerText = materia;

        fetch('get_alumnos.php?grupo_id=' + grupoId)
            .then(res => res.text())
            .then(html => { document.getElementById('tabla_alumnos_res').innerHTML = html; });
    }

    function regresar() {
        document.getElementById('vista_principal').style.display = 'block';
        document.getElementById('vista_alumnos').style.display = 'none';
    }

    // --- LÓGICA DEL MODAL DE UNIDADES ---
    function abrirModalUnidad() {
        document.getElementById('modalUnidad').style.display = 'flex';
    }

    function cerrarModalUnidad() {
        document.getElementById('modalUnidad').style.display = 'none';
        document.getElementById('formNuevaUnidad').reset();
    }

    // --- ENVÍO DE LA NUEVA UNIDAD AL SERVIDOR ---
    document.getElementById('formNuevaUnidad').onsubmit = function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        btn.innerText = "⏳ Guardando...";
        btn.disabled = true;

        const data = new FormData(this);
        data.append('grupo_id', grupoActualId); // Le pegamos el ID del grupo

        fetch('guardar_unidad.php', { method: 'POST', body: data })
        .then(res => res.text())
        .then(res => {
            if(res.trim() === "success") {
                alert("✅ Unidad creada correctamente.");
                cerrarModalUnidad();
                // Opcional: Podrías recargar algo aquí si estuvieras listando las unidades
            } else {
                alert("❌ Error al crear la unidad:\n" + res);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("❌ Error de conexión.");
        })
        .finally(() => {
            btn.innerText = "💾 Guardar Unidad";
            btn.disabled = false;
        });
    };
    </script>
</body>
</html>