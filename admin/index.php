<?php
session_start();

$title = "Painel Administrativo";
$css = ['admin'];
$path = "../";

include '../database.php';
include '../functions/is_admin.php';

include '../functions/get_nivel.php';
include '../functions/get_especie.php';
include '../functions/get_usuario.php';
include '../functions/get_postagens.php';
include '../functions/get_comentario.php';

// Buscar estatísticas
$stats = [
    'especies' => 0,
    'usuarios' => 0,
    'posts' => 0,
    'comentarios' => 0
];

try {
    // Total de espécies
    $stats['especies'] = get_total_especies();

    // Total de usuários
    $stats['usuarios'] = get_total_usuarios(true);

    // Total de posts
    $stats['posts'] = get_total_postagens();

    // Total de comentários
    $stats['comentarios'] = get_total_comentarios();

    // Últimas espécies cadastradas
    $ultimas_especies = get_especies(true, 5);

    // Últimos usuários registrados
    $ultimos_usuarios = get_usuarios(true, true, 5);

} catch(PDOException $e) {
    $_SESSION['error'] = "Erro ao carregar estatísticas: " . $e->getMessage();
}


?>

<?php 
include '../layouts/header.php'; 
include '../layouts/navbar_admin.php';
?>

<div class="admin-container">
    <h2>Dashboard</h2>

    <?php include '../layouts/alerts.php'; ?>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <a href="especies" class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['especies']; ?></h3>
                <p>Espécies Cadastradas</p>
            </div>
        </a>

        <a href="usuarios" class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['usuarios']; ?></h3>
                <p>Usuários Registrados</p>
            </div>
        </a>

        <a href="posts" class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['posts']; ?></h3>
                <p>Postagens Publicadas</p>
            </div>
        </a>

        <a href="comentarios" class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['comentarios']; ?></h3>
                <p>Comentários</p>
            </div>
        </a>
    </div>

    <!-- Grid de Informações -->
    <div class="admin-grid">
        <!-- Últimas Espécies -->
        <div class="admin-list">
            <h3>Últimas Espécies Cadastradas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Espécie</th>
                            <th>Tipo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_especies as $especie): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($especie['NomeComum']); ?></td>
                                <td><?php echo htmlspecialchars($especie['NomeCientifico']); ?></td>
                                <td><?php echo htmlspecialchars($especie['Tipo']); ?></td>
                                <td><?php echo htmlspecialchars($especie['StatusExtincao']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Últimos Usuários -->
        <div class="admin-list">
            <h3>Últimos Usuários Registrados</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Tipo</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos_usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['Nome']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['Tipo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['DataRegistro'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

