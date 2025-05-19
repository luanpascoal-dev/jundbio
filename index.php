<?php
session_start();

include 'functions/is_logado.php';

include 'database.php';

include 'functions/get_postagens.php';


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <title>JundBios</title>

  <link rel="stylesheet" href="css/main.css">
</head>
    <?php include 'layouts/header.php'; ?>
<body>
  

    <div class="container">
        <textarea name="postagem" id="postagem" placeholder="O que você está pensando?"></textarea>
        <button>Publicar</button>
    </div>

    <div class="postagens">
        <?php
            $postagens = get_postagens($conn);


            if($postagens->num_rows <= 0) {
                echo "<p>Sem postagens!</p>";
            }

            while($post = $postagens->fetch_assoc()) {

            ?>
            <div class="postagem">
                <div class="postagem-header">
                    <h3><?= $post['Nome_Usuario'] ?></h3>
                </div>
                <div class="postagem-conteudo">
                    <p><?= $post['Texto'] ?></p>
                </div>
                <div class="postagem-imagem">
                    <img src="<?= $post['Foto'] ?>" alt="postagem">
                </div>
                <div class="postagem-footer">
                    <a href="#" class="btn btn-danger btn-sm">
                        <?= $post['Curtidas'] ?> <i class="fa-solid fa-heart"></i>
                    </a>
                    <a href="comentar?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                        <?= $post['Comentarios'] ?> <i class="fa-solid fa-comment-dots"></i>
                    </a>
                    <p><?= date('d/m/Y H:i', strtotime($post['DataHora_Envio'])) ?></p>
                </div>
            </div>
            <?php

            }
        ?>
    </div>
</body>
</html>
