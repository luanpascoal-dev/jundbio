<?php
session_start();

$title = "Postagem";
$css = ['verpost'];

include 'database.php';

include 'functions/get_usuario.php';
include 'functions/get_nivel.php';
include 'functions/get_curtidas.php';
include 'functions/get_especie.php';

// Verificar se o ID da postagem foi fornecido
if (!isset($_GET['id'])) {
    header('Location: ./');
    exit;
}

$post_id = (int)$_GET['id'];

// Buscar detalhes da postagem
$stmt = $conn->prepare("
    SELECT p.*, u.Nome as Nome_Usuario, u.Foto as Foto_Usuario, u.Id as Id_Usuario, f.Id_Especie,
           COUNT(DISTINCT c.Id) as Comentarios,
           COUNT(DISTINCT cur.Id_Usuario) as Curtidas,
           l.Latitude, l.Longitude, l.Descricao as Localizacao_Descricao
    FROM POSTAGEM p
    JOIN USUARIO u ON p.Id_Usuario = u.Id
    LEFT JOIN FOTO f ON p.Id = f.Id_Postagem
    LEFT JOIN LOCALIZACAO l ON f.Id_Localizacao = l.Id
    LEFT JOIN COMENTARIO c ON p.Id = c.Id_Postagem
    LEFT JOIN CURTIDA cur ON p.Id = cur.Id_Postagem
    WHERE p.Id = ?
    GROUP BY p.Id
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

// Se a postagem não existir, redirecionar
if (!$post) {
    header('Location: ./');
    exit;
}

// Buscar fotos da postagem
$stmt = $conn->prepare("SELECT * FROM FOTO WHERE Id_Postagem = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$fotos = $stmt->get_result();

// Buscar comentários
$stmt = $conn->prepare("
    SELECT c.*, u.Nome as Nome_Usuario, u.Foto as Foto_Usuario
    FROM COMENTARIO c
    JOIN USUARIO u ON c.Id_Usuario = u.Id
    WHERE c.Id_Postagem = ?
    ORDER BY c.DataHora DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comentarios = $stmt->get_result();


$linhas = preg_split('/<br\s*\/?>/i', nl2br(htmlspecialchars($post['Texto'])));

$totalLinhas = count($linhas);

$especie = get_especie($post['Id_Especie']);


?>


<?php 
$title = htmlspecialchars($post['Texto']);
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>

    <div class="container">
        <div class="post-container">
            <div class="back-button">
                <a href="./" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            <?php include 'layouts/alerts.php'; ?>

            <div class="post-grid">
                <!-- Coluna da Postagem -->
                <div class="post-main">
                    <div class="post">

                        <div class="post-header">
                            <div class="user-info">
                                <?php if(isset($post['Foto_Usuario']) && !empty($post['Foto_Usuario']) && file_exists($post['Foto_Usuario'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['Foto_Usuario']); ?>" alt="Foto de perfil" class="user-avatar lg">
                                <?php else: ?>
                                    <div class="default-avatar lg">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3>
                                        <?php echo htmlspecialchars($post['Nome_Usuario']); ?>
                                        <?php if(is_especialista($post['Id_Usuario'])): ?>
                                            <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <span class="post-type"><?php echo get_nivel($post['Id_Usuario']); ?></span>
                                </div>
                            </div>
                            <div class="post-meta">
                                <div class="post-actions">
                                    <button onclick="curtirPost(<?php echo $post['Id']; ?>)" class="like-btn btn-sm <?php echo has_curtida($_SESSION['id'], $post['Id']) ? 'liked' : ''; ?>" data-post-id="<?php echo $post['Id']; ?>">
                                        <i class="<?php echo has_curtida($_SESSION['id'], $post['Id']) ? 'fa-solid' : 'fa-regular'; ?> fa-heart" data-icon="<?php echo $post['Id']; ?>"></i>
                                        <span data-count-id="<?php echo $post['Id']; ?>"><?php echo $post['Curtidas']; ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="post-content">
                            <p>
                                <?php 
                                    for($i = 0; $i < $totalLinhas && $i <= 10; $i++):
                                        echo nl2br(htmlspecialchars($linhas[$i]));
                                    endfor;
                                    
                                    ?>
                                    <div class="more-content" style="display: none;">
                                        <?php
                                        for($i = 10; $i < $totalLinhas; $i++):
                                            echo nl2br(htmlspecialchars($linhas[$i]));
                                        endfor;
                                        ?>
                                    </div>
                                </p>
                            <?php if($totalLinhas >= 10): ?>
                                    <button class="btn" onclick="verMais(this)">Ver mais</button>
                            <?php endif; ?>
                        </div>



                        <?php if($fotos->num_rows > 0): ?>
                            <div class="post-images">
                                <?php while($foto = $fotos->fetch_assoc()): ?>
                                    <?php if(file_exists($foto['URL'])): ?>
                                        <img src="<?php echo htmlspecialchars($foto['URL']); ?>" alt="Foto da postagem">
                                    <?php endif; ?>
                                <?php endwhile; ?>
                                <div class="post-meta">
                                    <span class="post-date">
                                        <i class="far fa-clock"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($post['DataHora_Envio'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if(isset($post['Latitude']) && isset($post['Longitude'])): ?>
                        
                            <div class="post-map">
                                <h3 class="title-3">Localização</h3>
                                <div id="mapa" class="map-container"></div>
                            </div>
                        
                    <?php endif; ?>
                </div>

                <!-- Coluna dos Comentários -->

                <div class="post-sidebar">

                    
                        <h3 class="title-3">Espécie</h3>
                        <!-- <div class="especie-info"> -->
                            <div class="especie-details">
                                <h3><?= $especie['NomeComum'] ? htmlspecialchars($especie['NomeComum']) : "Desconhecido" ?></h3>
                                <p class="nome-cientifico"><?= $especie['NomeCientifico'] ? htmlspecialchars($especie['NomeCientifico']) : "Sem informação" ?></p>
                                <?php if(isset($especie['StatusExtincao'])): ?>
                                    <span class="status-badge <?= strtolower(str_replace(' ', '-', $especie['StatusExtincao'])) ?>">
                                        <?= $especie['StatusExtincao'] ? htmlspecialchars($especie['StatusExtincao']) : "Desconhecido" ?>
                                    </span>
                                <?php endif; ?>
                                
                                <div class="especie-meta">
                                    <p class="classificacao">
                                        <i class="fas fa-tag"></i>
                                        <?= $especie['Classificacao'] ? htmlspecialchars($especie['Classificacao']) : "Nenhum" ?>
                                    </p>
                                    
                                        <p class="familia">
                                            <i class="fas fa-sitemap"></i>
                                            <?= $especie['Familia'] ? htmlspecialchars($especie['Familia']) : "Nenhum" ?>
                                    </p>
                                </div>

                                <p class="descricao">
                                    <?= $especie['Descricao'] ? htmlspecialchars(substr($especie['Descricao'], 0, 150)) : "Sem Descrição" ?>...
                                </p>
                                
                                <?php if($especie['Id'] != 1): ?>
                                    <a href="verespecie?id=<?= $especie['Id'] ?>" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-info-circle"></i>
                                        Ver detalhes
                                    </a>
                                <?php endif; ?>
                                
                            </div>
                            
                        <!-- </div> -->
                    




                    <div class="comments-section">
                        <h3>Comentários</h3>
                        
                        <?php if(isset($_SESSION['id'])): ?>
                            <form method="POST" action="functions/comentar.php" class="comment-form">
                                <input type="hidden" name="post_id" value="<?php echo $post['Id']; ?>">
                                <textarea name="comentario" placeholder="Escreva um comentário..." maxlength="1024" required></textarea>
                                <button type="submit" class="btn btn-secondary">
                                    <i class="fas fa-paper-plane"></i> Comentar
                                </button>
                            </form>
                            
                        <?php endif; ?>

                        <div class="comments-list">
                            <?php if($comentarios->num_rows > 0): ?>
                                <?php while($comentario = $comentarios->fetch_assoc()): ?>
                                    <div class="comment">
                                        <div class="comment-header">
                                            <div class="user-info">
                                                <?php if(isset($comentario['Foto_Usuario']) && !empty($comentario['Foto_Usuario']) && file_exists($comentario['Foto_Usuario'])): ?>
                                                    <img src="<?php echo htmlspecialchars($comentario['Foto_Usuario']); ?>" alt="Foto de perfil" class="user-avatar">
                                                <?php else: ?>
                                                    <div class="default-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <h4>
                                                        <?php echo htmlspecialchars($comentario['Nome_Usuario']); ?>
                                                        <?php if(is_especialista($comentario['Id_Usuario'])): ?>
                                                            <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <span class="comment-date">
                                                        <?php echo date('d/m/Y H:i', strtotime($comentario['DataHora'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <p>
                                                <?php 
                                                $lines = preg_split('/<br\s*\/?>/i', nl2br(htmlspecialchars($comentario['Texto'])));

                                                $totalLines = count($lines);

                                                for($i = 0; $i < $totalLines && $i < 5; $i++):
                                                    echo nl2br(htmlspecialchars($lines[$i]));
                                                endfor;
                                                
                                                ?>
                                                <div class="more-content" data-id="<?php echo $comentario['Id']; ?>" style="display: none;">
                                                    <?php
                                                    for($i = 5; $i < $totalLines; $i++):
                                                        echo nl2br(htmlspecialchars($lines[$i]));
                                                    endfor;
                                                    ?>
                                                </div>
                                            </p>
                                            <?php if($totalLines >= 5): ?>
                                                    <button class="btn" onclick="verMais(this, <?php echo $comentario['Id']; ?>)">Ver mais</button>
                                            <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="no-comments">
                                    <i class="far fa-comment-dots"></i>
                                    <p>Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/curtir.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function verMais(btn, id = null) {
                const more = document.querySelector(`.more-content${id ? `[data-id="${id}"]` : ''}`);
                if (more.style.display === 'none') {
                    more.style.display = 'block';
                    btn.textContent = 'Ver menos';
                } else {
                    more.style.display = 'none';
                    btn.textContent = 'Ver mais';
                }
            }

        // Inicializar mapa centralizado na Serra do Japi
        const mapa = L.map('mapa').setView([-23.2369, -46.9376], 12);

        // Adiciona o layer do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(mapa);

        <?php
        if(isset($post['Latitude']) && isset($post['Longitude'])):
        ?>
            L.marker([<?= $post['Latitude'] ?>, <?= $post['Longitude'] ?>])
                .addTo(mapa);
            
            mapa.setView([<?= $post['Latitude'] ?>, <?= $post['Longitude'] ?>], 14);
        <?php 
        
        endif;
            
        ?>

        // Adicionar controle de escala
        L.control.scale({
            position: 'bottomleft',
            metric: true,
            imperial: false
        }).addTo(mapa);

    </script>
</body>
</html> 