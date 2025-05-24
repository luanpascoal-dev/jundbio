<?php

function remove_avatar($id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE USUARIO SET Foto = NULL WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function update_usuario($id, $nome, $email, $senha, $foto, $biografia, $ocupacao) {
    global $conn;
    $stmt = $conn->prepare("UPDATE USUARIO SET Nome = ?, Email = ?, Senha = ?, Foto = ?, Biografia = ?, Ocupacao = ? WHERE Id = ?");
    $stmt->bind_param("ssssssi", $nome, $email, $senha, $foto, $biografia, $ocupacao, $id);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}

function update_usuario_sem_senha($id, $nome, $email, $foto, $biografia, $ocupacao) {
    global $conn;
    $stmt = $conn->prepare("UPDATE USUARIO SET Nome = ?, Email = ?, Foto = ?, Biografia = ?, Ocupacao = ? WHERE Id = ?");
    $stmt->bind_param("sssssi", $nome, $email, $foto, $biografia, $ocupacao, $id);
    $stmt->execute();
    return $stmt->affected_rows > 0;
}
