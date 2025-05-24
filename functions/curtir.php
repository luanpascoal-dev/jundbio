<?php
session_start();

include '../database.php';

include 'is_logado.php';

include 'get_curtidas.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = (int) $_POST['post_id'];
    $usuario_id = $_SESSION['id'];
    
    try {
        if (has_curtida($usuario_id, $post_id)) {
            descurtir_postagem($post_id, $usuario_id);
            $acao = 'descurtir';
        } else {
            curtir_postagem($post_id, $usuario_id);
            $acao = 'curtir';
        }
        
        $total = get_curtidas_postagem($post_id);
        
        echo json_encode([
            'sucesso' => true,
            'acao' => $acao,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao processar curtida: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Requisição inválida'
    ]);
}

