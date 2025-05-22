<?php

function get_avatar($id, $path = '') {
    global $conn;
    $sql = "SELECT Foto FROM USUARIO WHERE Id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $foto = $row['Foto'];
    return $path . $foto;
}

function get_especie_image($id, $path = '') {
    global $conn;

    $sql = "SELECT f.URL, COUNT(DISTINCT cur.Id_Usuario) as total_curtidas
            FROM ESPECIE e 
            JOIN FOTO f ON e.Id = f.Id_Especie 
            JOIN POSTAGEM p ON p.Id = f.Id_Postagem 
            LEFT JOIN CURTIDA cur ON p.Id = cur.Id_Postagem 
            WHERE e.Id = ? 
            GROUP BY f.Id, f.URL 
            ORDER BY total_curtidas DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if(!$row){
        return get_default_image();
    }
    $foto = $row['URL'];

    return $path . $foto;
}

function get_default_image() {
    return '<div class="no-image">
                <i class="fas fa-leaf"></i>
            </div>';
}

function get_default_avatar() {
    return '<div class="default-avatar">
                <i class="fas fa-user"></i>
            </div>';
}

