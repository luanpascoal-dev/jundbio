<?php

function get_usuarios() {
    global $conn;
    $stmt = $conn->prepare("SELECT *, COUNT(Id) as total FROM USUARIO GROUP BY Id ORDER BY Id");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_total_usuarios() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM USUARIO");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function get_usuario($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM USUARIO WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_usuario_by_email($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM USUARIO WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_id_by_email($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT Id FROM USUARIO WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['Id'];
}

function get_pontos_by_id($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT Pontos FROM USUARIO WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['Pontos'];
}

function get_fotos_by_id($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
    FROM FOTO f 
    JOIN POSTAGEM p ON f.Id_Postagem = p.Id 
    WHERE p.Id_Usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function get_comentarios_by_id($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total 
    FROM COMENTARIO c 
    JOIN POSTAGEM p ON c.Id_Postagem = p.Id 
    WHERE p.Id_Usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function is_especialista($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT Id_Usuario FROM ESPECIALISTA WHERE Id_Usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

