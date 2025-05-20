<?php

function get_nivel($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT Pontos FROM USUARIO WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $pontos = $row['Pontos'];
    return get_nivel_by_pontos($pontos);
}

function get_nivel_by_pontos($pontos) {
    $niveis = array(
        '0' => 'Iniciante',
        '50' => 'Aprendiz',
        '100' => 'Intermediário',
        '250' => 'Avançado',
        '500' => 'Experiente'
    );
    $n = $niveis[0];
    foreach ($niveis as $ponto => $nivel) {
        if ($pontos >= $ponto) {
            $n = $nivel;
        }
    }
    return $n;
}