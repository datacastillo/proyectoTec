<?php
// Evitamos errores si por alguna razón no hay sesión
$id_user_sidebar = $_SESSION['id_usuario'] ?? 0;
$nombre_side = $_SESSION['nombre'] ?? 'Alumno';

// 1. Obtener la matrícula desde la tabla ALUMNOS
$query_alu_side = "SELECT matricula FROM alumnos WHERE usuario_id = '$id_user_sidebar' LIMIT 1";
$res_alu_side = mysqli_query($conexion, $query_alu_side);
$data_alu_side = mysqli_fetch_assoc($res_alu_side);
$matricula_side = $data_alu_side['matricula'] ?? 'S/N';

// 2. Obtener el avatar actual desde la tabla USUARIOS
$query_usr_side = "SELECT avatar FROM usuarios WHERE id = '$id_user_sidebar' LIMIT 1";
$res_usr_side = mysqli_query($conexion, $query_usr_side);
$data_usr_side = mysqli_fetch_assoc($res_usr_side);
$avatar_actual = (!empty($data_usr_side['avatar'])) ? $data_usr_side['avatar'] : 'default.png';

// Detectar en qué página estamos para poner la clase "active" automáticamente
$current_page = basename($_SERVER['PHP_SELF']);

// 3. Escanear la carpeta de avatares (CORREGIDO: Solo un nivel atrás para llegar a proyectoTec)
$ruta_fisica_avatares = __DIR__ . "/../Administrador/assets/avatares/";
$lista_avatares = [];
if (is_dir($ruta_fisica_avatares)) {
    $lista_avatares = array_diff(scandir($ruta_fisica_avatares), array('..', '.'));
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../img/logoTec.png" alt="Logo" style="max-width: 120px; margin-bottom: 10px;" onerror="this.src='../../img/logoTec.png'">
        
        <div style="margin-top: 15px; margin-bottom: 10px; cursor: pointer; transition: 0.3s;" onclick="abrirModalAvatar()" title="Clic para cambiar avatar">
            <img src="../../Administrador/assets/avatares/<?php echo $avatar_actual; ?>" id="img_nav_profile" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #3e92cc; background-color: #0d1b2a;">
            <div style="font-size: 10px; color: #3e92cc; margin-top: 5px;">✏️ Cambiar foto</div>
        </div>

        <div class="user-info">
            <span style="color:#3e92cc; font-size: 12px; font-weight: bold;">ALUMNO:</span><br>
            <b style="color: white; font-size: 14px;"><?php echo strtoupper($nombre_side); ?></b><br>
            <span style="color: #adb5bd; font-size: 12px;">Matrícula: <?php echo $matricula_side; ?></span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'Index.php' || $current_page == 'detalle_materia.php') ? 'active' : ''; ?>">
                <a href="../Materias/Index.php"><i class="fa-solid fa-book"></i> MIS MATERIAS</a>
            </li>
            <li class="<?php echo ($current_page == 'calificaciones.php') ? 'active' : ''; ?>">
                <a href="../Calificaciones/calificaciones.php"><i class="fa-solid fa-chart-simple"></i> CALIFICACIONES</a>
            </li>
            <li class="<?php echo ($current_page == 'tareas.php') ? 'active' : ''; ?>">
                <a href="../Tareas/tareas.php"><i class="fa-solid fa-list-check"></i> TAREAS PENDIENTES</a>
            </li>
            <li class="<?php echo ($current_page == 'kardex.php') ? 'active' : ''; ?>">
                <a href="../Kardex/kardex.php"><i class="fa-solid fa-file-lines"></i> MI KARDEX</a>
            </li>
            <li style="margin-top: 30px;">
                <a href="../../auth/logout.php" style="color: #e74c3c;"><i class="fa-solid fa-door-open"></i> CERRAR SESIÓN</a>
            </li>
        </ul>
    </nav>
</aside>

<div id="modalAvatar" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); justify-content: center; align-items: center;">
    <div class="modal-content" style="background: #142d3e; padding: 25px; border-radius: 15px; width: 450px; text-align: center; border: 1px solid #3e92cc;">
        <h2 style="color: #fff; margin-bottom: 15px;">Elige tu Avatar</h2>
        
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; max-height: 300px; overflow-y: auto; padding: 10px; background: #0d1b2a; border-radius: 10px;">
            <?php if(!empty($lista_avatares)): ?>
                <?php foreach($lista_avatares as $archivo): ?>
                    <img src="../../Administrador/assets/avatares/<?php echo $archivo; ?>" 
                         onclick="seleccionarEsteAvatar('<?php echo $archivo; ?>', this)"
                         class="avatar-option"
                         style="width: 100%; aspect-ratio: 1/1; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: 0.3s; object-fit: cover;">
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #adb5bd; grid-column: span 4;">No se encontraron avatares en la carpeta.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <input type="hidden" id="input_avatar_seleccionado" value="<?php echo $avatar_actual; ?>">
            
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="cerrarModalAvatar()" style="background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; flex: 1;">CANCELAR</button>
                <button type="button" onclick="guardarAvatarBD()" style="background: #3e92cc; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; flex: 1; font-weight: bold;">GUARDAR CAMBIOS</button>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-option:hover { transform: scale(1.1); border-color: #3e92cc !important; }
    .avatar-selected { border-color: #2ecc71 !important; background: rgba(46, 204, 113, 0.2); transform: scale(1.1); }
</style>

<script>
    function abrirModalAvatar() { document.getElementById("modalAvatar").style.display = "flex"; }
    function cerrarModalAvatar() { document.getElementById("modalAvatar").style.display = "none"; }

    function seleccionarEsteAvatar(nombreArchivo, elemento) {
        document.querySelectorAll('.avatar-option').forEach(img => img.classList.remove('avatar-selected'));
        elemento.classList.add('avatar-selected');
        document.getElementById('input_avatar_seleccionado').value = nombreArchivo;
    }

    function guardarAvatarBD() {
        const avatarElegido = document.getElementById('input_avatar_seleccionado').value;
        let formData = new FormData();
        formData.append('avatar', avatarElegido);

        // CORREGIDO: Usamos la ruta absoluta desde la raíz de tu proyecto 
        // porque "actualizar_avatar.php" está dentro de la carpeta "Materias"
        fetch('/proyectoTec/Alumno/Materias/actualizar_avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('img_nav_profile').src = "../../Administrador/assets/avatares/" + avatarElegido;
                cerrarModalAvatar();
            } else {
                alert("Hubo un problema: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Error de conexión al guardar el avatar.");
        });
    }
</script>