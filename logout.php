<?php
session_start();
session_destroy();

// redireciona pro login
header("Location: login.html");
exit;