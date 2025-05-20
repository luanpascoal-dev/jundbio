<?php
session_start();
include 'database.php';

include 'functions/is_logado.php';
include 'functions/get_usuario.php';
include 'functions/get_postagens.php';

$usuario_id = $_SESSION['id'];
$usuario = get_usuario($usuario_id);

// Buscar postagens do usuário
$postagens = get_postagens_by_usuario($usuario_id);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

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
                    <h2><?php echo htmlspecialchars($usuario['Nome']); ?></h2>
                    <p class="profile-type"><?php echo htmlspecialchars($usuario['Tipo']); ?></p>
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

                    <a href="editar_perfil" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar Perfil
                    </a>
                </div>
            </div>

            <!-- Coluna de Postagens -->
            <div class="profile-posts">
                <h3>Minhas Postagens</h3>
                <?php if (empty($postagens)): ?>
                    <div class="no-posts">
                        <i class="fas fa-newspaper"></i>
                        <p>Você ainda não fez nenhuma postagem.</p>
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
                                            <a href="postagem?id=<?php echo $post['Id']; ?>" class="btn btn-sm btn-primary">
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

    <style>
    .profile-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .profile-view, .profile-posts {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        background: var(--primary-green);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-avatar i {
        font-size: 3rem;
        color: white;
    }

    .profile-header h2 {
        color: var(--text-dark);
        margin: 0;
        font-size: 1.5rem;
    }

    .profile-type {
        color: var(--text-light);
        margin: 0.5rem 0 0;
    }

    .profile-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .info-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-group label {
        font-weight: 500;
        color: var(--text-dark);
    }

    .info-group p {
        margin: 0;
        color: var(--text-light);
    }

    /* Estilos para as postagens */
    .profile-posts h3 {
        color: var(--primary-green);
        margin-bottom: 1.5rem;
    }

    .no-posts {
        text-align: center;
        padding: 2rem;
        color: var(--text-light);
    }

    .no-posts i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--border-color);
    }

    .posts-grid {
        display: grid;
        gap: 1.5rem;
    }

    .post-card {
        background: #f8f9fa;
        border-radius: 8px;
        overflow: hidden;
        transition: transform 0.3s ease;
        padding: 1rem;
    }

    .post-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .post-content h4 {
        margin: 0 0 0.5rem;
        color: var(--text-dark);
        font-size: 1.1rem;
    }

    .post-excerpt {
        color: var(--text-light);
        margin: 0 0 1rem;
        font-size: 0.9rem;
        line-height: 1.4;
    }

    .post-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }

    .post-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .post-date {
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .post-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .post-status.pendente {
        background: #fff3cd;
        color: #856404;
    }

    .post-status.aprovado {
        background: #d4edda;
        color: #155724;
    }

    .post-status.negado {
        background: #f8d7da;
        color: #721c24;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .btn-primary {
        background: var(--primary-green);
        color: white;
    }

    .btn-primary:hover {
        background: #2e7d32;
        transform: translateY(-1px);
    }

    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-view, .profile-posts {
            padding: 1.5rem;
        }
    }
    </style>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 