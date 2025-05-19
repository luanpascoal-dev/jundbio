<?php

function get_postagens($conn) {
    $stmt = $conn->prepare("SELECT 
    p.*, 
    COUNT(DISTINCT c.Id) AS Comentarios, 
    COUNT(DISTINCT ct.Id_Usuario) AS Curtidas, 
    f.URL AS Foto, 
    u.Nome AS Nome_Usuario
    FROM POSTAGEM p
    LEFT JOIN COMENTARIO c ON c.Id_Postagem = p.Id
    LEFT JOIN CURTIDA ct ON ct.Id_Postagem = p.Id
    LEFT JOIN FOTO f ON f.Id_Postagem = p.Id
    LEFT JOIN USUARIO u ON u.Id = p.Id_Usuario
    WHERE p.Status = 'APROVADO'
    GROUP BY p.Id");

    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}