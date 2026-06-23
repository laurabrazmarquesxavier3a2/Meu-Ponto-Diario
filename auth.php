<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['id_usuario'])) {
    header('Location: /login.php');
    exit;
}