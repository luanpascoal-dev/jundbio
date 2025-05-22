<?php
session_start();

include 'functions/not_logado.php';

include 'database.php';

include 'functions/insert_usuario.php';

include 'functions/get_usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    $termos = isset($_POST['termos']) ? true : false;

    // Validação
    if (empty($nome)) 
        $_SESSION['error'] = "Nome é obrigatório";
    

    if (empty($email)) 
        $_SESSION['error'] = "Email é obrigatório";
    else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        $_SESSION['error'] = "Email inválido";

    if (empty($senha)) 
        $_SESSION['error'] = "Senha é obrigatória";
    else if (strlen($senha) < 6) 
        $_SESSION['error'] = "A senha deve ter pelo menos 6 caracteres";

    if ($senha !== $confirmar_senha) 
        $_SESSION['error'] = "As senhas não coincidem";

    if (!$termos) 
        $_SESSION['error'] = "Você precisa aceitar os termos de uso";

    // Verificar se email já existe
    if (empty($_SESSION['error'])) {

        $id = get_id_by_email($email);
        
        if ($id) 
            $_SESSION['error'] = "Este email já está cadastrado";
        else {
            
            $insert = insert_usuario($nome, $email, $senha);

            if ($insert) {
                $_SESSION['success'] = "Cadastro realizado com sucesso! Redirecionando...";
                // Redirecionar para login após 2 segundos
                header("refresh:2;url=login");
            } else {
                $_SESSION['error'] = "Erro ao cadastrar. Tente novamente.";
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

                <?php include 'layouts/alerts.php'; ?>

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
                                    <span>Li e aceito os <a href="termos" target="_blank">termos de uso</a> e <a href="privacidade.php" target="_blank">política de privacidade</a></span>
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