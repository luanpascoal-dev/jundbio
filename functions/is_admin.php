<?php

$stmt = $conn->prepare("SELECT Tipo FROM USUARIO WHERE id = ? AND Tipo = 'ADMIN'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: ../");
    exit();
}