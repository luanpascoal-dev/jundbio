<?php
session_start();

include 'database.php';

include 'functions/get_postagens.php';

include 'functions/get_nivel.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JundBio - Serra do Japi</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <div class="container">
                <h1>Bem-vindo ao JundBio</h1>
                <p class="subtitle">Compartilhe e descubra a biodiversidade da Serra do Japi</p>
            </div>
        </div>

        <div class="container">
            <div class="actions-bar">
                <a href="postar" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Postagem
                </a>
                <div class="filters">
                    <select class="filter-select" name="tipo">
                        <option value="recentes">Mais Recentes</option>
                        <option value="curtidas">Mais Curtidas</option>
                        <option value="comentarios">Mais Comentadas</option>
                    </select>
                </div>
            </div>

            <div class="postagens">
                <?php
                    $tipo = $_GET['tipo'] ?? 'recentes';
                    $postagens = get_postagens($tipo);

                    if($postagens->num_rows <= 0) {
                        echo '<div class="no-posts">
                                <i class="fas fa-leaf"></i>
                                <p>Nenhuma postagem encontrada</p>
                                <p class="text-light">Seja o primeiro a compartilhar um avistamento!</p>
                              </div>';
                    }

                    while($post = $postagens->fetch_assoc()) {
                ?>
                    <div class="postagem">
                        <div class="postagem-header">
                            <div class="user-info">
                            <?php if(isset($post['Foto_Usuario']) && !empty($post['Foto_Usuario'])): ?>
                                <img src="<?php echo htmlspecialchars($post['Foto_Usuario']); ?>" alt="Foto de perfil" class="user-avatar">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                                <div>
                                    <h3><?= htmlspecialchars($post['Nome_Usuario']) ?></h3>
                                    <span class="post-type"><?= get_nivel($post['Id_Usuario']) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="postagem-conteudo">
                            <p><?= htmlspecialchars($post['Texto']) ?></p>
                        </div>

                        <?php if($post['Foto']): ?>
                        <div class="postagem-imagem">
                            <img src="<?= htmlspecialchars($post['Foto']) ?>" alt="Imagem da postagem">
                        </div>
                        <?php endif; ?>

                        <div class="postagem-footer">
                            <div class="interactions">
                                <button class="btn btn-danger btn-sm like-btn" data-post-id="<?= $post['Id'] ?>">
                                    <i class="fa-solid fa-heart"></i>
                                    <span><?= $post['Curtidas'] ?></span>
                                </button>
                                <a href="comentar.php?id=<?= $post['Id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-comment-dots"></i>
                                    <span><?= $post['Comentarios'] ?></span>
                                </a>
                            </div>
                            <div class="post-meta">
                                <span class="post-date">
                                    <i class="far fa-clock"></i>
                                    <?= date('d/m/Y H:i', strtotime($post['DataHora_Envio'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php
                    }
                ?>
            </div>
        </div>
    </main>

    <?php include 'layouts/footer.php'; ?>

    <script>
        // Like button functionality
        document.querySelectorAll('.like-btn').forEach(button => {
            button.addEventListener('click', function() {
                const postId = this.dataset.postId;
                // Add your like functionality here
                this.classList.toggle('liked');
            });
        });
    </script>
</body>
</html>
