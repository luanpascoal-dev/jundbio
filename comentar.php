<?php
session_start();

include 'functions/is_logado.php';

include 'database.php';


// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_id = (int) $_POST['post_id'];
    $comentario = trim($_POST['comentario']);
    $usuario_id = $_SESSION['id'];

    // Validar dados
    if (empty($comentario)) {
        $_SESSION['erro'] = "O comentário não pode estar vazio.";
        header("Location: post?id=$post_id");
        exit;
    }

    // Inserir comentário
    $stmt = $conn->prepare("INSERT INTO COMENTARIO (Id_Postagem, Id_Usuario, Texto, DataHora) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $usuario_id, $comentario);

    if ($stmt->execute()) {
        $_SESSION['sucesso'] = "Comentário publicado com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao publicar comentário. Tente novamente.";
    }

    header("Location: post?id=$post_id");
    exit;
}

// Se não for POST, redirecionar para a página inicial
header('Location: ./');
exit; 