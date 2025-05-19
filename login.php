<?php
session_start();

if(isset($_SESSION['id']) && isset($_SESSION['usuario'])) {
    header("Location: ./");
    exit();
}

include 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $stmt = $conn->prepare("SELECT * FROM USUARIO WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($senha, $user['Senha']) && $user['Ativo'] == 1) {
            $_SESSION['id'] = $user['Id'];
            $_SESSION['usuario'] = $user['Nome'];
            $_SESSION['tipo'] = $user['Tipo'];
            

            if($user['Tipo'] == 'ADMIN') {
                header("Location: dashboard");
            } else {
                header("Location: ./");
            }
            exit();
        } else {
            $error_message = "Email ou senha inválidos";
        }
    } else {
        $error_message = "Email ou senha inválidos";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Entrar</h2>
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>