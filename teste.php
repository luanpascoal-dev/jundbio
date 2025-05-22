<?php
session_start();
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
    
    // Processar upload da imagem
    $foto_perfil = $usuario['Foto']; // Manter a foto atual por padrão
    
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto_perfil'];
        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extensao, $extensoes_permitidas)) {
            $_SESSION['error'] = "Formato de arquivo não permitido. Use: " . implode(', ', $extensoes_permitidas);
        } elseif ($arquivo['size'] > 5 * 1024 * 1024) { // 5MB
            $_SESSION['error'] = "O arquivo é muito grande. Tamanho máximo: 5MB";
        } else {
            $nome_arquivo = uniqid('perfil_') . '.' . $extensao;
            $diretorio = 'uploads/perfil/';
            
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
                // Atualizar dados do usuário
                if (!empty($nova_senha)) {
                    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE USUARIO SET Nome = ?, Email = ?, Senha = ?, Foto = ? WHERE Id = ?");
                    $stmt->bind_param("ssssi", $nome, $email, $senha_hash, $foto_perfil, $usuario_id);
                } else {
                    $stmt = $conn->prepare("UPDATE USUARIO SET Nome = ?, Email = ?, Foto = ? WHERE Id = ?");
                    $stmt->bind_param("sssi", $nome, $email, $foto_perfil, $usuario_id);
                }

                $_SESSION['usuario'] = $nome;

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Perfil atualizado com sucesso!";
                    header("Location: perfil.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Erro ao atualizar perfil.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao atualizar perfil: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <?php include 'layouts/alerts.php'; ?>
        
        <div class="edit-profile-container">
            <div class="edit-profile-header">
                <h1>Editar Perfil</h1>
                <a href="perfil.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Perfil
                </a>
            </div>

            <form method="POST" class="edit-profile-form" enctype="multipart/form-data">
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
                            <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewImage(this)">
                            <small>Formatos permitidos: JPG, JPEG, PNG, GIF. Tamanho máximo: 5MB</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['Nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['Email']); ?>" required>
                    </div>
                </div>

                <div class="form-section">
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

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .edit-profile-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .edit-profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .edit-profile-header h1 {
        color: var(--primary-green);
        margin: 0;
    }

    .form-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .form-section h2 {
        color: var(--text-dark);
        margin: 0 0 1.5rem;
        font-size: 1.25rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--text-dark);
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
    }

    .profile-upload {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .current-photo {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .current-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .no-photo {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-green);
        color: white;
    }

    .no-photo i {
        font-size: 3rem;
    }

    .profile-upload input[type="file"] {
        width: 100%;
        max-width: 300px;
    }

    .profile-upload small {
        color: var(--text-light);
        font-size: 0.875rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
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
    }

    .btn-primary {
        background: var(--primary-green);
        color: white;
    }

    .btn-primary:hover {
        background: #2e7d32;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #e5e7eb;
        color: var(--text-dark);
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    @media (max-width: 768px) {
        .edit-profile-container {
            margin: 1rem;
            padding: 1rem;
        }

        .edit-profile-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .form-section {
            padding: 1rem;
        }
    }
    </style>

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
    </script>

    <?php include 'layouts/footer.php'; ?>
</body>
</html>