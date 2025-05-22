<?php

function get_comentarios() {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM COMENTARIO");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_total_comentarios() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM COMENTARIO");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function get_comentario($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM COMENTARIO WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->fetch_assoc();
    return $result;
}

function get_comentarios_by_post($postagem_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM COMENTARIO WHERE Id_Postagem = ?");
    $stmt->bind_param("i", $postagem_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->num_rows;
}

function get_comentarios_by_usuario($usuario_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM COMENTARIO WHERE Id_Usuario = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->num_rows;
}

function get_comentarios_by_especie($especie_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT c.*, COUNT(c.Id) as total FROM COMENTARIO c
    LEFT JOIN POSTAGEM p ON p.Id = c.Id_Postagem
    LEFT JOIN FOTO f ON f.Id_Postagem = p.Id
    LEFT JOIN ESPECIE e ON e.Id = f.Id_Especie
    GROUP BY c.Id WHERE e.Id = ?");
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result;
}


