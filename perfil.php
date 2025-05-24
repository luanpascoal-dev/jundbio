<?php
session_start();

$title = "Perfil";
$css = ['perfil'];

include 'database.php';

include 'functions/is_logado.php';

include 'functions/get_usuario.php';
include 'functions/get_postagens.php';
include 'functions/get_comentario.php';
include 'functions/get_curtidas.php';
include 'functions/get_nivel.php';

$usuario_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['id'];

$usuario = get_usuario($usuario_id);

if(!$usuario){
    $_SESSION['error'] = 'Usuário não encontrado';
    header('Location: /JundBio/');
    exit();
}

// Buscar postagens do usuário
$postagens = get_postagens_by_usuario($usuario_id);

// Buscar total de fotos
$total_fotos = get_fotos_by_id($usuario_id);

// Buscar total de curtidas
$total_curtidas = get_curtidas_postagens_by_usuario($usuario_id);

// Buscar total de comentários
$total_comentarios = get_comentarios_by_usuario($usuario_id);


$pontuacao = get_pontos_by_id($usuario_id);

$proximo_nivel = get_proximo_nivel($pontuacao);
$pontos_proximo_nivel = get_pontos_by_nivel($proximo_nivel);


// Medalhas baseadas em diferentes critérios
$conquistas = [
    [
        'icon' => 'fa-camera',
        'title' => 'Fotógrafo Iniciante',
        'description' => 'Postou 10 fotos',
        'achieved' => $total_fotos >= 10
    ],
    [
        'icon' => 'fa-heart',
        'title' => 'Popular',
        'description' => 'Recebeu 50 curtidas',
        'achieved' => $total_curtidas >= 50
    ],
    [
        'icon' => 'fa-comment',
        'title' => 'Comunicativo',
        'description' => 'Fez 20 comentários',
        'achieved' => $total_comentarios >= 20
    ],
    [
        'icon' => 'fa-star',
        'title' => 'Estrela',
        'description' => 'Alcançou 100 pontos',
        'achieved' => $pontuacao >= 100
    ]
];

?>

<?php
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>
    <style>
        .level-medal {
            width: 120px;
            height: 120px;
            min-width: 120px;
            min-height: 120px;
            background: <?php echo get_nivel_cor($usuario['Id']); ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
        }
    </style>

    <div class="container">
        
        <?php include 'layouts/alerts.php'; ?>

        <div class="profile-grid">
            <!-- Coluna de Visualização -->
            <div class="profile-view">


                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($usuario['Foto'])): ?>
                            <img src="<?php echo htmlspecialchars($usuario['Foto']); ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($usuario['Nome']); ?>
                    <?php if(is_especialista($usuario['Id'])): ?>
                        <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                    <?php endif; ?>
                
                </h2>
                    <?php if($usuario['Tipo'] == 'ADMIN'): ?>
                        <p class="profile-type">Administrador</p>
                    <?php endif; ?>

                </div>



                <div class="profile-info">

                    <div class="info-group">
                        <label>Email</label>
                        <p><?php echo htmlspecialchars($usuario['Email']); ?></p>
                    </div>

                    <div class="info-group">
                        <label>Data de Registro</label>
                        <p><?php echo date('d/m/Y', strtotime($usuario['DataRegistro'])); ?></p>
                    </div>
                    <div class="info-group">
                        <label>Ocupação</label>
                        <p><?php echo !empty($usuario['Ocupacao']) ? htmlspecialchars($usuario['Ocupacao']) : 'Nenhuma'; ?></p>
                    </div>
                    <div class="info-group">
                        <label>Biografia</label>
                        <p><?php echo !empty($usuario['Biografia']) ? htmlspecialchars($usuario['Biografia']) : 'Nenhuma'; ?></p>
                    </div>

                    

                    

                    <?php if($usuario['Id'] == $_SESSION['id']): ?>
                        <a href="editar_perfil" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar Perfil
                        </a>
                    <?php endif; ?>
                </div>


            </div>

            <div class="profile-stats">
                <div class="level-display">
                    <div class="current-level">
                        <div class="level-medal">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div class="level-info">
                            <h3 class="level-title" style="color: <?php echo get_nivel_cor($usuario['Id']); ?>">
                                <?php echo get_nivel($usuario['Id']); ?>
                            </h3>
                            <div class="level-details">
                                <span class="level-points">
                                    <i class="fas fa-star"></i>
                                    <?php echo $pontuacao; ?> pontos
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if($proximo_nivel != get_nivel($usuario['Id'])): ?>
                    <div class="next-level">
                        <i class="fas fa-arrow-right"></i>
                        <div class="next-level-medal">
                            <i class="fa-solid fa-medal"></i>
                        </div>
                        <div class="next-level-info">
                            <h3><?php echo $proximo_nivel; ?></h3>
                            <p>Faltam <?php echo $pontos_proximo_nivel - $pontuacao; ?> pontos</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_fotos; ?></h3>
                            <p>Fotos</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_curtidas; ?></h3>
                            <p>Curtidas</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-comment"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_comentarios; ?></h3>
                            <p>Comentários</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-info">
                            <h3>0</h3>
                            <p>????</p>
                        </div>
                    </div>
                </div>

                <div class="medals-section">
                    <h3>Conquistas</h3>
                    <div class="medals-grid">
                        <?php

                        foreach ($conquistas as $medal): ?>
                            <div class="medal-card <?php echo $medal['achieved'] ? 'achieved' : ''; ?>">
                                <div class="medal-icon" style="background: <?php echo isset($medal['color']) ? $medal['color'] : 'var(--primary-green)'; ?>">
                                    <i class="fas <?php echo $medal['icon']; ?>"></i>
                                </div>
                                <div class="medal-info">
                                    <h4><?php echo $medal['title']; ?></h4>
                                    <p><?php echo $medal['description']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="profile-posts">
            </div>

            <!-- Coluna de Postagens -->
            <div class="profile-posts">

                <h3>Postagens</h3>

                <?php if(!$postagens || empty($postagens) || $postagens->num_rows <= 0): ?>
                    <div class="no-results">
                        <i class="fas fa-newspaper"></i>
                        <p>Este usuário ainda não fez nenhuma postagem</p>
                    </div>

                <?php else: ?>

                    <div class="posts-grid">

                        <?php foreach ($postagens as $post): ?>
                            <div class="post-card">
                                <div class="post-content">
                                    <h4><?php echo htmlspecialchars($post['Tipo']); ?></h4>
                                    <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['Texto'], 0, 100)) . '...'; ?></p>
                                    <div class="post-meta">
                                        <span class="post-date">
                                            <i class="far fa-calendar"></i>
                                            <?php echo date('d/m/Y', strtotime($post['DataHora_Envio'])); ?>
                                        </span>
                                        <div class="post-actions">
                                            <span class="post-status <?php echo strtolower($post['Status']); ?>">
                                                <?php echo $post['Status']; ?>
                                            </span>
                                            <a href="verpost?id=<?php echo $post['Id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Ver Post
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 