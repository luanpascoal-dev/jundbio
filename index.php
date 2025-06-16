<?php
session_start();

$css = ['index'];

include 'database.php';

include 'functions/get_postagens.php';

include 'functions/get_nivel.php';

include 'functions/get_curtidas.php';

include 'functions/get_usuario.php';

?>

<?php 
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>
<body>

    <main class="main-content">
        <div class="page-header">
            <div class="container">
                <h1>Bem-vindo ao JundBio</h1>
                <p class="subtitle">Compartilhe e descubra a biodiversidade da Serra do Japi</p>
            </div>
        </div>

        

        <div class="container">
            
            <?php include 'layouts/alerts.php'; ?>

            <div class="actions-bar">
                <a href="postar" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Postagem
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar postagens...">
                </div>
                <div class="filters">
                    <select class="filter-select" id="tipoFilter" name="tipo">
                        <option value="recentes">Mais Recentes</option>
                        <option value="curtidas">Mais Curtidas</option>
                        <option value="comentarios">Mais Comentadas</option>
                    </select>
                </div>
            </div>

            
                <?php
                    $tipo = $_GET['tipo'] ?? 'recentes';
                    $postagens = get_postagens($tipo);

                    if($postagens->num_rows <= 0): ?>
                        <div class="no-results">
                                <i class="fas fa-leaf"></i>
                                <h3>Nenhuma postagem encontrada</h3>
                                <p class="text-light">Seja o primeiro a compartilhar um avistamento!</p>
                              </div>
                    <?php
                    else:
                        ?>
                        <div class="postagens">
                        <?php
                            while($post = $postagens->fetch_assoc()):
                        ?>
                
                    <div class="postagem">
                        <div class="postagem-header">
                            <div class="user-info">
                                <a href="perfil?id=<?= $post['Id_Usuario'] ?>" class="user-link">
                                    <?php if(isset($post['Foto_Usuario']) && !empty($post['Foto_Usuario']) && file_exists($post['Foto_Usuario'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['Foto_Usuario']); ?>" alt="Foto de perfil" class="user-avatar">
                                    <?php else: ?>
                                        <div class="default-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h3>
                                            <?= htmlspecialchars($post['Nome_Usuario']) ?>
                                            <?php if(is_especialista($post['Id_Usuario'])): ?>
                                                <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                                            <?php endif; ?>
                                        </h3>
                                        <span class="post-type"><?= get_nivel($post['Id_Usuario']) ?></span>
                                    </div>
                                </a>
                            </div>
                        </div>
                        
                        <div class="postagem-conteudo">
                            <p><?= htmlspecialchars(substr($post['Texto'], 0, 200)) . (strlen($post['Texto']) > 100 ? '...' : '') ?></p>
                        </div>

                        <div class="postagem-imagem">
                        <?php if($post['Foto'] && file_exists($post['Foto'])): ?>
                            <a href="verpost?id=<?= $post['Id'] ?>">
                                <img src="<?= htmlspecialchars($post['Foto']) ?>" alt="Imagem da postagem">
                            </a>
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-leaf"></i>
                            </div>
                        <?php endif; ?>
                        </div>
                        
                        <div class="postagem-footer">
                            <div class="interactions">
                                <button onclick="curtirPost(<?php echo $post['Id']; ?>)" class="like-btn btn-sm <?php echo isset($_SESSION['id']) && has_curtida($_SESSION['id'], $post['Id']) ? 'liked' : ''; ?>" data-post-id="<?= $post['Id'] ?>">
                                <i class="<?php echo isset($_SESSION['id']) && has_curtida($_SESSION['id'], $post['Id']) ? 'fa-solid' : 'fa-regular'; ?> fa-heart" data-icon="<?php echo $post['Id']; ?>"></i>
                                <span data-count-id="<?php echo $post['Id']; ?>"><?php echo $post['Curtidas']; ?></span>
                                </button>
                                <a href="verpost?id=<?= $post['Id'] ?>" class="btn btn-primary btn-sm comment-btn">
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
                    endwhile;
                    ?>
                </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </main>

    <?php include 'layouts/footer.php'; ?>

    <script src="js/curtir.js"></script>
    <script>
        // Filtros e busca
        const searchInput = document.getElementById('searchInput');
        const tipoFilter = document.getElementById('tipoFilter');
        const postagens = document.querySelectorAll('.postagem');

        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase();
            const tipoValue = tipoFilter.value.toLowerCase();

            postagens.forEach(postagem => {
                const texto = postagem.querySelector('.postagem-conteudo p').textContent.toLowerCase();

                const matchesSearch = texto.includes(searchTerm);
                const matchesTipo = !tipoValue || true;//tipo === tipoValue;

                postagem.style.display = matchesSearch && matchesTipo ? 'flex' : 'none';
            });
        }

        searchInput.addEventListener('input', filterCards);
        tipoFilter.addEventListener('change', filterCards);
    </script>
</body>
</html>
