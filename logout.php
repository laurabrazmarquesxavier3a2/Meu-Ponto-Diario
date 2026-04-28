<?php
session_start();
session_destroy();

// opcional: apagar cookies
setcookie(session_name(), '', time() - 3600);

// redireciona pro login
header("Location: login.html");
exit;