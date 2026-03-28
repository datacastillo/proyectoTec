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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | ISIC</title>

    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<div class="app-container">

    <aside class="sidebar" id="sidebar">

        <div class="brand-section">
            <img src="../Alumno/img/logoTec.png" alt="Logo TEC" class="logo-img" style="max-width: 120px;">
        </div>

        <div class="user-profile" style="text-align: center; margin-top: 20px; color: white;">
            <i class="fas fa-user-shield user-icon" style="font-size: 40px; color: #d4af37;"></i>
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
                <button class="btn-primary" onclick="abrirModal('ALUMNO')">+ Nuevo Alumno</button>
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
                <button class="btn-primary" onclick="abrirModal('DOCENTE')">+ Nuevo Docente</button>
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

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Nombre Completo</label>
                <input type="text" id="userName" required placeholder="Ej. Juan Castillo" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Correo Electrónico</label>
                <input type="email" id="userEmail" required placeholder="correo@ejemplo.com" style="width: 100%; padding: 10px; border-radius: 4px; border: none; background: white; color: black;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
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

<script src="admin.js"></script>

</body>
</html>