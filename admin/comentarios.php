<?php
session_start();

$title = "Gerenciar Comentários";
$css = ['admin']; 
$path = "../";

include '../database.php';
include '../functions/is_logado.php';
include '../functions/is_admin.php';

// Funções de outros arquivos
include_once '../functions/get_usuario.php'; // Para detalhes do autor

// Função para excluir comentário
if (!function_exists('delete_comentario_admin')) {
    function delete_comentario_admin($comentario_id) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM COMENTARIO WHERE Id = ?");
        if (!$stmt) {
            error_log("Erro ao preparar statement (delete comentario): " . $conn->error);
            return false;
        }
        $stmt->bind_param("i", $comentario_id);
        $success = $stmt->execute();
        if (!$success) {
            error_log("Erro ao executar statement (delete comentario): " . $stmt->error);
        }
        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $success && $affected_rows > 0;
    }
}

// Processar ação de exclusão
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'excluir_comentario') {
    if (isset($_POST['comentario_id']) && !empty($_POST['comentario_id'])) {
        $comentario_id = (int)$_POST['comentario_id'];
        if (delete_comentario_admin($comentario_id)) {
            $_SESSION['success'] = "Comentário #{$comentario_id} excluído com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao excluir comentário #{$comentario_id}.";
        }
    } else {
        $_SESSION['error'] = "ID do comentário inválido para exclusão.";
    }
    header("Location: comentarios?" . http_build_query($_GET)); // Manter filtros
    exit();
}

// Filtros e busca
$busca = $_GET['busca'] ?? ''; // Busca por texto do comentário ou nome do usuário
$filtro_post_id = isset($_GET['post_id']) && is_numeric($_GET['post_id']) ? (int)$_GET['post_id'] : '';
$pagina = (int)($_GET['pagina'] ?? 1);
$por_pagina = 15; // Comentários por página
$offset = ($pagina - 1) * $por_pagina;

// Construir query base para buscar comentários
$sql_base = "
    SELECT
        c.Id AS ComentarioId,
        c.Texto AS ComentarioTexto,
        c.DataHora AS ComentarioDataHora,
        u.Id AS UsuarioId,
        u.Nome AS UsuarioNome,
        u.Foto AS UsuarioFoto,
        p.Id AS PostagemId,
        SUBSTRING(p.Texto, 1, 70) AS PostagemTrecho -- Trecho da postagem para contexto
    FROM
        COMENTARIO c
    JOIN
        USUARIO u ON c.Id_Usuario = u.Id
    JOIN
        POSTAGEM p ON c.Id_Postagem = p.Id";

$where_conditions = [];
$params = [];
$param_types = "";

if (!empty($busca)) {
    $where_conditions[] = "(c.Texto LIKE ? OR u.Nome LIKE ?)";
    $busca_param = "%{$busca}%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $param_types .= "ss";
}

if (!empty($filtro_post_id)) {
    $where_conditions[] = "p.Id = ?";
    $params[] = $filtro_post_id;
    $param_types .= "i";
}

$sql_where = "";
if (!empty($where_conditions)) {
    $sql_where = " WHERE " . implode(" AND ", $where_conditions);
}

// Contar total de comentários para paginação
$count_sql = "SELECT COUNT(c.Id) as total FROM COMENTARIO c JOIN USUARIO u ON c.Id_Usuario = u.Id JOIN POSTAGEM p ON c.Id_Postagem = p.Id" . $sql_where;
$count_stmt = $conn->prepare($count_sql);
if (!$count_stmt) {
    die("Erro na preparação da query de contagem: " . $conn->error);
}
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_comentarios = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_comentarios / $por_pagina);
$count_stmt->close();

// Buscar comentários com paginação e ordenação
$sql_query = $sql_base . $sql_where . " ORDER BY c.DataHora DESC LIMIT ? OFFSET ?";
$params_query = $params; // Clona params para não adicionar limit/offset duas vezes
$params_query[] = $por_pagina;
$params_query[] = $offset;
$param_types_query = $param_types . "ii";

$stmt = $conn->prepare($sql_query);
if (!$stmt) {
    die("Erro na preparação da query principal: " . $conn->error);
}
if (!empty($params_query)) {
    $stmt->bind_param($param_types_query, ...$params_query);
}
$stmt->execute();
$comentarios = $stmt->get_result();

?>

<?php
include '../layouts/header.php';
include '../layouts/navbar_admin.php';
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <h2>Gerenciar Comentários (<?php echo $total_comentarios; ?>)</h2>

            <?php include '../layouts/alerts.php'; ?>

            <div class="admin-filters">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <input type="text" name="busca" placeholder="Buscar comentário ou usuário..."
                               value="<?php echo htmlspecialchars($busca); ?>" class="form-control-filter">
                    </div>
                    <div class="form-group">
                        <input type="number" name="post_id" placeholder="ID da Postagem"
                               value="<?php echo htmlspecialchars($filtro_post_id); ?>" class="form-control-filter">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="comentarios" class="btn btn-light">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </form>
            </div>

            <div class="admin-list-comments">
                <?php if ($comentarios->num_rows > 0): ?>
                    <?php while($comentario = $comentarios->fetch_assoc()): ?>
                        <div class="comment-card">
                            <div class="comment-card-header">
                                <div class="comment-author-info">
                                    <?php if (!empty($comentario['UsuarioFoto']) && file_exists($path . $comentario['UsuarioFoto'])): ?>
                                        <img src="<?php echo $path . htmlspecialchars($comentario['UsuarioFoto']); ?>" alt="Foto de <?php echo htmlspecialchars($comentario['UsuarioNome']); ?>" class="user-avatar sm">
                                    <?php else: ?>
                                        <div class="default-avatar sm">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <a href="../perfil.php?id=<?php echo $comentario['UsuarioId']; ?>" target="_blank" class="comment-author-name">
                                        <?php echo htmlspecialchars($comentario['UsuarioNome']); ?>
                                    </a>
                                </div>
                                <span class="comment-date">
                                    <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($comentario['ComentarioDataHora'])); ?>
                                </span>
                            </div>
                            <div class="comment-card-body">
                                <p><?php echo nl2br(htmlspecialchars($comentario['ComentarioTexto'])); ?></p>
                            </div>
                            <div class="comment-card-footer">
                                <span class="comment-post-context">
                                    Em: <a href="../verpost.php?id=<?php echo $comentario['PostagemId']; ?>" target="_blank">
                                        Postagem #<?php echo $comentario['PostagemId']; ?> "<?php echo htmlspecialchars($comentario['PostagemTrecho']); ?>..."
                                    </a>
                                </span>
                                <form method="POST" class="comment-action-form" onsubmit="return confirm('Tem certeza que deseja excluir este comentário?');">
                                    <input type="hidden" name="comentario_id" value="<?php echo $comentario['ComentarioId']; ?>">
                                    <input type="hidden" name="action" value="excluir_comentario">
                                    <?php foreach ($_GET as $key => $value) if ($key !== 'comentario_id' && $key !== 'action') echo "<input type='hidden' name='".htmlspecialchars($key)."' value='".htmlspecialchars($value)."'>"; ?>
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon-action" title="Excluir Comentário">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php
                                $queryParams = $_GET;
                                unset($queryParams['pagina']);
                                $queryString = http_build_query($queryParams);
                            ?>
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?php echo $pagina-1; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light">&laquo; Anterior</a>
                            <?php endif; ?>

                            <?php
                            // Lógica para exibir um número limitado de páginas
                            $num_links_paginacao = 5; // Total de links de página a serem exibidos (aprox.)
                            $inicio_link = max(1, $pagina - floor($num_links_paginacao / 2));
                            $fim_link = min($total_paginas, $pagina + floor($num_links_paginacao / 2));

                            if ($fim_link - $inicio_link + 1 < $num_links_paginacao) {
                                if ($inicio_link == 1) {
                                    $fim_link = min($total_paginas, $inicio_link + $num_links_paginacao - 1);
                                } else {
                                    $inicio_link = max(1, $fim_link - $num_links_paginacao + 1);
                                }
                            }
                            ?>

                            <?php if ($inicio_link > 1): ?>
                                <a href="?pagina=1&<?php echo $queryString; ?>" class="btn btn-sm btn-light">1</a>
                                <?php if ($inicio_link > 2): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $inicio_link; $i <= $fim_link; $i++): ?>
                                <?php if ($i == $pagina): ?>
                                    <span class="btn btn-sm btn-primary current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($fim_link < $total_paginas): ?>
                                <?php if ($fim_link < $total_paginas - 1): ?>
                                    <span class="pagination-ellipsis">...</span>
                                <?php endif; ?>
                                <a href="?pagina=<?php echo $total_paginas; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light"><?php echo $total_paginas; ?></a>
                            <?php endif; ?>


                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina+1; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light">Próxima &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-results-admin">
                        <i class="far fa-comment-dots"></i>
                        <p>Nenhum comentário encontrado com os filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<style>
    .admin-filters {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid #e0e0e0;
    }
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }
    .form-control-filter {
        padding: 0.5rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    .form-control-filter:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.2rem rgba(44, 85, 48, 0.25);
        outline: none;
    }

    .admin-list-comments {
        margin-top: 1.5rem;
    }
    .comment-card {
        background-color: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        margin-bottom: 1rem;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .comment-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #f0f0f0;
    }
    .comment-author-info {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .user-avatar.sm { /* Estilo para avatar pequeno, assumindo que você tem esta classe */
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
    }
    .default-avatar.sm { /* Para avatar padrão pequeno */
        width: 32px;
        height: 32px;
        min-width: 32px;
        min-height: 32px;
        font-size: 1rem;
    }
    .comment-author-name {
        font-weight: 600;
        color: var(--primary-green);
        text-decoration: none;
    }
    .comment-author-name:hover {
        text-decoration: underline;
    }
    .comment-date {
        font-size: 0.8rem;
        color: #777;
    }
    .comment-date i {
        margin-right: 0.25rem;
    }
    .comment-card-body p {
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.6;
        color: #333;
        word-wrap: break-word;
    }
    .comment-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 0.75rem;
        border-top: 1px solid #f0f0f0;
        font-size: 0.85rem;
    }
    .comment-post-context {
        color: #555;
    }
    .comment-post-context a {
        color: var(--secondary-green);
        text-decoration: none;
    }
    .comment-post-context a:hover {
        text-decoration: underline;
    }
    .comment-action-form {
        display: inline;
    }
    .btn-icon-action i {
        margin-right: 0.3rem;
    }
    .no-results-admin {
        text-align: center;
        padding: 2rem;
        color: #777;
        background-color: #f9f9f9;
        border-radius: 8px;
    }
    .no-results-admin i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.5;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }
    .pagination .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
    }
    .pagination .current {
        background-color: var(--primary-green);
        color: white;
        border-color: var(--primary-green);
    }
    .pagination-ellipsis {
        padding: 0.4rem 0.6rem;
        color: #777;
    }
</style>
</body>
</html>