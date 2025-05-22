<?php
session_start();

include 'functions/not_logado.php';

include 'database.php';

include 'functions/get_usuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (empty($email) || empty($senha)) {
        $_SESSION['error'] = "Email e senha são obrigatórios";
        header("Location: login");
        exit();
    }

    $result = get_usuario_by_email($email);


    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user['Senha']) && $user['Ativo'] == 1) {

            $_SESSION['id'] = $user['Id'];
            $_SESSION['usuario'] = $user['Nome'];
            $_SESSION['tipo'] = $user['Tipo'];
            $_SESSION['foto'] = $user['Foto'];

            $_SESSION['success'] = "Login realizado com sucesso";

            if($user['Tipo'] == 'ADMIN') {
                header("Location: admin");
            } else {
                header("Location: ./");
            }
        } else {
            $_SESSION['error'] = "Email ou senha inválidos";
        }
    } else {
        $_SESSION['error'] = "Email ou senha inválidos";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JundBio</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <main class="main-content">
        <div class="auth-container two-columns">
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-leaf"></i>
                    <h2>Entrar</h2>
                    <p>Acesse sua conta JundBio</p>
                </div>

                <?php include 'layouts/alerts.php'; ?>

                <form method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="seu@email.com" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">
                            <i class="fas fa-lock"></i>
                            Senha
                        </label>
                        <input type="password" id="senha" name="senha"
                               placeholder="Sua senha" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Não tem uma conta? <a href="cadastro">Criar conta</a></p>
                    <p><a href="esqueci_senha">Esqueceu sua senha?</a></p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>