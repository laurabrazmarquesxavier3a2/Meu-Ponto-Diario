<?php

require_once __DIR__ . '/config.php';

$con = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASS,
    DB_NAME,
    (int) $_ENV['MYSQLPORT']
);

if ($con->connect_error) {
    die('Erro na conexão: ' . $con->connect_error);
}

$con->set_charset("utf8");
