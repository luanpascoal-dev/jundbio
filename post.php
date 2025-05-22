<?php
session_start();
include 'database.php';
include 'functions/is_logado.php';
include 'functions/get_usuario.php';
include 'functions/get_nivel.php';
include 'functions/curtida.php';

// Verificar se o ID da postagem foi fornecido
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Buscar detalhes da postagem
$stmt = $conn->prepare("
    SELECT p.*, u.Nome as Nome_Usuario, u.Foto as Foto_Usuario, u.Id as Id_Usuario,
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
    header('Location: index.php');
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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['Texto']); ?> - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="post-container">
            <div class="back-button">
                <a href="./" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i>
                </a>
            </div>

            <div class="post-grid">
                <!-- Coluna da Postagem -->
                <div class="post-main">
                    <div class="post">

                        <div class="post-header">
                            <div class="user-info">
                                <?php if(isset($post['Foto_Usuario']) && !empty($post['Foto_Usuario']) && file_exists($post['Foto_Usuario'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['Foto_Usuario']); ?>" alt="Foto de perfil" class="user-avatar">
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h3><?php echo htmlspecialchars($post['Nome_Usuario']); ?></h3>
                                    <span class="post-type"><?php echo get_nivel($post['Id_Usuario']); ?></span>
                                </div>
                            </div>
                            <div class="post-meta">
                                <div class="post-actions">
                                    <button onclick="curtirPost(<?php echo $post['Id']; ?>)" class="btn btn-danger btn-sm <?php echo has_curtida($_SESSION['id'], $post['Id']) ? '' : 'like-btn'; ?>" data-post-id="<?php echo $post['Id']; ?>">
                                        <i class="<?php echo has_curtida($_SESSION['id'], $post['Id']) ? 'fa-solid' : 'fa-regular'; ?> fa-heart" data-icon="<?php echo $post['Id']; ?>"></i>
                                        <span data-count-id="<?php echo $post['Id']; ?>"><?php echo $post['Curtidas']; ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="post-content">
                            <p><?php echo nl2br(htmlspecialchars($post['Texto'])); ?></p>
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
                </div>

                <!-- Coluna dos Comentários -->
                
                <?php if(isset($post['Latitude']) && isset($post['Longitude'])): ?>
                    <div class="post-sidebar">
                        <div class="post-map">
                            <h3>Localização</h3>
                            <div id="map" class="map-container"></div>
                        </div>
                    </div>
                <?php endif; ?>
                


                <div class="post-sidebar">
                    <div class="comments-section">
                        <h3>Comentários</h3>
                        
                        <?php if(isset($_SESSION['id'])): ?>
                            <form method="POST" action="comentar.php" class="comment-form">
                                <input type="hidden" name="post_id" value="<?php echo $post['Id']; ?>">
                                <textarea name="comentario" placeholder="Escreva um comentário..." maxlength="1024" required></textarea>
                                <button type="submit" class="btn btn-primary">
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
                                                    <h4><?php echo htmlspecialchars($comentario['Nome_Usuario']); ?></h4>
                                                    <span class="comment-date">
                                                        <?php echo date('d/m/Y H:i', strtotime($comentario['DataHora'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <p><?php echo nl2br(htmlspecialchars($comentario['Texto'])); ?></p>
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

    <style>
    .post-container {
        max-width: 1200px;
        margin: 2rem auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .back-button {
        padding: 1rem 2rem;
        border-bottom: 1px solid var(--border-color);
        background: var(--bg-light);
    }

    .back-button .btn-light {
        width: 40px;
        height: 40px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: white;
        color: var(--primary-green);
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .back-button .btn-light:hover {
        background: var(--primary-green);
        color: white;
        transform: translateX(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .post-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        min-height: calc(100vh - 200px);
    }

    .post-main {
        min-width: 0;
        padding: 2rem;
        border-right: 1px solid var(--border-color);
    }

    .post-sidebar {
        padding: 2rem;
    }

    .post {
        background: white;
        border-radius: 8px;
    }

    .post-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .post-actions {
        margin-bottom: 1.5rem;
    }

    .post-actions .btn-link {
        font-size: 1.2rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background: var(--bg-light);
        transition: all 0.3s ease;
    }

    .post-actions .btn-link:hover {
        background: var(--primary-green);
        color: white;
    }

    .post-actions .btn-link.liked {
        background: var(--danger-color);
        color: white;
    }

    .post-actions .btn-link.liked:hover {
        background: var(--danger-color);
        opacity: 0.9;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
    }

    .default-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--primary-green);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .post-content {
        margin-bottom: 1.5rem;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    .post-images {
        display: grid;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .post-images img {
        width: 100%;
        border-radius: 8px;
        object-fit: cover;
    }

    .comments-section {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .comments-section h3 {
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        color: var(--primary-green);
    }

    .comment-form {
        margin-bottom: 2rem;
    }

    .comment-form textarea {
        width: 100%;
        min-height: 100px;
        padding: 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 1rem;
        resize: vertical;
        font-size: 1rem;
        background-color: var(--bg-light);
    }

    .comments-list {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding-right: 1rem;
    }

    .comments-list::-webkit-scrollbar {
        width: 6px;
    }

    .comments-list::-webkit-scrollbar-track {
        background: var(--bg-light);
        border-radius: 3px;
    }

    .comments-list::-webkit-scrollbar-thumb {
        background: var(--primary-green);
        border-radius: 3px;
    }

    .comment {
        background: var(--bg-light);
        padding: 1rem;
        border-radius: 8px;
    }

    .comment-header {
        margin-bottom: 0.5rem;
    }

    .comment .user-info {
        gap: 0.5rem;
    }

    .comment .user-avatar,
    .comment .default-avatar {
        width: 32px;
        height: 32px;
        font-size: 1rem;
    }

    .comment-date {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .no-comments {
        text-align: center;
        padding: 2rem;
        color: var(--text-light);
    }

    .no-comments i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    .post-type {
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .post-map {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .post-map h3 {
        margin-bottom: 1rem;
        color: var(--primary-green);
        font-size: 1.2rem;
    }

    .map-container {
        width: 100%;
        height: 300px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    @media (max-width: 1024px) {
        .post-grid {
            grid-template-columns: 1fr;
        }

        .post-main {
            border-right: none;
            border-bottom: 1px solid var(--border-color);
        }

        .post-sidebar {
            padding-top: 0;
        }

        .comments-section {
            height: auto;
        }
    }
    </style>

    <script src="js/curtir.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY"></script>
    <script>
    function initMap() {
        <?php if(isset($post['Latitude']) && isset($post['Longitude'])): ?>
        const location = {
            lat: <?php echo $post['Latitude']; ?>,
            lng: <?php echo $post['Longitude']; ?>
        };

        const map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: location,
            styles: [
                {
                    "featureType": "all",
                    "elementType": "geometry",
                    "stylers": [{"color": "#f5f1e8"}]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [{"color": "#8ba888"}]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [{"color": "#4a7856"}]
                }
            ]
        });

        const marker = new google.maps.Marker({
            position: location,
            map: map,
            title: "Local da foto",
            animation: google.maps.Animation.DROP
        });
        <?php endif; ?>
    }

    // Inicializar o mapa quando a página carregar
    window.onload = initMap;
    </script>
</body>
</html> 