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

function get_niveis() {
    return array(
        '0' => 'Iniciante',
        '50' => 'Aprendiz',
        '100' => 'Intermediário',
        '250' => 'Avançado',
        '500' => 'Experiente',
        '1000' => 'Mestre'
    );
}

function get_nivel_by_pontos($pontos) {
    $niveis = get_niveis();
    $n = $niveis[0];
    foreach ($niveis as $ponto => $nivel) {
        if ($pontos >= $ponto) {
            $n = $nivel;
        }
    }
    return $n;
}

function get_pontos_by_nivel($nivel) {
    $pontos = array_search($nivel, get_niveis());
    return $pontos;
}

function get_proximo_nivel($pontos) {
    $n = get_nivel_by_pontos($pontos);
    $niveis = array_values(get_niveis());
    $nivel_atual = array_search($n, $niveis);
    if(count($niveis) > $nivel_atual + 1) 
        return $niveis[$nivel_atual + 1];
    else 
        return $n;
}

function get_nivel_cor($id) {
    global $conn;
    
    // Buscar pontos do usuário
    $stmt = $conn->prepare("SELECT Pontos FROM USUARIO WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    $pontos = $usuario['Pontos'];
    
    // Definir cores para cada nível
    if ($pontos >= 1000) {
        return '#FFD700'; // Dourado para nível mais alto
    } elseif ($pontos >= 500) {
        return '#C0C0C0'; // Prata
    } elseif ($pontos >= 250) {
        return '#CD7F32'; // Bronze
    } elseif ($pontos >= 100) {
        return '#4CAF50'; // Verde
    } elseif ($pontos >= 50) {
        return '#FFA500'; // Laranja
    } else {
        return '#2196F3'; // Azul para iniciantes
    }
}