<?php
session_start();

$title = "Login";
$css = ['login'];

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

    if ($result) {
        $user = $result;

        if (password_verify($senha, $user['Senha']) && $user['Ativo'] == 1) {

            $_SESSION['id'] = $user['Id'];
            $_SESSION['usuario'] = $user['Nome'];
            $_SESSION['tipo'] = $user['Tipo'];
            $_SESSION['foto'] = $user['Foto'];

            $_SESSION['success'] = "Login realizado com sucesso";

            $stmt = $conn->prepare("UPDATE USUARIO SET UltimoLogin = CURRENT_TIMESTAMP WHERE Id = ?");
            $stmt->bind_param("i", $user['Id']);
            $stmt->execute();
            $stmt->close();

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

<?php 
include 'layouts/header.php'; 
?>

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
                        <input type="email" id="email" name="email" autocomplete="off"
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