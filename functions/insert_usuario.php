<?php

function insert_usuario($nome, $email, $senha) {
    global $conn;

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO USUARIO (Nome, Email, Senha) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha_hash);

    if ($stmt->execute()) return true;

    return false;
}

function insert_usuario_admin($nome, $email, $senha) {
    global $conn;

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO USUARIO (Nome, Email, Senha, Tipo) VALUES (?, ?, ?, 'ADMIN')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nome, $email, $senha_hash);

    if ($stmt->execute()) return true;

    return false;
}