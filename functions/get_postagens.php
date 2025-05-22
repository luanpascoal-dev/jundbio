<?php

function get_postagens($tipo = 'recentes') {
    global $conn;
    $sql = "SELECT 
    p.*, 
    COUNT(DISTINCT c.Id) AS Comentarios, 
    COUNT(DISTINCT ct.Id_Usuario) AS Curtidas, 
    f.URL AS Foto, 
    u.Nome AS Nome_Usuario,
    u.Pontos AS Pontos,
    u.Foto AS Foto_Usuario
    FROM POSTAGEM p
    LEFT JOIN COMENTARIO c ON c.Id_Postagem = p.Id
    LEFT JOIN CURTIDA ct ON ct.Id_Postagem = p.Id
    LEFT JOIN FOTO f ON f.Id_Postagem = p.Id
    LEFT JOIN USUARIO u ON u.Id = p.Id_Usuario
    WHERE p.Status = 'APROVADO' AND u.Ativo = 1 AND p.Tipo = 'AVISTAMENTO'
    GROUP BY p.Id";

    if($tipo == 'recentes') {
        $sql .= " ORDER BY p.DataHora_Envio DESC";
    } else if($tipo == 'curtidas') {
        $sql .= " ORDER BY Curtidas DESC";
    } else if($tipo == 'comentarios') {
        $sql .= " ORDER BY Comentarios DESC";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_total_postagens() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM POSTAGEM");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function get_todas_postagens() {
    global $conn;
    $sql = "SELECT * FROM POSTAGEM";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result();
}

function get_postagens_by_usuario($usuario_id) {
    global $conn;
    $sql = "SELECT * FROM POSTAGEM WHERE Id_Usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result();
}

function get_postagens_by_especie($especie_id) {
    global $conn;
    $sql = "SELECT p.*, u.Nome as Nome_Usuario, u.Foto as Foto_Usuario, l.Latitude, l.Longitude
    FROM POSTAGEM p 
    JOIN USUARIO u ON p.Id_Usuario = u.Id 
    JOIN FOTO f ON f.Id_Postagem = p.Id 
    JOIN LOCALIZACAO l ON l.Id = f.Id_Localizacao
    WHERE f.Id_Especie = ? AND p.Status = 'APROVADO'
    ORDER BY p.DataHora_Envio DESC 
    LIMIT 6";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    return $stmt->get_result();
}


