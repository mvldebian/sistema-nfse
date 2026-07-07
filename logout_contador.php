<?php
session_start();
unset($_SESSION['contador_id'], $_SESSION['contador_nome'], $_SESSION['contador_pasta'], $_SESSION['contador_usuario_id']);
header('Location: contador_login.php');
exit;
?>
