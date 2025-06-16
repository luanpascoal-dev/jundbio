<?php

function get_especies($no_desconhecido = true, $limit = null) {
    global $conn;
    $query = "SELECT *, COUNT(Id) as total FROM ESPECIE";
    if ($no_desconhecido) $query .= " WHERE NomeComum != 'Desconhecido'";
    $query .= " GROUP BY Id ORDER BY NomeComum";
    if ($limit) $query .= " LIMIT $limit";
    $stmt = $conn->prepare($query);
    $stmt->execute();   
    $result = $stmt->get_result();
    return $result;
}

function get_total_especies($no_desconhecido = true) {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM ESPECIE";
    if ($no_desconhecido) $query .= " WHERE NomeComum != 'Desconhecido'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc()['total'];
}

function get_especie($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM ESPECIE WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function get_especies_by_familia($familia) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM ESPECIE WHERE Familia = ?");
    $stmt->bind_param("s", $familia);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_especie_by_nome($nome) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM ESPECIE WHERE NomeComum = ?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_id_by_nome($nome) {
    global $conn;
    $stmt = $conn->prepare("SELECT Id FROM ESPECIE WHERE NomeComum = ?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['Id'];
}


function get_especies_mapa() {
    global $conn;
    $sql = "SELECT e.*, u.Nome as autor_nome, u.Id as autor_id, f.URL as Foto, l.Latitude, l.Longitude, f.DataHora_Foto as DataHora_Registro
                FROM ESPECIE e 
                JOIN FOTO f ON e.Id = f.Id_Especie
                JOIN POSTAGEM p ON f.Id_Postagem = p.Id
                JOIN USUARIO u ON p.Id_Usuario = u.Id
                JOIN LOCALIZACAO l ON f.Id_Localizacao = l.Id
                WHERE l.Latitude IS NOT NULL AND l.Longitude IS NOT NULL AND e.NomeComum != 'Desconhecido'
                GROUP BY e.Id
                ORDER BY e.NomeComum DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_especie_foto($id, $limit = 5) {
    global $conn;
    $stmt = $conn->prepare("SELECT URL FROM FOTO WHERE Id_Especie = ? LIMIT ?");
    $stmt->bind_param("ii", $id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}
