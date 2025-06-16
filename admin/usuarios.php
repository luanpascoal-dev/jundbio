<?php
session_start();

$title = "Gerenciar Usuários";
$css = ['admin', 'usuarios'];
$path = "../";

include '../functions/is_logado.php';
include '../database.php';

include '../functions/is_admin.php';
include '../functions/get_usuario.php';
include '../functions/get_nivel.php';
include '../functions/get_image.php';

// Processar ações dos usuários
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'criar_usuario':
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $senha = trim($_POST['senha']);
            $tipo = trim($_POST['tipo']);
            $biografia = trim($_POST['biografia']);
            $ocupacao = trim($_POST['ocupacao']);
            
            if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
                $_SESSION['error'] = "Campos obrigatórios não preenchidos";
            } else {
                try {
                    // Verificar se email já existe
                    $stmt = $conn->prepare("SELECT Id FROM USUARIO WHERE Email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    
                    if ($stmt->get_result()->num_rows > 0) {
                        $_SESSION['error'] = "Este email já está cadastrado";
                    } else {
                        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                        $data_registro = date('Y-m-d H:i:s');
                        
                        $stmt = $conn->prepare("INSERT INTO USUARIO (Nome, Email, Senha, Tipo, Biografia, Ocupacao, DataRegistro) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssssss", $nome, $email, $senha_hash, $tipo, $biografia, $ocupacao, $data_registro);
                        
                        if ($stmt->execute()) {
                            $_SESSION['success'] = "Usuário criado com sucesso!";
                        } else {
                            throw new Exception($conn->error);
                        }
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "Erro ao criar usuário: " . $e->getMessage();
                }
            }
            break;
            
        case 'alterar_status':
            $usuario_id = (int)$_POST['usuario_id'];
            $novo_status = $_POST['status'];
            if($usuario_id == 1){
                $_SESSION['error'] = "Não é possível alterar o status do usuário administrador principal.";
                break;
            }
            if($usuario_id == $_SESSION['id']){
                $_SESSION['error'] = "Não é possível alterar seu próprio status do usuário.";
                break;
            }
            try {
                $stmt = $conn->prepare("UPDATE USUARIO SET Ativo = ? WHERE Id = ?");
                $stmt->bind_param("si", $novo_status, $usuario_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Status do usuário alterado com sucesso!";
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao alterar status: " . $e->getMessage();
            }
            break;
            
        case 'alterar_tipo':
            $usuario_id = (int)$_POST['usuario_id'];
            $novo_tipo = $_POST['tipo'];
            if($usuario_id == 1){
                $_SESSION['error'] = "Não é possível alterar o tipo do usuário administrador principal.";
                break;
            }
            if($usuario_id == $_SESSION['id']){
                $_SESSION['error'] = "Não é possível alterar seu próprio tipo do usuário.";
                break;
            }
            try {
                $stmt = $conn->prepare("UPDATE USUARIO SET Tipo = ? WHERE Id = ?");
                $stmt->bind_param("si", $novo_tipo, $usuario_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Tipo de usuário alterado com sucesso!";
                } else {
                    throw new Exception($conn->error);
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Erro ao alterar tipo: " . $e->getMessage();
            }
            break;
        case 'excluir_usuario':
            header("Location: excluir_usuario");
            break;
    }
    
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: usuarios");
    exit();
}

// Filtros e busca
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_status = $_GET['status'] ?? '';
$busca = $_GET['busca'] ?? '';
$pagina = (int)($_GET['pagina'] ?? 1);
$por_pagina = 15;
$offset = ($pagina - 1) * $por_pagina;

// Construir query com filtros
$where_conditions = ["Id != 1"]; // Excluir admin principal
$params = [];
$param_types = "";

if (!empty($filtro_tipo)) {
    $where_conditions[] = "Tipo = ?";
    $params[] = $filtro_tipo;
    $param_types .= "s";
}

if (!empty($filtro_status)) {
    $where_conditions[] = "Ativo = ?";
    $params[] = $filtro_status;
    $param_types .= "i";
}

if (!empty($busca)) {
    $where_conditions[] = "(Nome LIKE ? OR Email LIKE ?)";
    $busca_param = "%$busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $param_types .= "ss";
}

$where_clause = implode(" AND ", $where_conditions);

// Contar total de usuários
$count_sql = "SELECT COUNT(*) as total FROM USUARIO WHERE $where_clause";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_usuarios = $count_stmt->get_result()->fetch_assoc()['total'];
$total_paginas = ceil($total_usuarios / $por_pagina);

// Buscar usuários com paginação
$sql = "SELECT * FROM USUARIO WHERE $where_clause ORDER BY DataRegistro DESC LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$params[] = $offset;
$param_types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$usuarios = $stmt->get_result();

// Buscar estatísticas rápidas
$stats = [
    'total' => 0,
    'ativos' => 0,
    'inativos' => 0,
    'admins' => 0,
    'usuarios_comuns' => 0
];

try {
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN Ativo = '1' THEN 1 ELSE 0 END) as ativos,
            SUM(CASE WHEN Ativo = '0' THEN 1 ELSE 0 END) as inativos,
            SUM(CASE WHEN Tipo = 'ADMIN' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN Tipo = 'COMUM' THEN 1 ELSE 0 END) as usuarios_comuns
        FROM USUARIO WHERE Id != 1
    ";
    $stats_result = $conn->query($stats_sql)->fetch_assoc();
    $stats = array_merge($stats, $stats_result ?? 0);
} catch (Exception $e) {
    // Manter valores padrão
}
?>

<?php 
include '../layouts/header.php'; 
include '../layouts/navbar_admin.php';
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>Gerenciar Usuários</h2>
                <button class="btn btn-primary" onclick="window.location.href='novo_usuario'">
                    <i class="fas fa-plus"></i>
                    Novo Usuário
                </button>
            </div>

            <?php include '../layouts/alerts.php'; ?>

            <!-- Estatísticas Rápidas -->
            <div class="quick-stats">
                <div class="quick-stat">
                    <h4><?php echo $stats['total']; ?></h4>
                    <p>Total de Usuários</p>
                </div>
                <div class="quick-stat">
                    <h4><?php echo $stats['ativos']; ?></h4>
                    <p>Usuários Ativos</p>
                </div>
                <div class="quick-stat">
                    <h4><?php echo $stats['inativos']; ?></h4>
                    <p>Usuários Inativos</p>
                </div>
                <div class="quick-stat">
                    <h4><?php echo $stats['admins']; ?></h4>
                    <p>Administradores</p>
                </div>
                <div class="quick-stat">
                    <h4><?php echo $stats['usuarios_comuns']; ?></h4>
                    <p>Usuários Comuns</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="user-filters">
                <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; width: 100%;">
                    <div class="form-group" style="margin: 0;">
                        <input type="text" name="busca" placeholder="Buscar por nome ou email..." 
                               value="<?php echo htmlspecialchars($busca); ?>" style="min-width: 250px;">
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <select name="tipo">
                            <option value="">Todos os tipos</option>
                            <option value="admin" <?php echo $filtro_tipo === 'admin' ? 'selected' : ''; ?>>Administradores</option>
                            <option value="comum" <?php echo $filtro_tipo === 'usuario' ? 'selected' : ''; ?>>Usuários</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin: 0;">
                        <select name="status">
                            <option value="">Todos os status</option>
                            <option value="ativo" <?php echo $filtro_status === 'ativo' ? 'selected' : ''; ?>>Ativos</option>
                            <option value="inativo" <?php echo $filtro_status === 'inativo' ? 'selected' : ''; ?>>Inativos</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Filtrar
                    </button>
                    
                    <a href="usuarios" class="btn btn-light">
                        <i class="fas fa-refresh"></i>
                        Limpar
                    </a>
                </form>
            </div>

            <?php include '../layouts/alerts.php'; ?>

            <!-- Lista de Usuários -->
            <div class="admin-list">
                <h3>Usuários Cadastrados (<?php echo $total_usuarios; ?>)</h3>
                
                <?php if ($usuarios->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Tipo</th>
                                    <th>Status</th>
                                    <th>Registro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($usuario = $usuarios->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                
                                                
                                                    
                                               
                                                <div class="user-info">
                                                    <?php if (!empty($usuario['Foto'])): ?>
                                                        <img src="<?php echo '../' . htmlspecialchars($usuario['Foto']); ?>" alt="Foto de perfil" class="user-avatar">
                                                    <?php else: ?>
                                                        
                                                        <?php echo get_default_avatar(); ?>
                                                        
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="user-name"><?php echo htmlspecialchars($usuario['Nome']); ?>
                                                        <?php if(is_especialista($usuario['Id'])): ?>
                                                            <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                                                        <?php endif; ?>
                                                        </div>
                                                        <div class="user-email"><?php echo htmlspecialchars($usuario['Email']); ?></div>
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pos-relative">
                                            <span class="status-badge tipo-<?php echo strtolower($usuario['Tipo']); ?>">
                                                <?php echo ucfirst($usuario['Tipo']); ?>
                                            </span>
                                        </td>
                                        <td class="pos-relative">
                                            <span class="status-badge status-<?php echo $usuario['Ativo'] ? 'ativo' : 'inativo'; ?>">
                                                <?php echo $usuario['Ativo'] ? 'Ativo' : 'Inativo'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['DataRegistro'])); ?></td>
                                        <td>
                                            <div class="user-actions">
                                                <button class="btn-icon" title="Alterar Status" 
                                                        onclick="alterarStatus(<?php echo $usuario['Id']; ?>, '<?php echo $usuario['Ativo']; ?>')">
                                                    <i class="fas fa-toggle-<?php echo $usuario['Ativo'] === '1' ? 'on' : 'off'; ?>"></i>
                                                </button>
                                                
                                                <button class="btn-icon" title="Alterar Tipo" 
                                                        onclick="alterarTipo(<?php echo $usuario['Id']; ?>, '<?php echo $usuario['Tipo']; ?>')">
                                                    <i class="fas fa-user-cog"></i>
                                                </button>
                                                <a href="../perfil?id=<?php echo $usuario['Id']; ?>" class="btn-icon" title="Ver Perfil">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="editar_usuario?id=<?php echo $usuario['Id']; ?>" class="btn-icon" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn-icon btn-danger-icon" title="Excluir Usuário"
                                                        onclick="confirmarExclusao(<?php echo $usuario['Id']; ?>, '<?php echo htmlspecialchars(addslashes($usuario['Nome'])); ?>')">
                                                    <i class="fas fa-user-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                        <div class="pagination">
                            <?php if ($pagina > 1): ?>
                                <a href="?pagina=<?php echo $pagina-1; ?>&tipo=<?php echo $filtro_tipo; ?>&status=<?php echo $filtro_status; ?>&busca=<?php echo $busca; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagina-2); $i <= min($total_paginas, $pagina+2); $i++): ?>
                                <?php if ($i == $pagina): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?pagina=<?php echo $i; ?>&tipo=<?php echo $filtro_tipo; ?>&status=<?php echo $filtro_status; ?>&busca=<?php echo $busca; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($pagina < $total_paginas): ?>
                                <a href="?pagina=<?php echo $pagina+1; ?>&tipo=<?php echo $filtro_tipo; ?>&status=<?php echo $filtro_status; ?>&busca=<?php echo $busca; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="fas fa-users" style="font-size: 3em; margin-bottom: 10px; opacity: 0.3;"></i>
                        <p>Nenhum usuário encontrado com os filtros aplicados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <script>
        
        function alterarStatus(userId, currentStatus) {
            const newStatus = currentStatus === '1' ? '0' : '1';
            const action = newStatus === '1' ? 'ativar' : 'desativar';
            
            if (confirm(`Tem certeza que deseja ${action} este usuário?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="alterar_status">
                    <input type="hidden" name="usuario_id" value="${userId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function alterarTipo(userId, currentType) {
            const newType = currentType === 'ADMIN' ? 'COMUM' : 'ADMIN';
            const action = newType === 'ADMIN' ? 'promover a administrador' : 'rebaixar a usuário comum';
            
            if (confirm(`Tem certeza que deseja ${action} este usuário?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="alterar_tipo">
                    <input type="hidden" name="usuario_id" value="${userId}">
                    <input type="hidden" name="tipo" value="${newType}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function confirmarExclusao(id, nome) {
            if (confirm(`Tem certeza ABSOLUTA que deseja excluir o usuário "${nome}" (ID: ${id})? Suas postagens ficarão órfãs, e seus comentários e curtidas serão removidos.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'excluir_usuario';
                form.innerHTML = `
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>