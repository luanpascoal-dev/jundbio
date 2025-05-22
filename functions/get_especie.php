<?php

function get_especies() {
    global $conn;
    $stmt = $conn->prepare("SELECT *, COUNT(Id) as total FROM ESPECIE WHERE NomeComum != 'Desconhecido' GROUP BY Id ORDER BY NomeComum");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

function get_total_especies() {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM ESPECIE WHERE NomeComum != 'Desconhecido'");
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
