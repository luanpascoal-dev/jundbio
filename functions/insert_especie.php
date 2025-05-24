<?php

function insert_especie($nome_comum, $nome_cientifico, $familia, $classificacao, $descricao, $status_extincao, $tipo) {
    global $conn;

    $sql = "INSERT INTO ESPECIE (NomeComum, NomeCientifico, Familia, Classificacao, Descricao, StatusExtincao, Tipo) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $nome_comum, $nome_cientifico, $familia, $classificacao, $descricao, $status_extincao, $tipo);

    if ($stmt->execute()) return true;

    return false;
}
            
