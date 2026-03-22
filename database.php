<?php
//requer que haja o arquivo config.php configurado
require_once 'config.php';

$con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if(!$con){
    echo 'Falha na conexão com banco:'.$con->error;
}