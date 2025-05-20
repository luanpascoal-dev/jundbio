<?php
session_start();
include 'database.php';

$token_valido = false;
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);

if (empty($token)) {
    header('Location: login');
    exit;
}

// Verificar se o token é válido e não expirou
$stmt = $conn->prepare("
    SELECT r.*, u.Email 
    FROM RESET_SENHA r 
    JOIN USUARIO u ON r.Id_Usuario = u.Id 
    WHERE r.Token = ? AND r.Expiracao > NOW() AND r.Utilizado = 0
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Link inválido ou expirado. Por favor, solicite um novo link de redefinição de senha.';
} else {
    $token_valido = true;
    $reset_data = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valido) {
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
    $confirmar_senha = filter_input(INPUT_POST, 'confirmar_senha', FILTER_SANITIZE_STRING);
    
    if (empty($senha) || empty($confirmar_senha)) {
        $_SESSION['error'] = 'Por favor, preencha todos os campos.';
    } elseif (strlen($senha) < 6) {
        $_SESSION['error'] = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirmar_senha) {
        $_SESSION['error'] = 'As senhas não coincidem.';
    } else {
        // Atualizar a senha
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE USUARIO SET Senha = ? WHERE Id = ?");
        $stmt->bind_param("si", $senha_hash, $reset_data['Id_Usuario']);
        
        if ($stmt->execute()) {
            // Marcar o token como utilizado
            $stmt = $conn->prepare("UPDATE RESET_SENHA SET Utilizado = 1 WHERE Token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();

            $_SESSION['success'] = 'Senha atualizada com sucesso! Você já pode fazer login com sua nova senha.';
            $token_valido = false; // Desabilita o formulário após sucesso
        } else {
            $_SESSION['error'] = 'Erro ao atualizar a senha. Por favor, tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

    <div class="container">
        <div class="reset-container">
            <h1>Redefinir Senha</h1>
            
            <?php include 'layouts/alerts.php'; ?>

            <?php if ($token_valido): ?>
                <div class="reset-form">
                    <p class="reset-info">
                        Digite sua nova senha abaixo.
                    </p>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="senha">Nova Senha</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="senha" name="senha" required 
                                       placeholder="Digite sua nova senha" minlength="6">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required 
                                       placeholder="Confirme sua nova senha" minlength="6">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Salvar nova senha
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="reset-links">
                <a href="login" class="link">
                    <i class="fas fa-arrow-left"></i> Voltar para o login
                </a>
            </div>
        </div>
    </div>

    <style>
    .reset-container {
        max-width: 500px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .reset-container h1 {
        color: var(--primary-green);
        margin-bottom: 1.5rem;
        text-align: center;
        font-size: 1.75rem;
    }

    .reset-info {
        text-align: center;
        color: var(--text-light);
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .reset-form {
        margin-top: 2rem;
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

    .input-group {
        position: relative;
    }

    .input-group i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-light);
    }

    .input-group input {
        width: 100%;
        padding: 0.75rem 1rem 0.75rem 2.5rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .input-group input:focus {
        border-color: var(--primary-green);
        outline: none;
    }

    .btn-block {
        width: 100%;
        margin-top: 1rem;
    }

    .reset-links {
        margin-top: 1.5rem;
        text-align: center;
    }

    .reset-links .link {
        color: var(--primary-green);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: color 0.3s ease;
    }

    .reset-links .link:hover {
        color: #2e7d32;
    }

    @media (max-width: 768px) {
        .reset-container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
    </style>

</body>
</html> 