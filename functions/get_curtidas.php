<?php


function curtir_postagem($post_id, $usuario_id) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO CURTIDA (Id_Usuario, Id_Postagem) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $post_id);
    return $stmt->execute();
}

function descurtir_postagem($post_id, $usuario_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM CURTIDA WHERE Id_Usuario = ? AND Id_Postagem = ?");
    $stmt->bind_param("ii", $usuario_id, $post_id);
    return $stmt->execute();
}

function get_curtidas_postagem($post_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM CURTIDA WHERE Id_Postagem = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

function get_curtidas_usuario($id_usuario) {
    global $conn;

    $sql = "SELECT COUNT(*) as total FROM CURTIDA WHERE Id_Usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function has_curtida($usuario_id, $post_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM CURTIDA WHERE Id_Usuario = ? AND Id_Postagem = ?");
    $stmt->bind_param("ii", $usuario_id, $post_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}


function get_curtidas_postagens_by_usuario($usuario_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM CURTIDA c
    JOIN POSTAGEM p ON c.Id_Postagem = p.Id 
    WHERE p.Id_Usuario = ?
    ");
    
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}