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

// Obtener grupos para las tarjetas
$query = "SELECT g.id, g.nombre_grupo, m.nombre AS materia 
          FROM grupos g 
          JOIN materias m ON g.materia_id = m.id 
          WHERE g.docente_id = '$id_docente'";
$grupos = mysqli_query($conexion, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Tareas | ISIC</title>
    <style>
        :root {
            --bg-principal: #0f3145;
            --bg-secundario: #255b68;
            --accent: #2b6671;
            --texto: #ffffff;
            --subtexto: #adb5bd;
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

        /* SIDEBAR FIJA */
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
            color: var(--subtexto);
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
            border: 1px solid var(--accent);
            border-radius: 12px;
            padding: 25px;
            cursor: pointer;
            transition: 0.3s;
            width: 280px;
            text-align: center;
        }

        .card-materia:hover {
            transform: translateY(-5px);
            border-color: var(--exito);
        }

        .btn-accion {
            background: var(--exito);
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        /* MODALES */
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
            width: 450px;
            border: 1px solid var(--accent);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .modal-content input, .modal-content select, .modal-content textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: var(--bg-principal);
            border: 1px solid var(--accent);
            color: white;
            border-radius: 5px;
            box-sizing: border-box;
        }

        /* Spinner para carga de PDF */
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border-left-color: var(--exito);
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
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
            <li><a href="../MateriasDocente/docenteM.php">📚 MIS MATERIAS</a></li>
            <li><a href="../Calificaciones/calificaciones.php">📝 CALIFICACIONES</a></li>
            <li><a href="docenteT.php" class="active">📂 TAREAS</a></li>
        </ul>
        <a href="../../auth/logout.php" style="margin-top:auto; padding:20px; text-align:center; color:#ffb3b3; text-decoration:none; font-weight:bold;">CERRAR SESIÓN</a>
    </aside>

    <main class="main-content">
        <div id="vista_grupos">
            <h1>Gestión de Tareas</h1>
            <p style="color: var(--subtexto); margin-bottom: 30px;">Selecciona un grupo para gestionar actividades.</p>
            <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                <?php while($g = mysqli_fetch_assoc($grupos)): ?>
                    <div class="card-materia" onclick="abrirGrupo(<?php echo $g['id']; ?>, '<?php echo $g['materia']; ?>')">
                        <div style="font-size: 2rem; margin-bottom: 10px;">📁</div>
                        <h3><?php echo $g['materia']; ?></h3>
                        <small style="color: var(--exito);">Grupo: <?php echo $g['nombre_grupo']; ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div id="vista_detalle" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 id="txt_titulo_materia"></h2>
                <div>
                    <button class="btn-accion" onclick="mostrarModalTarea()" style="background: var(--accent); margin-right: 10px; color: white;">+ Nueva Tarea</button>
                    <button onclick="regresar()" style="background:none; border:1px solid var(--subtexto); color:white; padding:10px; cursor:pointer; border-radius:5px;">⬅ Volver</button>
                </div>
            </div>
            <div id="contenedor_entregas"></div>
        </div>
    </main>

    <div id="modalTarea" class="modal">
        <div class="modal-content">
            <h2 style="margin-top:0; color: var(--exito);">Asignar Nueva Tarea</h2>
            <form id="formNuevaTarea">
                <input type="hidden" name="grupo_id" id="modal_grupo_id">
                
                <label>Título de la tarea:</label>
                <input type="text" name="titulo" required placeholder="Ej: Ensayo de Redes">
                
                <label>Unidad:</label>
                <select name="unidad_id" id="select_unidades" required></select>

                <label>Descripción:</label>
                <textarea name="descripcion" rows="3" placeholder="Instrucciones para el alumno..."></textarea>

                <button type="submit" class="btn-accion" style="width:100%; margin-top:10px;">🚀 Publicar Tarea</button>
                <button type="button" onclick="cerrarModal()" style="width:100%; background:none; border:none; color:var(--subtexto); margin-top:10px; cursor:pointer;">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="visor_modal" class="modal" style="background: rgba(0,0,0,0.92);">
        <div style="background: #0f3145; border: 1px solid var(--accent); border-radius: 12px; width: 95%; max-width: 1400px; height: 92vh; display: flex; flex-direction: column; overflow: hidden;">
            
            <div style="padding: 15px 25px; background: var(--bg-secundario); border-bottom: 1px solid var(--accent); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 id="visor_nombre_alumno" style="margin: 0; color: var(--exito);">CARGANDO...</h3>
                    <small id="visor_titulo_tarea" style="color: var(--subtexto);">Tarea</small>
                </div>
                <button onclick="cerrarVisor()" style="background:none; border:none; color:white; font-size:35px; cursor:pointer;">&times;</button>
            </div>

            <div style="flex: 1; display: flex;">
                <div style="flex: 2; background: #1a1a1a; position: relative; border-right: 1px solid var(--accent);">
                    <iframe id="pdf_frame" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                    <div id="pdf_loading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div class="spinner"></div>
                        <p style="color: var(--subtexto); margin-top: 10px;">Cargando documento...</p>
                    </div>
                </div>

                <div style="flex: 0.6; padding: 30px; background: #0f3145; display: flex; flex-direction: column;">
                    <h4 style="color:white; margin-bottom: 25px;">EVALUACIÓN</h4>
                    
                    <label style="color: var(--subtexto); font-size: 13px;">CALIFICACIÓN (0-100):</label>
                    <input type="number" id="visor_nota_input" min="0" max="100" step="0.1" 
                           style="width: 100%; padding: 15px; background: #163d50; border: 2px solid var(--exito); color: var(--exito); border-radius: 8px; font-size: 32px; text-align: center; font-weight: bold; margin: 15px 0;">

                    <button onclick="guardarCalificacionVisor()" id="btn_guardar_visor" class="btn-accion" style="padding: 20px; font-size: 1.1rem; margin-top: 20px;">
                        💾 GUARDAR NOTA
                    </button>
                    
                    <p style="font-size: 12px; color: var(--subtexto); line-height: 1.6; margin-top: auto;">
                        * La nota se guardará directamente en la base de datos y se reflejará en la tabla del grupo.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    let grupoSeleccionado = 0;
    let actualEntregaId = null;

    // --- LÓGICA DE GRUPOS Y TAREAS ---
    function abrirGrupo(id, nombre) {
        grupoSeleccionado = id;
        document.getElementById('vista_grupos').style.display = 'none';
        document.getElementById('vista_detalle').style.display = 'block';
        document.getElementById('txt_titulo_materia').innerText = nombre;
        document.getElementById('modal_grupo_id').value = id;
        cargarEntregas(id);
    }

    function cargarEntregas(id) {
        fetch('get_entregas.php?grupo_id=' + id)
            .then(res => res.text())
            .then(html => { document.getElementById('contenedor_entregas').innerHTML = html; });
    }

    // --- LÓGICA DEL VISOR ---
    function abrirVisor(entregaId, nombre, tarea, ruta, notaActual) {
        actualEntregaId = entregaId;
        document.getElementById('visor_nombre_alumno').innerText = nombre.toUpperCase();
        document.getElementById('visor_titulo_tarea').innerText = tarea;
        document.getElementById('visor_nota_input').value = (notaActual > 0) ? notaActual : "";
        
        const frame = document.getElementById('pdf_frame');
        const loading = document.getElementById('pdf_loading');
        
        frame.style.display = 'none';
        loading.style.display = 'block';
        frame.src = ruta;

        frame.onload = function() {
            frame.style.display = 'block';
            loading.style.display = 'none';
        };

        document.getElementById('visor_modal').style.display = 'flex';
    }

    function cerrarVisor() {
        document.getElementById('visor_modal').style.display = 'none';
        document.getElementById('pdf_frame').src = "";
    }

    function guardarCalificacionVisor() {
        const nota = document.getElementById('visor_nota_input').value;
        const btn = document.getElementById('btn_guardar_visor');

        if (nota === "" || nota < 0 || nota > 100) {
            alert("Ingresa una nota válida.");
            return;
        }

        btn.innerText = "⏳ Guardando...";
        btn.disabled = true;

        const data = new FormData();
        data.append('entrega_id', actualEntregaId);
        data.append('nota', nota);

        fetch('guardar_nota_tarea.php', { method: 'POST', body: data })
        .then(res => res.text())
        .then(res => {
            if(res.trim() === "success") {
                cargarEntregas(grupoSeleccionado); 
                cerrarVisor();
            } else {
                alert("Error al guardar nota");
            }
        })
        .finally(() => {
            btn.innerText = "💾 GUARDAR NOTA";
            btn.disabled = false;
        });
    }

    // --- GESTIÓN DE MODAL NUEVA TAREA ---
    function mostrarModalTarea() {
        document.getElementById('modalTarea').style.display = 'flex';
        fetch(`../Calificaciones/get_calificaciones_unidades.php?grupo_id=${grupoSeleccionado}&solo_unidades=1`)
            .then(res => res.json())
            .then(unidades => {
                let options = unidades.map(u => `<option value="${u.id}">${u.nombre_unidad}</option>`).join('');
                document.getElementById('select_unidades').innerHTML = options;
            });
    }

    document.getElementById('formNuevaTarea').onsubmit = function(e) {
        e.preventDefault();
        fetch('guardar_tarea.php', { method: 'POST', body: new FormData(this) })
        .then(() => { cerrarModal(); cargarEntregas(grupoSeleccionado); });
    };

    function cerrarModal() { document.getElementById('modalTarea').style.display = 'none'; }
    function regresar() { 
        document.getElementById('vista_grupos').style.display = 'flex'; 
        document.getElementById('vista_detalle').style.display = 'none'; 
    }
    </script>
</body>
</html>