<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "fashionstyle";

$con = new mysqli($host, $usuario, $senha, $banco);

if ($con->connect_error) {
    die("Erro na conexão: " . $con->connect_error);
}

$con->set_charset("utf8mb4");
