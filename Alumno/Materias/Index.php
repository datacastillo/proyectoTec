<?php
session_start();
require_once '../../config/db.php'; 

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'alumno') {
    header("Location: /proyectoTec/auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAlumno = $_SESSION['nombre'] ?? 'Alumno';

// Consulta de información del alumno
$query_info = "SELECT id, matricula FROM alumnos WHERE usuario_id = '$id_usuario' LIMIT 1";
$res_info = mysqli_query($conexion, $query_info);
$info_alumno = mysqli_fetch_assoc($res_info);

$alumno_id = $info_alumno['id'] ?? 0;
$matricula = $info_alumno['matricula'] ?? 'S/N';

// Consulta para obtener el avatar del usuario desde la base de datos
$query_user = "SELECT avatar FROM usuarios WHERE id = '$id_usuario' LIMIT 1";
$res_user = mysqli_query($conexion, $query_user);
$info_user = mysqli_fetch_assoc($res_user);
$avatar = (!empty($info_user['avatar'])) ? $info_user['avatar'] : 'default.png';

// Consulta de materias dinámicas
$query_materias = "SELECT DISTINCT g.id AS grupo_id, m.nombre, m.clave                   
                   FROM materias m
                   INNER JOIN grupos g ON m.id = g.materia_id
                   INNER JOIN inscripciones i ON g.id = i.grupo_id
                   WHERE i.alumno_id = '$alumno_id'";
$res_materias = mysqli_query($conexion, $query_materias);

// --- NUEVO: Consulta de Alertas/Notificaciones No Leídas ---
$query_alertas = "SELECT id, mensaje, fecha_envio FROM notificaciones WHERE receptor_id = '$id_usuario' AND leido = 0 ORDER BY fecha_envio DESC";
$res_alertas = mysqli_query($conexion, $query_alertas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Alumno | ISIC</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Paleta de colores Azul (Estilo Docente) */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: #0d1b2a; color: #e0e1dd; }
        .wrapper { display: flex; min-height: 100vh; }
        
        /* Barra Lateral */
        .sidebar { width: 280px; background: #142d3e; padding-top: 20px; border-right: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;}
        .sidebar-header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .user-info { margin-top: 15px; }
        .sidebar-nav ul { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-nav li a { display: block; padding: 15px 25px; color: #e0e1dd; text-decoration: none; transition: 0.3s; font-size: 14px; font-weight: bold; }
        .sidebar-nav li a:hover { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        .sidebar-nav li.active a { background: #0d1b2a; color: #3e92cc; border-left: 4px solid #3e92cc; }
        
        /* Contenido Principal */
        .main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { background: #142d3e; padding: 20px 30px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; }
        .isic-box { background: #3e92cc; color: #fff; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px; letter-spacing: 1px; }
        
        /* Tarjetas de Materias */
        .grid-materias { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            margin-top: 30px; 
        }

        @media (max-width: 1200px) {
            .grid-materias { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .grid-materias { grid-template-columns: 1fr; }
        }

        .card-materia { 
            background: linear-gradient(145deg, #142d3e, #102432);
            border: 1px solid rgba(62, 146, 204, 0.2); 
            border-radius: 12px; 
            padding: 30px; 
            transition: all 0.4s ease; 
            cursor: pointer; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card-materia:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
            border-color: #3e92cc; 
        }
        .card-title { font-size: 1.3rem; font-weight: 900; color: #fff; line-height: 1.3; margin-bottom: 10px; }
        .card-clave { font-size: 0.9rem; color: #3e92cc; font-weight: bold; background: rgba(62, 146, 204, 0.1); padding: 5px 10px; border-radius: 5px; display: inline-block; margin-bottom: 20px;}
        .btn-entrar { background: rgba(62, 146, 204, 0.1); color: #3e92cc; border: 1px solid #3e92cc; padding: 12px 20px; border-radius: 8px; font-weight: bold; width: 100%; transition: all 0.3s ease; cursor: pointer; text-transform: uppercase; font-size: 13px; letter-spacing: 1px;}
        .card-materia:hover .btn-entrar { background: #3e92cc; color: #fff; }

        /* --- NUEVOS ESTILOS PREMIUM PARA LA ALERTA --- */
        .alerta-premium {
            background: rgba(20, 45, 62, 0.8); /* Fondo oscuro translúcido */
            backdrop-filter: blur(10px); /* Efecto Glassmorphism */
            border-radius: 12px;
            border: 1px solid rgba(231, 76, 60, 0.3); /* Borde rojo sutil */
            border-left: 5px solid #e74c3c; /* Acento rojo brillante */
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            
            /* Animación de entrada */
            animation: slideDownFade 0.5s ease-out forwards;
            opacity: 0; /* Empieza invisible */
            transform: translateY(-20px); /* Empieza un poco arriba */
        }

        .alerta-icono {
            color: #e74c3c;
            font-size: 1.5rem;
            margin-right: 20px;
            margin-top: 3px;
        }

        .alerta-titulo {
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .alerta-mensaje {
            font-size: 0.95em;
            color: #adb5bd;
            line-height: 1.5;
            display: inline-block;
            margin-top: 5px;
            font-weight: 400;
        }

        .btn-entendido {
            background: transparent;
            color: #e74c3c;
            border: 2px solid #e74c3c;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            margin-left: 20px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-entendido:hover {
            background: #e74c3c;
            color: white;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Definición de la animación de entrada */
        @keyframes slideDownFade {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="wrapper">
    
    <?php include '../sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="isic-box">PORTAL ALUMNO | ISIC</div>
        </header>

        <section style="padding: 40px 30px;">
            
            <?php if($res_alertas && mysqli_num_rows($res_alertas) > 0): ?>
                <div id="contenedor-alertas" style="margin-bottom: 35px;">
                    <?php while($alerta = mysqli_fetch_assoc($res_alertas)): ?>
                        <div id="alerta-<?php echo $alerta['id']; ?>" class="alerta-premium">
                            <div style="display: flex; align-items: flex-start;">
                                <div class="alerta-icono">
                                    <i class="fas fa-satellite-dish"></i> </div>
                                <div>
                                    <span class="alerta-titulo">AVISO DEL SISTEMA</span><br>
                                    <span class="alerta-mensaje">
                                        <?php echo htmlspecialchars($alerta['mensaje']); ?>
                                    </span>
                                </div>
                            </div>
                            <button onclick="marcarLeida(<?php echo $alerta['id']; ?>)" class="btn-entendido">
                                Entendido
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            <h2 style="color: #fff; font-size: 2.2rem; margin-bottom: 5px; font-weight: 900;">Bienvenido, <?php echo strtoupper($nombreAlumno); ?></h2>
            <p style="color: #adb5bd; font-size: 1.1rem; margin-bottom: 30px; font-weight: 400;">Selecciona una materia para ver sus detalles y recursos.</p>

            <div class="grid-materias">
                <?php 
                if($res_materias && mysqli_num_rows($res_materias) > 0) {
                    while($materia = mysqli_fetch_assoc($res_materias)) {
                        echo "
                    <div class='card-materia' onclick=\"location.href='detalle_materia.php?grupo_id=".$materia['grupo_id']."'\">
                        <div>
                            <div class='card-title'>".strtoupper($materia['nombre'])."</div>
                            <div class='card-clave'>CLAVE: ".$materia['clave']."</div>
                        </div>
                        <button class='btn-entrar'>Ir a la materia ➔</button>
                    </div>";
                    }
                } else {
                    echo "<div style='grid-column: 1 / -1; padding: 40px; background: #142d3e; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); text-align: center;'>
                            <h3 style='color: #fff; margin-bottom: 10px;'>Sin materias</h3>
                            <p style='color: #adb5bd;'>No tienes materias inscritas en este semestre.</p>
                          </div>";
                }
                ?>
            </div>
        </section>
    </main>
</div>

<div id="modalAvatar" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#142d3e; padding:30px; border-radius:12px; width:450px; text-align:center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <h3 style="color:#fff; margin-bottom:20px;">Elige tu nuevo Avatar</h3>
        
        <div style="display:flex; flex-wrap:wrap; gap:15px; justify-content:center; margin-bottom: 20px;">
            <img src="../../Administrador/assets/avatares/default.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('default.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_1.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_1.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_2.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_2.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_3.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_3.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_4.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_4.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_5.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_5.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_6.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_6.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_7.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_7.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_8.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_8.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_9.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_9.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_10.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_10.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_11.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_11.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_12.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_12.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_13.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_13.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_14.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_14.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
            <img src="../../Administrador/assets/avatares/avatar_15.png" class="avatar-opt" onclick="seleccionarNuevoAvatar('avatar_15.png', this)" style="width:70px; height:70px; border-radius:50%; cursor:pointer; border:3px solid transparent; background: #0d1b2a;">
        </div>

        <input type="hidden" id="inputMiAvatar" value="">
        
        <div style="margin-top:20px; display: flex; justify-content: center; gap: 10px;">
            <button onclick="guardarMiAvatar()" style="background:#2ecc71; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold; width: 100%;">Guardar Avatar</button>
            <button onclick="cerrarModalAvatar()" style="background:#e74c3c; color:#fff; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold; width: 100%;">Cancelar</button>
        </div>
    </div>
</div>

<script>
    // --- LÓGICA PARA MARCAR ALERTA COMO LEÍDA (Intacto pero con animación de salida) ---
    function marcarLeida(idNotificacion) {
        const alerta = document.getElementById('alerta-' + idNotificacion);
        
        // Animación suave de salida
        alerta.style.transition = "all 0.4s ease";
        alerta.style.opacity = "0";
        alerta.style.transform = "translateX(50px)"; // Se barre hacia la derecha

        // Esperamos a que termine la animación para quitar el espacio
        setTimeout(() => {
            alerta.style.display = 'none';
            
            // Si ya no quedan alertas visibles, quitamos el margen del contenedor
            const contenedor = document.getElementById('contenedor-alertas');
            if (contenedor && contenedor.querySelectorAll('.alerta-premium[style*="display: none"]').length === contenedor.querySelectorAll('.alerta-premium').length) {
                contenedor.style.marginBottom = "0";
            }
        }, 400);

        // Petición al servidor para actualizar el estado
        fetch('marcar_leida.php?id=' + idNotificacion)
            .catch(error => console.error("Error al marcar como leída:", error));
    }

    // --- LÓGICA DE AVATARES (Código intacto) ---
    function abrirModalAvatar() {
        document.getElementById('modalAvatar').style.display = 'flex';
        document.getElementById('inputMiAvatar').value = ''; 
        // Resetear bordes
        document.querySelectorAll('.avatar-opt').forEach(img => img.style.borderColor = 'transparent');
    }

    function cerrarModalAvatar() {
        document.getElementById('modalAvatar').style.display = 'none';
    }

    function seleccionarNuevoAvatar(archivo, elemento) {
        document.getElementById('inputMiAvatar').value = archivo;
        
        // Quitar borde a todos y ponérselo al seleccionado
        document.querySelectorAll('.avatar-opt').forEach(img => img.style.borderColor = 'transparent');
        elemento.style.borderColor = '#3e92cc';
    }

    async function guardarMiAvatar() {
        const avatarSeleccionado = document.getElementById('inputMiAvatar').value;
        
        if (!avatarSeleccionado) {
            alert("⚠️ Por favor, selecciona un avatar primero.");
            return;
        }

        const formData = new FormData();
        formData.append('avatar', avatarSeleccionado);

        try {
            const response = await fetch('actualizar_avatar.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                location.reload();
            } else {
                alert("❌ Error: " + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert("⚠️ Ocurrió un error de conexión.");
        }
    }
</script>
</body>
</html>