<?php
session_start();

$title = "Editar Perfil";
$css = ['editar_perfil'];

include 'database.php';

include 'functions/is_logado.php';
include 'functions/get_usuario.php';

$usuario_id = $_SESSION['id'];

$usuario = get_usuario($usuario_id);

// Processar o formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $biografia = trim($_POST['biografia']);
    $ocupacao = trim($_POST['ocupacao']);
    

    // Verifica se o usuário quer remover a foto
    if (isset($_POST['remover_foto'])) {
        
        // Busca o caminho da foto atual
        $usuario = get_avatar_by_id($usuario_id);
        
        // Se existir uma foto, deletar o arquivo
        if ($usuario['Foto'] && file_exists($usuario['Foto'])) {
            unlink($usuario['Foto']);
        }
        
        // Atualiza o banco de dados removendo a foto
        remove_avatar($usuario_id);

        unset($_SESSION['foto']);
        
        $_SESSION['success'] = 'Foto de perfil removida com sucesso!';
        header('Location: editar_perfil');
        exit;
    }

    // Processar upload de nova foto
    $foto_perfil = $usuario['Foto']; // Manter a foto atual por padrão

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {

        $arquivo = $_FILES['foto_perfil'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['error'] = "Formato de arquivo não permitido. Use: " . implode(', ', $extensoes_permitidas);
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            $_SESSION['error'] = "O arquivo é muito grande. Tamanho máximo: 5MB";
        } else {
            $nome_arquivo = uniqid('') . '.' . $extensao;

            $diretorio = 'uploads/avatars/';

            // Criar diretório se não existir
            if (!file_exists($diretorio)) {
                mkdir($diretorio, 0777, true);
            }
            
            $caminho_completo = $diretorio . $nome_arquivo;
            if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
                // Remover foto antiga se existir
                if (!empty($usuario['Foto']) && file_exists($usuario['Foto'])) {
                    unlink($usuario['Foto']);
                }
                $foto_perfil = $caminho_completo;
            } else {
                $_SESSION['error'] = "Erro ao fazer upload da imagem.";
            }
        }
    }
    // Validar dados
    if (empty($nome) || empty($email)) {
        $_SESSION['error'] = "Nome e email são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email inválido.";
    }

    // Se estiver alterando a senha
    if (!empty($nova_senha)) {
        if (empty($senha_atual)) {
            $_SESSION['error'] = "Senha atual é obrigatória para alterar a senha.";
        } elseif ($nova_senha !== $confirmar_senha) {
            $_SESSION['error'] = "As senhas não conferem.";
        } elseif (strlen($nova_senha) < 6) {
            $_SESSION['error'] = "A nova senha deve ter pelo menos 6 caracteres.";
        }
    }

    if (empty($_SESSION['error'])) {
        try {
            // Verificar senha atual se estiver alterando a senha
            if (!empty($nova_senha)) {
                $stmt = $conn->prepare("SELECT Senha FROM USUARIO WHERE Id = ?");
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $usuario_db = $result->fetch_assoc();
                if (!password_verify($senha_atual, $usuario_db['Senha'])) {
                    $_SESSION['error'] = "Senha atual incorreta.";
                }
            }

            if (empty($_SESSION['error'])) {
                $execute = null;
                // Atualizar dados do usuário
                if (!empty($nova_senha)) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $execute = update_usuario($usuario_id, $nome, $email, $senha_hash, $foto_perfil, $biografia, $ocupacao);
                } else {
                    $execute = update_usuario_sem_senha($usuario_id, $nome, $email, $foto_perfil, $biografia, $ocupacao);
                }
                if ($execute) {
                    $_SESSION['usuario'] = $nome;
                    $_SESSION['foto'] = $foto_perfil;
                    $_SESSION['success'] = "Perfil atualizado com sucesso!";
                    header("Location: perfil");
                    exit();
                } else {
                    $_SESSION['error'] = "Erro ao atualizar perfil.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao atualizar perfil";
        }
    }
}
?>

<?php include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>

    <div class="container">
        
        
        <div class="edit-profile-container">
            <div class="edit-profile-header">
                <h1>Editar Perfil</h1>
                <a href="perfil" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Perfil
                </a>
            </div>

            <?php include 'layouts/alerts.php'; ?>

            <form method="POST" class="edit-profile-form" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- Coluna da Esquerda - Informações Pessoais -->
                    <div class="form-section">
                        <h2>Informações Pessoais</h2>
                        
                        <div class="form-group">
                            <label for="foto_perfil">Foto de Perfil</label>
                            <div class="profile-upload">
                                <div class="current-photo">
                                    <?php if (!empty($usuario['Foto'])): ?>
                                        <img src="<?php echo htmlspecialchars($usuario['Foto']); ?>" alt="Foto atual" id="currentPhoto">
                                    <?php else: ?>
                                        <div class="no-photo">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="photo-actions">
                                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(this)">
                                    <small>Formatos permitidos: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                                    
                                    <?php if (!empty($usuario['Foto'])): ?>
                                        <button type="button" class="btn btn-danger" onclick="removerFoto()">
                                            <i class="fas fa-trash"></i> Remover foto
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" id="nome" max-length="128" name="nome" value="<?php echo htmlspecialchars($usuario['Nome']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" max-length="256" name="email" value="<?php echo htmlspecialchars($usuario['Email']); ?>" required>
                        </div>

                    </div>

                    <!-- Coluna da Direita - Alterar Senha -->
                    <div class="form-section">

                        <div class="form-group">
                            <label for="ocupacao">Ocupação</label>
                            <input type="text" id="ocupacao" max-length="64" name="ocupacao" value="<?php echo htmlspecialchars($usuario['Ocupacao']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="biografia">Biografia</label>
                            <textarea id="biografia" max-length="256" name="biografia"><?php echo htmlspecialchars($usuario['Biografia']); ?></textarea>
                        </div>
                        
                        <h2>Alterar Senha</h2>
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual">
                        </div>

                        <div class="form-group">
                            <label for="nova_senha">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha">
                        </div>

                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const currentPhoto = document.getElementById('currentPhoto');
                if (currentPhoto) {
                    currentPhoto.src = e.target.result;
                } else {
                    const noPhoto = document.querySelector('.no-photo');
                    if (noPhoto) {
                        noPhoto.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    }
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

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 