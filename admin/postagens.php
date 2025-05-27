<?php
session_start();

$title = "Gerenciar Postagens";
$css = ['admin', 'admin_postagens']; 
$path = "../";

include '../database.php';
include '../functions/is_logado.php';
include '../functions/is_admin.php';

// Funções de outros arquivos que podem ser úteis (se não incluídas automaticamente)
include_once '../functions/get_usuario.php';
include_once '../functions/get_especie.php';
// include_once '../functions/get_image.php'; // Para avatares, etc.

// Função para atualizar o status da postagem (idealmente em um arquivo de funções de postagem)
if (!function_exists('update_postagem_status')) {
    function update_postagem_status($post_id, $novo_status) {
        global $conn;
        $allowed_statuses = ['PENDENTE', 'NEGADO', 'APROVADO'];
        if (!in_array($novo_status, $allowed_statuses)) {
            error_log("Status inválido fornecido: " . $novo_status);
            return false;
        }

        $stmt = $conn->prepare("UPDATE POSTAGEM SET Status = ? WHERE Id = ?");
        if (!$stmt) {
            error_log("Erro ao preparar statement: " . $conn->error);
            return false;
        }
        $stmt->bind_param("si", $novo_status, $post_id);
        if ($stmt->execute()) {
            // Opcional: Adicionar ao histórico do especialista/admin
            // if (isset($_SESSION['id'])) {
            //     $id_especialista = $_SESSION['id'];
            //     $acao = "Postagem ID $post_id definida como $novo_status";
            //     $stmt_hist = $conn->prepare("INSERT INTO HISTORICO (Id_Postagem, Id_Especialista, DataHora, Acao) VALUES (?, ?, NOW(), ?)");
            //     $stmt_hist->bind_param("iis", $post_id, $id_especialista, $acao);
            //     $stmt_hist->execute();
            //     $stmt_hist->close();
            // }
            return $stmt->affected_rows > 0;
        } else {
            error_log("Erro ao executar statement: " . $stmt->error);
        }
        $stmt->close();
        return false;
    }
}


// Processar ações de moderação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'], $_POST['post_id'], $_POST['novo_status'])) {
    $post_id = (int)$_POST['post_id'];
    $novo_status = strtoupper(trim($_POST['novo_status'])); // PENDENTE, APROVADO, NEGADO

    if (update_postagem_status($post_id, $novo_status)) {
        $_SESSION['success'] = "Status da postagem #{$post_id} alterado para {$novo_status} com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao alterar status da postagem #{$post_id}. Status fornecido: {$novo_status}";
    }
    header("Location: postagens?" . http_build_query($_GET)); // Manter filtros atuais
    exit();
}

// Filtros e busca
$filtro_status = $_GET['status'] ?? ''; // PENDENTE, APROVADO, NEGADO
$filtro_tipo_postagem = $_GET['tipo_postagem'] ?? ''; // AVISTAMENTO, ATROPELAMENTO
$busca = $_GET['busca'] ?? '';
$pagina = (int)($_GET['pagina'] ?? 1);
$por_pagina = 10; // Quantidade de posts por página
$offset = ($pagina - 1) * $por_pagina;

// Construir query com filtros
$sql_base = "
    SELECT
        p.Id AS PostagemId,
        p.Tipo AS PostagemTipo,
        p.Texto AS PostagemTexto,
        p.Status AS PostagemStatus,
        p.DataHora_Envio AS PostagemDataEnvio,
        u.Id AS UsuarioId,
        u.Nome AS UsuarioNome,
        u.Foto AS UsuarioFoto,
        (SELECT f.URL FROM FOTO f WHERE f.Id_Postagem = p.Id ORDER BY f.Id LIMIT 1) AS PostagemFotoURL,
        (SELECT e.NomeComum FROM ESPECIE e JOIN FOTO f_esp ON e.Id = f_esp.Id_Especie WHERE f_esp.Id_Postagem = p.Id AND e.Id != 1 LIMIT 1) AS EspecieNomeComum,
        (SELECT e.Id FROM ESPECIE e JOIN FOTO f_esp ON e.Id = f_esp.Id_Especie WHERE f_esp.Id_Postagem = p.Id AND e.Id != 1 LIMIT 1) AS EspecieId
    FROM
        POSTAGEM p
    LEFT JOIN
        USUARIO u ON p.Id_Usuario = u.Id"; // LEFT JOIN para caso o usuário seja deletado

$where_conditions = [];
$params = [];
$param_types = "";

if (!empty($filtro_status)) {
    $where_conditions[] = "p.Status = ?";
    $params[] = $filtro_status;
    $param_types .= "s";
}

if (!empty($filtro_tipo_postagem)) {
    $where_conditions[] = "p.Tipo = ?";
    $params[] = $filtro_tipo_postagem;
    $param_types .= "s";
}

if (!empty($busca)) {
    $where_conditions[] = "(p.Texto LIKE ? OR u.Nome LIKE ?)";
    $busca_param = "%{$busca}%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $param_types .= "ss";
}

$sql_where = "";
if (!empty($where_conditions)) {
    $sql_where = " WHERE " . implode(" AND ", $where_conditions);
}

// Contar total de postagens para paginação
$count_sql = "SELECT COUNT(DISTINCT p.Id) as total FROM POSTAGEM p LEFT JOIN USUARIO u ON p.Id_Usuario = u.Id" . $sql_where;
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_postagens = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_postagens / $por_pagina);
$count_stmt->close();

// Buscar postagens com paginação e ordenação
$sql_query = $sql_base . $sql_where . " ORDER BY CASE p.Status WHEN 'PENDENTE' THEN 1 WHEN 'APROVADO' THEN 2 WHEN 'NEGADO' THEN 3 ELSE 4 END, p.DataHora_Envio DESC LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($sql_query);
if (!$stmt) {
    die("Erro na preparação da query: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$postagens = $stmt->get_result();

?>

<?php
include '../layouts/header.php';
include '../layouts/navbar_admin.php';
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <h2>Gerenciar Postagens</h2>

            <?php include '../layouts/alerts.php'; ?>

            <div class="user-filters" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                    <div class="form-group" style="margin: 0;">
                        <input type="text" name="busca" placeholder="Buscar texto ou usuário..."
                               value="<?php echo htmlspecialchars($busca); ?>" style="min-width: 200px; padding: 0.5rem;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select name="status" style="padding: 0.5rem;">
                            <option value="">Todos os Status</option>
                            <option value="PENDENTE" <?php echo $filtro_status === 'PENDENTE' ? 'selected' : ''; ?>>Pendente</option>
                            <option value="APROVADO" <?php echo $filtro_status === 'APROVADO' ? 'selected' : ''; ?>>Aprovado</option>
                            <option value="NEGADO" <?php echo $filtro_status === 'NEGADO' ? 'selected' : ''; ?>>Negado</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <select name="tipo_postagem" style="padding: 0.5rem;">
                            <option value="">Todos os Tipos</option>
                            <option value="AVISTAMENTO" <?php echo $filtro_tipo_postagem === 'AVISTAMENTO' ? 'selected' : ''; ?>>Avistamento</option>
                            <option value="ATROPELAMENTO" <?php echo $filtro_tipo_postagem === 'ATROPELAMENTO' ? 'selected' : ''; ?>>Atropelamento</option>
                            </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="postagens" class="btn btn-light">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </form>
            </div>

            <div class="admin-list">
                <?php if ($postagens->num_rows > 0): ?>
                    <?php while($post = $postagens->fetch_assoc()): ?>
                        <div class="post-card-admin">
                            <div class="post-card-admin-image">
                                <?php if (!empty($post['PostagemFotoURL']) && file_exists($path . $post['PostagemFotoURL'])): ?>
                                    <img src="<?php echo $path . htmlspecialchars($post['PostagemFotoURL']); ?>" alt="Foto da postagem">
                                <?php else: ?>
                                    <span class="no-image-placeholder"><i class="fas fa-image"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="post-card-admin-content">
                                <div class="post-card-admin-header">
                                    <div>
                                        <h3 class="post-card-admin-title">
                                            Postagem #<?php echo $post['PostagemId']; ?>
                                            <span class="post-card-admin-status status-<?php echo strtolower(htmlspecialchars($post['PostagemStatus'])); ?>">
                                                <?php echo htmlspecialchars($post['PostagemStatus']); ?>
                                            </span>
                                        </h3>
                                        <div class="post-card-admin-meta">
                                            <span><i class="fas fa-user"></i>
                                                <?php if($post['UsuarioNome']): ?>
                                                    <a href="../perfil.php?id=<?php echo $post['UsuarioId']; ?>" target="_blank"><?php echo htmlspecialchars($post['UsuarioNome']); ?></a>
                                                <?php else: ?>
                                                    Usuário Deletado
                                                <?php endif; ?>
                                            </span>
                                            <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($post['PostagemDataEnvio'])); ?></span>
                                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($post['PostagemTipo']); ?></span>
                                            <?php if ($post['EspecieNomeComum']): ?>
                                                <span><i class="fas fa-leaf"></i> <a href="../verespecie.php?id=<?php echo $post['EspecieId']; ?>" target="_blank"><?php echo htmlspecialchars($post['EspecieNomeComum']); ?></a></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-card-admin-body">
                                    <?php echo nl2br(htmlspecialchars(substr($post['PostagemTexto'], 0, 250))); ?>
                                    <?php if (strlen($post['PostagemTexto']) > 250): ?>
                                        ...
                                    <?php endif; ?>
                                </div>
                                <div class="post-card-admin-actions">
                                    <a href="../verpost.php?id=<?php echo $post['PostagemId']; ?>" class="btn btn-sm btn-light" target="_blank"><i class="fas fa-eye"></i> Ver</a>
                                    <?php if ($post['PostagemStatus'] === 'PENDENTE'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostagemId']; ?>">
                                            <input type="hidden" name="novo_status" value="APROVADO">
                                            <input type="hidden" name="action" value="moderar">
                                            <?php foreach ($_GET as $key => $value) if ($key !== 'post_id' && $key !== 'novo_status' && $key !== 'action') echo "<input type='hidden' name='".htmlspecialchars($key)."' value='".htmlspecialchars($value)."'>"; ?>
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Aprovar esta postagem?');"><i class="fas fa-check"></i> Aprovar</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostagemId']; ?>">
                                            <input type="hidden" name="novo_status" value="NEGADO">
                                            <input type="hidden" name="action" value="moderar">
                                            <?php foreach ($_GET as $key => $value) if ($key !== 'post_id' && $key !== 'novo_status' && $key !== 'action') echo "<input type='hidden' name='".htmlspecialchars($key)."' value='".htmlspecialchars($value)."'>"; ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Negar esta postagem?');"><i class="fas fa-times"></i> Negar</button>
                                        </form>
                                    <?php elseif ($post['PostagemStatus'] === 'APROVADO'): ?>
                                         <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostagemId']; ?>">
                                            <input type="hidden" name="novo_status" value="NEGADO">
                                            <input type="hidden" name="action" value="moderar">
                                            <?php foreach ($_GET as $key => $value) if ($key !== 'post_id' && $key !== 'novo_status' && $key !== 'action') echo "<input type='hidden' name='".htmlspecialchars($key)."' value='".htmlspecialchars($value)."'>"; ?>
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Alterar para NEGADO?');"><i class="fas fa-times"></i> Negar</button>
                                        </form>
                                    <?php elseif ($post['PostagemStatus'] === 'NEGADO'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['PostagemId']; ?>">
                                            <input type="hidden" name="novo_status" value="APROVADO">
                                            <input type="hidden" name="action" value="moderar">
                                            <?php foreach ($_GET as $key => $value) if ($key !== 'post_id' && $key !== 'novo_status' && $key !== 'action') echo "<input type='hidden' name='".htmlspecialchars($key)."' value='".htmlspecialchars($value)."'>"; ?>
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Alterar para APROVADO?');"><i class="fas fa-check"></i> Aprovar</button>
                                        </form>
                                    <?php endif; ?>
                                    </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination" style="margin-top: 20px; text-align: center;">
                            <?php
                                $queryParams = $_GET;
                                unset($queryParams['pagina']); // Remove página dos parâmetros atuais para não duplicar
                                $queryString = http_build_query($queryParams);
                            ?>
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?php echo $pagina-1; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light">&laquo; Anterior</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <?php if ($i == $pagina): ?>
                                    <span class="btn btn-sm btn-primary current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina+1; ?>&<?php echo $queryString; ?>" class="btn btn-sm btn-light">Próxima &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-images" style="font-size: 3em; margin-bottom: 10px; opacity: 0.3;"></i>
                        <p>Nenhuma postagem encontrada com os filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>