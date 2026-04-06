<?php
session_start();
// Asegúrate de que la ruta a db.php sea correcta
// Si admin.php está en /Administrador/ y db.php en /config/
require_once '../config/db.php';

// Candado de seguridad: Si no hay sesión o no es admin, lo pateamos al login
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header("Location: ../../auth/login.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombreAdmin = $_SESSION['nombre'] ?? 'ADMINISTRADOR';

// --- NUEVA LÓGICA: Obtener datos de perfil (CORREGIDA A MYSQLI) ---
$sql = "SELECT foto_perfil, telefono FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_usuario);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

$foto_actual = !empty($user_data['foto_perfil']) ? $user_data['foto_perfil'] : 'avatar_1.png';
$telefono_actual = $user_data['telefono'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | ISIC</title>

    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos para la selección de avatar en el modal */
        .avatar-option {
            width: 45px; 
            height: 45px; 
            cursor: pointer; 
            border: 3px solid transparent; 
            border-radius: 50%; 
            transition: 0.3s;
            object-fit: cover;
        }
        .avatar-option.selected {
            border-color: #d4af37 !important;
            transform: scale(1.1);
            background: rgba(212, 175, 55, 0.2);
        }
        .avatar-opcion.selected { /* Para la sección de perfil */
            border-color: #d4af37 !important;
            transform: scale(1.1);
        }
    </style>
</head>

<body>

<div class="app-container">

    <aside class="sidebar" id="sidebar">

        <div class="brand-section">
            <img src="../Alumno/img/logoTec.png" alt="Logo TEC" class="logo-img" style="max-width: 120px;">
        </div>

        <div class="user-profile" style="text-align: center; margin-top: 20px; color: white;">
            <img id="imgSideBar" src="assets/avatares/<?php echo $foto_actual; ?>" style="width: 65px; height: 65px; border-radius: 50%; border: 2px solid #d4af37; object-fit: cover; margin-bottom: 10px;">
            <br>
            <span class="user-id" style="font-weight: bold; margin-top: 10px; display: block;">
                <?php echo strtoupper($nombreAdmin); ?>
            </span>
            <span style="font-size: 12px; color: #adb5bd;">Panel de Control</span>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li class="nav-item active" onclick="mostrarSeccion('alumnos')">
                    <i class="fas fa-user-graduate"></i> ALUMNOS
                </li>

                <li class="nav-item" onclick="mostrarSeccion('docentes')">
                    <i class="fas fa-chalkboard-teacher"></i> DOCENTES
                </li>

                <li class="nav-item" onclick="mostrarSeccion('fichas')">
                    <i class="fas fa-file-alt"></i> FICHAS
                </li>

                <li class="nav-item" onclick="mostrarSeccion('materias')">
                    <i class="fas fa-book"></i> MATERIAS
                </li>
                
                <li class="nav-item" onclick="mostrarSeccion('carga')">
                    <i class="fas fa-tasks"></i> CARGA ACADÉMICA
                </li>

                <li class="nav-item" onclick="mostrarSeccion('perfil')" style="color: #f1c40f;">
                    <i class="fas fa-user-cog"></i> MI PERFIL
                </li>

                <li class="nav-item" style="margin-top: 30px;">
                    <a href="../../auth/logout.php" style="color: #e74c3c; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-sign-out-alt"></i> CERRAR SESIÓN
                    </a>
                </li>
            </ul>
        </nav>

    </aside>

    <main class="main-content">

        <header class="main-header">
            <div class="header-left">
                <div class="menu-btn" onclick="toggleMenu()" style="cursor:pointer; font-size: 24px;">☰</div>
            </div>

            <div class="header-right">
                <div class="isic-box" style="background: #d4af37; color: #000; padding: 8px 15px; border-radius: 5px; font-weight: bold; font-size: 14px;">
                    MÓDULO ADMINISTRADOR
                </div>
            </div>
        </header>

        <section class="content-body seccion" id="alumnos">

    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Alumnos Inscritos</h2>
        <button class="btn-primary" onclick="document.getElementById('modalRegistrarAlumno').style.display='flex'">+ Nuevo Alumno</button>
    </div>

    <div style="margin-bottom: 15px;">
        <input type="text" id="buscadorAlumnos" placeholder="Buscar por nombre o matrícula..." style="padding: 8px; width: 100%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc; color: black; outline: none;">
    </div>
    <div class="table-container">
        <table class="user-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NOMBRE</th>
                    <th>MATRÍCULA</th>
                    <th>ACCIONES</th>
                </tr>
            </thead>
            <tbody id="tablaAlumnos">
            </tbody>
        </table>
    </div>

</section>

        <section class="content-body seccion" id="docentes" style="display:none;">

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Plantilla Docente</h2>
                <div>
                    <button class="btn-primary" onclick="document.getElementById('modalRegistrarDocente').style.display='flex'" style="background: #28a745; margin-left: 10px;">+ Registro Completo</button>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <input type="text" id="buscadorDocentes" placeholder="Buscar por nombre o especialidad..." style="padding: 8px; width: 100%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc; color: black; outline: none;">
            </div>
            <div class="table-container">
                <table class="user-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>ESPECIALIDAD</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDocentes">
                        </tbody>
                </table>
            </div>

        </section>

        <section class="content-body seccion" id="fichas" style="display:none;">

            <div class="page-header">
                <h2>Fichas (Solicitudes de Admisión)</h2>
            </div>

            <div class="table-container">
                <table class="user-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>TIPO</th>
                            <th>ESTADO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaFichas">
                        </tbody>
                </table>
            </div>

        </section>

        <section class="content-body seccion" id="materias" style="display:none;">

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Gestión de Materias</h2>
                <button class="btn-primary" onclick="abrirModal('MATERIA')">+ Nueva Materia</button>
            </div>

            <div style="margin-bottom: 15px;">
                <input type="text" id="buscadorMaterias" placeholder="Buscar por nombre o clave..." style="padding: 8px; width: 100%; max-width: 400px; border-radius: 5px; border: 1px solid #ccc; color: black; outline: none;">
            </div>
            <div class="table-container">
                <table class="user-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>CLAVE</th>
                            <th>NOMBRE</th>
                            <th>CARRERA</th>
                            <th>SEMESTRE</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaMaterias">
                    </tbody>
                </table>
            </div>

        </section>

        <section class="content-body seccion" id="carga" style="display:none;">

            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Asignación de Carga Académica (Grupos)</h2>
                <button class="btn-primary" onclick="document.getElementById('modalAsignarCarga').style.display='flex'">+ Asignar Materia a Docente</button>
            </div>

            <div class="table-container">
                <table class="user-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>DOCENTE</th>
                            <th>MATERIA</th>
                            <th>GRUPO</th>
                            <th>SEMESTRE</th>
                            <th>CICLO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCargaAcademica">
                        </tbody>
                </table>
            </div>

        </section>

        <section class="content-body seccion" id="perfil" style="display:none;">
            <div class="page-header">
                <h2>Mi Perfil de Administrador</h2>
            </div>
            
            <div style="background: #1a1a2e; padding: 30px; border-radius: 12px; border: 1px solid #333;">
                <form id="formMiPerfil" onsubmit="actualizarPerfil(event)">
                    <div style="display: flex; flex-wrap: wrap; gap: 30px; align-items: center; margin-bottom: 30px;">
                        <div style="text-align: center;">
                            <img id="imgPerfilGrande" src="assets/avatares/<?php echo $foto_actual; ?>" 
                                 style="width: 150px; height: 150px; border-radius: 50%; border: 4px solid #d4af37; object-fit: cover; box-shadow: 0 0 15px rgba(212, 175, 55, 0.3);">
                            <p style="color: #d4af37; margin-top: 10px; font-weight: bold;">AVATAR ACTUAL</p>
                        </div>

                        <div style="flex: 1; min-width: 250px;">
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: #888; margin-bottom: 5px;">Nombre Completo</label>
                                <input type="text" value="<?php echo $nombreAdmin; ?>" style="width: 100%; padding: 10px; background: #0f0f1a; border: 1px solid #333; color: #666;" readonly>
                            </div>
                            <div style="margin-bottom: 15px;">
                                <label style="display: block; color: white; margin-bottom: 5px;">Teléfono</label>
                                <input type="text" name="telefono" id="perfilTelefono" value="<?php echo $telefono_actual; ?>" placeholder="871XXXXXXX" style="width: 100%; padding: 10px; background: #fff; border: 1px solid #d4af37; color: #000; border-radius: 5px;">
                            </div>
                            <input type="hidden" name="foto_perfil" id="inputAvatarSeleccionado" value="<?php echo $foto_actual; ?>">
                            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; background: #d4af37; color: black; font-weight: bold; border: none; cursor: pointer;">
                                ACTUALIZAR DATOS
                            </button>
                        </div>
                    </div>

                    <h3 style="color: white; border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 20px;">Cambiar Avatar</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 15px; background: #0f0f1a; padding: 20px; border-radius: 10px;">
                        
                        <img src="assets/avatares/default.png" 
                             class="avatar-opcion" 
                             onclick="seleccionarAvatarPerfil('default.png', this)"
                             style="width: 100%; cursor: pointer; border-radius: 50%; border: 3px solid transparent; transition: 0.3s; <?php if('default.png' == $foto_actual) echo 'border-color: #d4af37; transform: scale(1.1);'; ?>">

                        <?php for($i=1; $i<=15; $i++): $nombreImg = "avatar_$i.png"; ?>
                            <img src="assets/avatares/<?php echo $nombreImg; ?>" 
                                 class="avatar-opcion" 
                                 onclick="seleccionarAvatarPerfil('<?php echo $nombreImg; ?>', this)"
                                 style="width: 100%; cursor: pointer; border-radius: 50%; border: 3px solid transparent; transition: 0.3s; <?php if($nombreImg == $foto_actual) echo 'border-color: #d4af37; transform: scale(1.1);'; ?>">
                        <?php endfor; ?>

                    </div>
                </form>
            </div>
        </section>

        </main>

</div>

<div id="userModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 id="modalTitle" style="color: #d4af37;">Nuevo Usuario</h3>
            <span class="close-modal" onclick="cerrarModal()" style="cursor: pointer; font-size: 24px; color: white;">&times;</span>
        </div>
        <form id="userForm" onsubmit="event.preventDefault(); guardarUsuario();">
            <input type="hidden" id="userId">
            <input type="hidden" id="userRole"> 
            
            <div id="avatarGroup" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; color: white;">Selecciona un Avatar:</label>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; background: rgba(255,255,255,0.05); padding: 10px; border-radius: 8px;">
                    
                    <input type="hidden" id="userAvatar" value="default.png">
                    <img src="assets/avatares/default.png" 
                         class="avatar-option selected" 
                         onclick="seleccionarAvatarModal('default.png', this)"
                         title="default.png">

                    <?php for($i=1; $i<=15; $i++): $img = "avatar_$i.png"; ?>
                        <img src="assets/avatares/<?php echo $img; ?>" 
                             class="avatar-option" 
                             onclick="seleccionarAvatarModal('<?php echo $img; ?>', this)"
                             title="<?php echo $img; ?>">
                    <?php endfor; ?>

                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nombre Completo</label>
                <input type="text" id="userName" required placeholder="Ej. Juan Castillo" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;" id="emailGroup">
                <label style="display: block; margin-bottom: 5px;">Correo Electrónico</label>
                <input type="email" id="userEmail" required placeholder="correo@ejemplo.com" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;" id="passGroup">
                <label style="display: block; margin-bottom: 5px;">Contraseña</label>
                <input type="password" id="userPass" required placeholder="********" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label id="extraLabel" style="display: block; margin-bottom: 5px;">Matrícula</label>
                <input type="text" id="userExtra" required placeholder="Dato adicional" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>
            <div class="modal-footer" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="button" class="btn-secondary" onclick="cerrarModal()" style="flex: 1; padding: 10px; cursor: pointer;">CANCELAR</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; cursor: pointer;">GUARDAR</button>
            </div>
        </form>
    </div>
</div>

<div id="modalRegistrarDocente" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #1a1a2e; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #d4af37; margin: 0;">Registrar Nuevo Docente</h3>
            <span onclick="document.getElementById('modalRegistrarDocente').style.display='none'" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <form id="formRegistrarDocente">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nombre Completo</label>
                <input type="text" name="nombre_completo" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Correo Electrónico</label>
                <input type="email" name="correo" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Contraseña</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Número de Empleado</label>
                <input type="text" name="numero_empleado" required placeholder="Ej. EMP-001" style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Especialidad</label>
                <input type="text" name="especialidad" placeholder="Ej. Sistemas, Matemáticas..." style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalRegistrarDocente').style.display='none'" style="flex: 1; padding: 10px;">CANCELAR</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px;">GUARDAR DOCENTE</button>
            </div>
        </form>
    </div>
</div>

<div id="modalRegistrarAlumno" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #1a1a2e; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #3b82f6; margin: 0;">Registrar Nuevo Alumno</h3>
            <span onclick="document.getElementById('modalRegistrarAlumno').style.display='none'" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <form id="formRegistrarAlumno">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nombre Completo</label>
                <input type="text" name="nombre_completo" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Correo Electrónico</label>
                <input type="email" name="correo" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Contraseña</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Matrícula</label>
                <input type="text" name="matricula" required placeholder="Ej. 24040001" style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalRegistrarAlumno').style.display='none'" style="flex: 1; padding: 10px;">CANCELAR</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px; background: #3b82f6;">GUARDAR ALUMNO</button>
            </div>
        </form>
    </div>
</div>

<div id="modalAsignarCarga" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #1a1a2e; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; color: white;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="color: #d4af37; margin: 0;">Asignar Materia a Docente</h3>
            <span onclick="document.getElementById('modalAsignarCarga').style.display='none'" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        <form id="formAsignarCarga">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Docente</label>
                <select name="docente_id" id="selectDocenteCarga" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
                    <option value="">Seleccione un docente...</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px;">Semestre</label>
                    <select name="semestre" onchange="cargarMateriasPorSemestre(this.value)" required style="width: 100%; padding: 10px; border-radius: 4px; border: none; box-sizing: border-box;">
                        <option value="">Seleccione...</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 5px;">Ciclo Escolar</label>
                    <select name="ciclo_escolar" required style="width: 100%; padding: 10px; border-radius: 4px; border: none; box-sizing: border-box;">
                        <option value="">Seleccione...</option>
                        <option value="2026-1">2026-1</option>
                        <option value="2026-2">2026-2</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Materia</label>
                <select name="materia_id" id="selectMateriaCarga" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
                    <option value="">Seleccione un semestre primero...</option>
                </select>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nombre del Grupo</label>
                <select name="nombre_grupo" required style="width: 100%; padding: 10px; border-radius: 4px; border: none;">
                    <option value="">Seleccione un grupo...</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('modalAsignarCarga').style.display='none'" style="flex: 1; padding: 10px;">CANCELAR</button>
                <button type="submit" class="btn-primary" style="flex: 1; padding: 10px;">ASIGNAR MATERIA</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Función para seleccionar avatar en el modal de usuarios
    function seleccionarAvatarModal(nombreImg, elemento) {
        // Quitar la clase 'selected' de todos los avatares en el modal
        document.querySelectorAll('.avatar-option').forEach(img => img.classList.remove('selected'));
        // Agregar la clase al seleccionado
        elemento.classList.add('selected');
        // Guardar el nombre en el input oculto
        document.getElementById('userAvatar').value = nombreImg;
    }

    // Función para seleccionar avatar en la sección Mi Perfil
    function seleccionarAvatarPerfil(nombreImg, elemento) {
        document.querySelectorAll('.avatar-opcion').forEach(img => img.classList.remove('selected'));
        elemento.classList.add('selected');
        document.getElementById('inputAvatarSeleccionado').value = nombreImg;
        // Vista previa inmediata
        document.getElementById('imgPerfilGrande').src = 'assets/avatares/' + nombreImg;
    }
</script>
<script src="admin.js"></script>

</body>
</html>