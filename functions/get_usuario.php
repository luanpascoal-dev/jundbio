<?php

function get_usuario($conn, $usuario) {
    $stmt = $conn->prepare("SELECT Id FROM USUARIO WHERE Nome = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}