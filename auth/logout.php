<?php
session_start();
// Destruimos todas las variables de sesión
session_unset();
// Destruimos la sesión en el servidor
session_destroy();

// Redirigimos al Login inmediatamente
header("Location: login.html");
exit();
?>