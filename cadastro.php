<?php
session_start();

if(isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
    header("Location: ./");
    exit();
}

include 'database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $termos = isset($_POST['termos']) ? true : false;

    // Validação
    if (empty($nome)) {
        $errors[] = "Nome é obrigatório";
    }

    if (empty($email)) {
        $errors[] = "Email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }

    if (empty($senha)) {
        $errors[] = "Senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres";
    }

    if ($senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem";
    }

    if (!$termos) {
        $errors[] = "Você precisa aceitar os termos de uso";
    }

    // Verificar se email já existe
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT Id FROM USUARIO WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Este email já está cadastrado";
        } else {
            // Hash da senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir usuário
            $stmt = $conn->prepare("INSERT INTO USUARIO (Nome, Email, Senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);
            
            if ($stmt->execute()) {
                $success = true;
                // Redirecionar para login após 2 segundos
                header("refresh:2;url=login.php");
            } else {
                $errors[] = "Erro ao cadastrar. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - JundBio</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <main class="main-content">
        <div class="auth-container two-columns">
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-leaf"></i>
                    <h2>Criar Conta</h2>
                    <p>Junte-se à comunidade JundBio</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Cadastro realizado com sucesso! Redirecionando...
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <div class="form-columns">
                        <div class="form-column">
                            <div class="form-group">
                                <label for="nome">
                                    <i class="fas fa-user"></i>
                                    Nome
                                </label>
                                <input type="text" id="nome" name="nome" 
                                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                       placeholder="Seu nome completo" required>
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    Email
                                </label>
                                <input type="email" id="email" name="email"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="seu@email.com" required>
                            </div>
                        </div>

                        <div class="form-column">
                            <div class="form-group">
                                <label for="senha">
                                    <i class="fas fa-lock"></i>
                                    Senha
                                </label>
                                <input type="password" id="senha" name="senha"
                                       placeholder="Mínimo 6 caracteres" required>
                            </div>

                            <div class="form-group">
                                <label for="confirmar_senha">
                                    <i class="fas fa-lock"></i>
                                    Confirmar Senha
                                </label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha"
                                       placeholder="Confirme sua senha" required>
                            </div>

                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="termos" required>
                                    <span>Li e aceito os <a href="termos.php" target="_blank">termos de uso</a> e <a href="privacidade.php" target="_blank">política de privacidade</a></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i>
                        Criar Conta
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Já tem uma conta? <a href="login">Entrar</a></p>
                </div>
            </div>
        </div>
    </main>

</body>
</html> 