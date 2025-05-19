<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$database = "jundbio";

$conn = new mysqli($host, $usuario, $senha, $database);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
