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

function update_usuario_admin($id, $dados, $nova_senha_hash = null, $nova_foto_path = null, $remover_foto_atual = false, $foto_antiga_path = null) {
    global $conn;

    $campos = [];
    $params = [];
    $tipos = "";

    if (isset($dados['nome'])) { $campos[] = "Nome = ?"; $params[] = $dados['nome']; $tipos .= "s"; }
    if (isset($dados['email'])) { $campos[] = "Email = ?"; $params[] = $dados['email']; $tipos .= "s"; }
    if (isset($dados['biografia'])) { $campos[] = "Biografia = ?"; $params[] = $dados['biografia']; $tipos .= "s"; }
    if (isset($dados['ocupacao'])) { $campos[] = "Ocupacao = ?"; $params[] = $dados['ocupacao']; $tipos .= "s"; }
    if (isset($dados['tipo']) && ($id != 1 || $dados['tipo'] == 'ADMIN')) { // Não permite alterar tipo do ID 1 para não-admin
        $campos[] = "Tipo = ?"; $params[] = $dados['tipo']; $tipos .= "s";
    }
    if (isset($dados['ativo']) && $id != 1) { // Não permite desativar ID 1
        $campos[] = "Ativo = ?"; $params[] = $dados['ativo']; $tipos .= "i";
    }
     if (isset($dados['pontos']) && is_numeric($dados['pontos'])) {
        $campos[] = "Pontos = ?"; $params[] = (int)$dados['pontos']; $tipos .= "i";
    }

    if ($nova_senha_hash) {
        $campos[] = "Senha = ?"; $params[] = $nova_senha_hash; $tipos .= "s";
    }

    if ($nova_foto_path !== null) { // Se uma nova foto foi definida (pode ser string vazia para remover ou novo caminho)
        $campos[] = "Foto = ?";
        $params[] = $nova_foto_path === '' ? null : $nova_foto_path; // Se string vazia, define como NULL
        $tipos .= "s";
        if ($remover_foto_atual && !empty($foto_antiga_path) && file_exists($foto_antiga_path)) {
            unlink($foto_antiga_path);
        }
    } elseif ($remover_foto_atual) { // Se apenas remoção foi solicitada sem nova foto
         $campos[] = "Foto = NULL";
         // Não precisa de param para NULL direto na query
         if (!empty($foto_antiga_path) && file_exists($foto_antiga_path)) {
            unlink($foto_antiga_path);
        }
    }


    if (empty($campos)) {
        return true; // Nenhuma alteração a ser feita
    }

    $sql = "UPDATE USUARIO SET " . implode(", ", $campos) . " WHERE Id = ?";
    $params[] = $id;
    $tipos .= "i";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Erro de preparação SQL: " . $conn->error);
        return false;
    }
    $stmt->bind_param($tipos, ...$params);
    $success = $stmt->execute();
    if (!$success) {
        error_log("Erro de execução SQL: " . $stmt->error);
    }
    $stmt->close();
    return $success;
}