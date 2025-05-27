<?php
session_start();

$title = "Editar Usuário";
$css = ['admin', 'editar_perfil']; // Reutilizando CSS de editar_perfil onde aplicável
$path = "../";

include '../database.php';
include '../functions/is_logado.php';
include '../functions/is_admin.php';
include_once '../functions/get_usuario.php'; // Para get_usuario() e get_avatar_by_id()
include_once '../functions/update_usuario.php'; // Para remove_avatar()

$usuario = null;
$usuario_id = null;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de usuário inválido fornecido.";
    header('Location: usuarios');
    exit;
}

$usuario_id = (int)$_GET['id'];

// O administrador não deve editar a si mesmo por esta página, deve usar ../editar_perfil.php
if ($usuario_id === $_SESSION['id']) {
    $_SESSION['error'] = "Para editar seu próprio perfil, por favor, use a página 'Editar Perfil' no menu de usuário.";
    header('Location: ../editar_perfil');
    exit;
}

// Carregar dados do usuário a ser editado
$usuario = get_usuario($usuario_id); //

if (!$usuario) {
    $_SESSION['error'] = "Usuário com ID {$usuario_id} não encontrado.";
    header('Location: usuarios');
    exit;
}


// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $biografia = trim($_POST['biografia']);
    $ocupacao = trim($_POST['ocupacao']);
    $tipo_usuario = $_POST['tipo_usuario'];
    $status_usuario = isset($_POST['status_usuario']) ? (int)$_POST['status_usuario'] : 0; // 1 para ativo, 0 para inativo
    $pontos = filter_var($_POST['pontos'], FILTER_VALIDATE_INT) !== false ? (int)$_POST['pontos'] : $usuario['Pontos'];

    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    $dados_update = [
        'nome' => $nome,
        'email' => $email,
        'biografia' => $biografia,
        'ocupacao' => $ocupacao,
        'tipo' => $tipo_usuario,
        'ativo' => $status_usuario,
        'pontos' => $pontos
    ];

    // Restrições para o usuário ID 1 (Super Admin)
    if ($usuario_id == 1) {
        $dados_update['tipo'] = 'ADMIN'; // Garante que o tipo não seja alterado
        $dados_update['ativo'] = 1;     // Garante que não seja desativado
        // Poderia adicionar uma verificação se o admin logado é o ID 1 para permitir mudar e-mail/senha do ID 1
        // if ($_SESSION['id'] != 1) { unset($dados_update['email']); /* não permite outro admin mudar email do superadmin */ }
    }

    // Verifica se o usuário quer remover a foto
    if (isset($_POST['remover_foto'])) {
        
        // Busca o caminho da foto atual
        $usuario = get_avatar_by_id($usuario_id);

        // Se existir uma foto, deletar o arquivo
        if ($usuario && file_exists($usuario)){
            unlink($usuario);
        }
        
        // Atualiza o banco de dados removendo a foto
        remove_avatar($usuario_id);

        unset($_SESSION['foto']);
        
        $_SESSION['success'] = 'Foto de perfil removida com sucesso!';
        header('Location: editar_usuario?id=' . $usuario_id);
        exit;
    }


    $nova_senha_hash = null;
    $foto_perfil_atualizada = $usuario['Foto']; // Mantém a foto atual por padrão
    $foto_antiga_servidor = $usuario['Foto'] ? $path . $usuario['Foto'] : null;


    // Validar dados básicos
    if (empty($nome) || empty($email)) {
        $_SESSION['error'] = "Nome e email são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email inválido.";
    } elseif ($email !== $usuario['Email']) {
        // Verificar se o novo email já existe para outro usuário
        $stmt_check_email = $conn->prepare("SELECT Id FROM USUARIO WHERE Email = ? AND Id != ?");
        $stmt_check_email->bind_param("si", $email, $usuario_id);
        $stmt_check_email->execute();
        if ($stmt_check_email->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Este email já está em uso por outro usuário.";
        }
        $stmt_check_email->close();
    }


    // Processar nova senha
    if (!empty($nova_senha)) {
        if ($nova_senha !== $confirmar_senha) {
            $_SESSION['error'] = "As novas senhas não coincidem.";
        } elseif (strlen($nova_senha) < 6) {
            $_SESSION['error'] = "A nova senha deve ter pelo menos 6 caracteres.";
        } else {
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        }
    }

    // Processar upload de nova foto ou remoção
    $nova_foto_enviada_path = null; // Caminho relativo ao banco
    $houve_upload_sucesso = false;

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto_perfil'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['error'] = "Formato de arquivo de foto não permitido. Use: " . implode(', ', $extensoes_permitidas);
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            $_SESSION['error'] = "O arquivo da foto é muito grande. Tamanho máximo: 5MB";
        } else {
            $nome_arquivo_servidor = uniqid('') . '.' . $extensao;
            $diretorio_upload = $path . 'uploads/avatars/'; // Caminho a partir da raiz do site para o servidor
            $caminho_completo_servidor = $diretorio_upload . $nome_arquivo_servidor;
            
            if (!file_exists($diretorio_upload)) {
                mkdir($diretorio_upload, 0777, true);
            }

            if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo_servidor)) {
                $nova_foto_enviada_path = 'uploads/avatars/' . $nome_arquivo_servidor; // Caminho para o banco
                $houve_upload_sucesso = true;
                // Se uma foto antiga existia e uma nova foi carregada, a antiga será removida pela função update
            } else {
                $_SESSION['error'] = "Erro ao fazer upload da nova imagem de perfil.";
            }
        }
    }

    $caminho_foto_para_db = $usuario['Foto']; // Default é a foto existente

    if ($houve_upload_sucesso) {
        $caminho_foto_para_db = $nova_foto_enviada_path;
        $remover_foto_fisicamente = true; // Indica que a foto antiga deve ser removida se houver upload de nova
    } else {
        $remover_foto_fisicamente = false; // Nenhuma nova foto e não pediu para remover
    }


    if (empty($_SESSION['error'])) {
        // update_usuario_admin($id, $dados, $nova_senha_hash = null, $nova_foto_path = null, $remover_foto_atual = false, $foto_antiga_path = null)
        if (update_usuario_admin($usuario_id, $dados_update, $nova_senha_hash, $caminho_foto_para_db, $remover_foto_fisicamente, $foto_antiga_servidor)) {
            $_SESSION['success'] = "Perfil do usuário atualizado com sucesso!";
            // Recarregar dados do usuário para exibir no formulário após a atualização
            $usuario = get_usuario($usuario_id); //
             //header('Location: usuarios.php'); // Ou de volta para a mesma página
             //exit();
        } else {
            $_SESSION['error'] = "Erro ao atualizar o perfil do usuário no banco de dados.";
        }
    }
    // Se houver erro, a página será recarregada mostrando a mensagem
}


?>

<?php
include $path . 'layouts/header.php'; //
include $path . 'layouts/navbar_admin.php'; //
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <div class="edit-profile-container"> <div class="edit-profile-header">
                    <h1>Editar Usuário: <?php echo htmlspecialchars($usuario['Nome']); ?></h1>
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
                                <label for="foto_perfil">Foto de Perfil</label>
                                <div class="profile-upload">
                                    <div class="current-photo">
                                        <?php if (!empty($usuario['Foto']) && file_exists($path . $usuario['Foto'])): ?>
                                            <img src="<?php echo $path . htmlspecialchars($usuario['Foto']); ?>" alt="Foto atual" id="currentPhotoPreview">
                                        <?php else: ?>
                                            <div class="no-photo"><i class="fas fa-user"></i></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="photo-actions">
                                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png" onchange="previewAdminUserImage(this)">
                                        <small>Formatos: JPG, JPEG, PNG. Max: 5MB</small>
                                        <?php if (!empty($usuario['Foto'])): ?>
                                            <label class="checkbox-label-inline">
                                                <input type="checkbox" name="remover_foto_existente" value="1"> Remover foto atual
                                            </label>
                                        <?php endif; ?>
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="nome">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" autocomplete="off" value="<?php echo htmlspecialchars($usuario['Nome']); ?>" required maxlength="128">
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" autocomplete="off" value="<?php echo htmlspecialchars($usuario['Email']); ?>" required maxlength="256" <?php if ($usuario_id == 1 && $_SESSION['id'] != 1) echo 'readonly'; /* Exemplo de restrição para email do superadmin */ ?>>
                            </div>
                             <div class="form-group">
                                <label for="ocupacao">Ocupação</label>
                                <input type="text" id="ocupacao" name="ocupacao" autocomplete="off" value="<?php echo htmlspecialchars($usuario['Ocupacao']); ?>" maxlength="64">
                            </div>

                            <div class="form-group">
                                <label for="biografia">Biografia</label>
                                <textarea id="biografia" name="biografia" rows="3" autocomplete="off" maxlength="256"><?php echo htmlspecialchars($usuario['Biografia']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h2>Configurações da Conta</h2>
                            <div class="form-group">
                                <label for="tipo_usuario">Tipo de Usuário</label>
                                <select id="tipo_usuario" name="tipo_usuario" <?php if ($usuario_id == 1) echo 'disabled'; ?>>
                                    <option value="COMUM" <?php echo ($usuario['Tipo'] == 'COMUM') ? 'selected' : ''; ?>>Comum</option>
                                    <option value="ADMIN" <?php echo ($usuario['Tipo'] == 'ADMIN') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <?php if ($usuario_id == 1): ?>
                                    <input type="hidden" name="tipo_usuario" value="ADMIN"> <small>O tipo do administrador principal não pode ser alterado.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="status_usuario">Status da Conta</label>
                                <select id="status_usuario" name="status_usuario" <?php if ($usuario_id == 1) echo 'disabled'; ?>>
                                    <option value="1" <?php echo ($usuario['Ativo'] == 1) ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?php echo ($usuario['Ativo'] == 0) ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                                <?php if ($usuario_id == 1): ?>
                                    <input type="hidden" name="status_usuario" value="1"> <small>O administrador principal não pode ser desativado.</small>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="pontos">Pontos</label>
                                <input type="number" id="pontos" name="pontos" value="<?php echo htmlspecialchars($usuario['Pontos']); ?>" min="0">
                            </div>
                            
                            <h2>Alterar Senha (Opcional)</h2>
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" placeholder="Deixe em branco para não alterar">
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Nova Senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme a nova senha">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
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
        const noPhotoDiv = document.querySelector('.current-photo .no-photo');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (preview) {
                    preview.src = e.target.result;
                } else if (noPhotoDiv) {
                    // Se não havia imagem antes, remove o ícone e adiciona a tag img
                    noPhotoDiv.innerHTML = ''; // Limpa o ícone
                    const newImg = document.createElement('img');
                    newImg.id = 'currentPhotoPreview';
                    newImg.src = e.target.result;
                    newImg.alt = 'Preview da nova foto';
                    noPhotoDiv.parentNode.appendChild(newImg); // Adiciona a nova imagem ao lado do div .no-photo
                    noPhotoDiv.style.display = 'none'; // Esconde o placeholder de ícone
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removerFoto() {
        
        // Criar um input hidden para o remover_foto
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'remover_foto';
        input.value = '1';
        
        // Adicionar o input ao formulário principal
        document.querySelector('.edit-profile-form').appendChild(input);
        
        // Submeter o formulário
        document.querySelector('.edit-profile-form').submit();
        
    }
</script>
<style>
    /* Estilos adicionais ou ajustes podem ser colocados aqui ou em admin.css / editar_perfil.css */
    .edit-profile-container { /* Herdado de editar_perfil.css */
        max-width: 900px; /* Um pouco mais largo para acomodar os campos de admin */
    }
    .form-grid { /* Herdado de editar_perfil.css */
        /* grid-template-columns: 1fr 1fr; Se já definido, ok */
    }
    .profile-upload .current-photo { /* Herdado de editar_perfil.css */
        width: 120px;
        height: 120px;
    }
    .profile-upload .no-photo i { /* Herdado de editar_perfil.css */
        font-size: 2.5rem;
    }
    .checkbox-label-inline {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-dark);
        margin-top: 0.5rem;
    }
    .checkbox-label-inline input[type="checkbox"] {
        width: auto; /* Reset para o tamanho padrão do checkbox */
        margin-right: 0.25rem;
    }
    .form-section small {
        display: block;
        margin-top: 0.5rem;
        color: var(--text-light);
        font-size: 0.85rem;
    }
</style>
</body>
</html>