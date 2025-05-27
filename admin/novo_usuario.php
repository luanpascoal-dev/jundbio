<?php
session_start();

$title = "Criar Novo Usuário";
$css = ['admin', 'editar_perfil']; // Reutilizar CSS de editar_perfil para o formulário
$path = "../";

include '../database.php';
include '../functions/is_logado.php';
include '../functions/is_admin.php';
include_once '../functions/get_usuario.php'; // Para checar se email existe

// Função para inserir um usuário completo pelo admin
// Idealmente, esta função estaria em functions/insert_usuario.php
if (!function_exists('insert_full_usuario_admin')) {
    function insert_full_usuario_admin($nome, $email, $senha_hash, $tipo, $ativo, $foto_path, $biografia, $ocupacao, $pontos) {
        global $conn;
        $sql = "INSERT INTO USUARIO (Nome, Email, Senha, Tipo, Ativo, Foto, Biografia, Ocupacao, Pontos, DataRegistro, UltimoLogin)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Erro de preparação SQL (insert_full_usuario_admin): " . $conn->error);
            return false;
        }
        // O tipo para 'ativo' e 'pontos' é 'i' (integer)
        $stmt->bind_param("ssssisssi", $nome, $email, $senha_hash, $tipo, $ativo, $foto_path, $biografia, $ocupacao, $pontos);
        
        $success = $stmt->execute();
        if (!$success) {
            error_log("Erro de execução SQL (insert_full_usuario_admin): " . $stmt->error);
            return false;
        }
        $id_inserido = $conn->insert_id;
        $stmt->close();
        return $id_inserido > 0 ? $id_inserido : false;
    }
}


// Processar o formulário de criação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo_usuario = $_POST['tipo_usuario'] ?? 'COMUM';
    $status_usuario = isset($_POST['status_usuario']) ? (int)$_POST['status_usuario'] : 1; // 1 para ativo, 0 para inativo
    $ocupacao = trim($_POST['ocupacao']);
    $biografia = trim($_POST['biografia']);
    $pontos = filter_var($_POST['pontos'], FILTER_VALIDATE_INT) !== false ? (int)$_POST['pontos'] : 0;

    $foto_perfil_path_db = null; // Caminho para salvar no DB

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $_SESSION['error'] = "Nome, email, senha e confirmação de senha são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Formato de email inválido.";
    } elseif (get_id_by_email($email)) { // Verifica se o email já existe
        $_SESSION['error'] = "Este email já está cadastrado.";
    } elseif (strlen($senha) < 6) {
        $_SESSION['error'] = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $_SESSION['error'] = "As senhas não coincidem.";
    } else {
        // Processar upload de foto (se enviada)
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $arquivo = $_FILES['foto_perfil'];
            $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
            $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

            if (!in_array($extensao, $extensoes_permitidas)) {
                $_SESSION['error'] = "Formato de arquivo de foto não permitido (JPG, JPEG, PNG).";
            } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
                $_SESSION['error'] = "O arquivo da foto é muito grande (Máximo: 5MB).";
            } else {
                $nome_arquivo_servidor = 'avatar_' . uniqid() . '_' . time() . '.' . $extensao;
                // O $path já é "../", então 'uploads/avatars/' será relativo à raiz do site.
                $diretorio_upload_servidor = $path . 'uploads/avatars/'; // Caminho físico no servidor
                $caminho_completo_servidor = $diretorio_upload_servidor . $nome_arquivo_servidor;
                
                if (!file_exists($diretorio_upload_servidor)) {
                    if (!mkdir($diretorio_upload_servidor, 0777, true)) {
                        $_SESSION['error'] = "Falha ao criar diretório de uploads.";
                        // Impedir continuação se não puder criar diretório
                    }
                }

                if (empty($_SESSION['error']) && move_uploaded_file($arquivo['tmp_name'], $caminho_completo_servidor)) {
                    $foto_perfil_path_db = 'uploads/avatars/' . $nome_arquivo_servidor; // Caminho para o banco
                } elseif(empty($_SESSION['error'])) { // Se não houve erro anterior mas o move_uploaded_file falhou
                    $_SESSION['error'] = "Erro ao fazer upload da imagem de perfil.";
                }
            }
        }

        // Se não houve erros até aqui, prosseguir com a inserção
        if (empty($_SESSION['error'])) {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $novo_usuario_id = insert_full_usuario_admin($nome, $email, $senha_hash, $tipo_usuario, $status_usuario, $foto_perfil_path_db, $biografia, $ocupacao, $pontos);

            if ($novo_usuario_id) {
                $_SESSION['success'] = "Novo usuário '{$nome}' criado com sucesso! ID: {$novo_usuario_id}";
                header('Location: usuarios.php'); // Redireciona para a lista de usuários
                exit();
            } else {
                $_SESSION['error'] = "Erro ao criar o novo usuário no banco de dados.";
                // Se o upload da foto foi feito mas o DB falhou, idealmente removeria a foto enviada.
                if ($foto_perfil_path_db && file_exists($path . $foto_perfil_path_db)) {
                    unlink($path . $foto_perfil_path_db);
                }
            }
        }
    }
    // Se houver erro, a página será recarregada mostrando a mensagem
    // Não há redirecionamento aqui para manter os dados do formulário (se desejado, ou limpar)
}
?>

<?php
include $path . 'layouts/header.php';
include $path . 'layouts/navbar_admin.php';
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <div class="edit-profile-container"> <div class="edit-profile-header">
                    <h1>Criar Novo Usuário</h1>
                    <a href="usuarios" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i> Voltar para Usuários
                    </a>
                </div>

                <?php include $path . 'layouts/alerts.php'; ?>

                <form method="POST" class="edit-profile-form" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-section">
                            <h2>Informações Pessoais</h2>

                            <div class="form-group">
                                <label for="foto_perfil">Foto de Perfil (Opcional)</label>
                                <div class="profile-upload">
                                    <div class="current-photo" id="currentPhotoContainer"> <img src="#" alt="Preview" id="currentPhotoPreview" style="display:none; max-width: 100%; max-height: 100%; object-fit: cover;">
                                        <div class="no-photo" id="noPhotoPlaceholder"><i class="fas fa-user"></i></div>
                                    </div>
                                    <div class="photo-actions">
                                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png" onchange="previewAdminUserImage(this)">
                                        <button type="button" id="removePhotoSelection" class="btn btn-sm btn-link-danger" style="display:none; margin-top: 5px;">
                                            <i class="fas fa-times"></i> Remover Seleção
                                        </button>
                                        <small>Formatos: JPG, PNG. Max: 5MB</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" autocomplete="off" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required maxlength="128">
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" autocomplete="off" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required maxlength="256">
                            </div>
                             <div class="form-group">
                                <label for="ocupacao">Ocupação</label>
                                <input type="text" id="ocupacao" name="ocupacao" autocomplete="off" value="<?php echo htmlspecialchars($_POST['ocupacao'] ?? ''); ?>" maxlength="64">
                            </div>

                            <div class="form-group">
                                <label for="biografia">Biografia</label>
                                <textarea id="biografia" name="biografia" rows="3" autocomplete="off" maxlength="256"><?php echo htmlspecialchars($_POST['biografia'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Configurações da Conta e Senha</h2>
                            <div class="form-group">
                                <label for="senha">Senha *</label>
                                <input type="password" id="senha" name="senha" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Senha *</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                            </div>

                            <div class="form-group">
                                <label for="tipo_usuario">Tipo de Usuário *</label>
                                <select id="tipo_usuario" name="tipo_usuario">
                                    <option value="COMUM" <?php echo (($_POST['tipo_usuario'] ?? 'COMUM') == 'COMUM') ? 'selected' : ''; ?>>Comum</option>
                                    <option value="ADMIN" <?php echo (($_POST['tipo_usuario'] ?? '') == 'ADMIN') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status_usuario">Status da Conta *</label>
                                <select id="status_usuario" name="status_usuario">
                                    <option value="1" <?php echo (($_POST['status_usuario'] ?? 1) == 1) ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?php echo (($_POST['status_usuario'] ?? 1) == 0) ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="pontos">Pontos</label>
                                <input type="number" id="pontos" name="pontos" value="<?php echo htmlspecialchars($_POST['pontos'] ?? 0); ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Criar Usuário
                        </button>
                        <a href="usuarios" class="btn btn-light">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script>
    function previewAdminUserImage(input) {
        const preview = document.getElementById('currentPhotoPreview');
        const noPhotoPlaceholder = document.getElementById('noPhotoPlaceholder');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (noPhotoPlaceholder) {
                    noPhotoPlaceholder.style.display = 'none';
                }
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            // Se nenhum arquivo for selecionado (ou seleção for cancelada), volta ao placeholder
            if (preview) {
                preview.style.display = 'none';
                preview.src = '#'; // Limpa a src
            }
            if (noPhotoPlaceholder) {
                noPhotoPlaceholder.style.display = 'flex'; // ou 'block' dependendo do seu CSS
            }
        }
    }

    const fotoPerfilInput = document.getElementById('foto_perfil');
    const photoPreview = document.getElementById('currentPhotoPreview');
    const noPhotoPlaceholder = document.getElementById('noPhotoPlaceholder');
    const removePhotoSelectionButton = document.getElementById('removePhotoSelection');

    function previewAdminUserImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (photoPreview) {
                    photoPreview.src = e.target.result;
                    photoPreview.style.display = 'block';
                }
                if (noPhotoPlaceholder) {
                    noPhotoPlaceholder.style.display = 'none';
                }
                if (removePhotoSelectionButton) {
                    removePhotoSelectionButton.style.display = 'inline-block'; // Mostrar botão de remover
                }
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            // Se nenhum arquivo for selecionado (ou seleção for cancelada por outros meios)
            resetPhotoPreview();
        }
    }

    function resetPhotoPreview() {
        if (photoPreview) {
            photoPreview.style.display = 'none';
            photoPreview.src = '#'; // Limpa a src
        }
        if (noPhotoPlaceholder) {
            noPhotoPlaceholder.style.display = 'flex'; // ou 'block' dependendo do seu CSS
        }
        if (removePhotoSelectionButton) {
            removePhotoSelectionButton.style.display = 'none'; // Esconder botão de remover
        }
        // Limpa o valor do input de arquivo para que o backend não receba um arquivo "vazio"
        if (fotoPerfilInput) {
            fotoPerfilInput.value = ''; 
        }
    }

    // Adiciona o evento de clique ao botão de remover seleção
    if (removePhotoSelectionButton) {
        removePhotoSelectionButton.addEventListener('click', function() {
            resetPhotoPreview();
        });
    }
</script>
<style>
    /* Estilos de editar_perfil.css são herdados pela classe .edit-profile-container e .form-section */
    /* Ajustes específicos para novo_usuario.php se necessário */
    .profile-upload .current-photo {
        width: 120px;
        height: 120px;
        background-color: #f0f0f0; /* Fundo para o placeholder */
        border: 1px dashed #ccc;
        display: flex; /* Para centralizar o ícone no .no-photo */
        align-items: center;
        justify-content: center;
    }
    .profile-upload .no-photo i {
        font-size: 2.5rem;
    }
    .form-actions {
        margin-top: 1.5rem; /* Reduzir um pouco a margem se necessário */
    }

    .btn-link-danger {
        background-color: transparent;
        border: none;
        color: var(--danger-color); /* */
        padding: 0.25rem 0.5rem;
        text-decoration: none;
        font-size: 0.85rem;
        cursor: pointer;
        display: inline-flex; /* Para alinhar ícone e texto */
        align-items: center;
        gap: 0.25rem;
    }
    .btn-link-danger:hover {
        text-decoration: underline;
        opacity: 0.8;
    }
    .profile-upload .photo-actions { /* Para garantir que o botão fique bem posicionado */
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Ou center, dependendo do seu design */
    }
</style>
</body>
</html>