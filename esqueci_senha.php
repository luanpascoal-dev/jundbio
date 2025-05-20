<?php
session_start();
include 'database.php';

$codigo_gerado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $_SESSION['error'] = 'Por favor, insira seu e-mail.';
    } else {
        // Verificar se o e-mail existe no banco de dados
        $stmt = $conn->prepare("SELECT Id, Nome FROM USUARIO WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Gerar código numérico de 6 dígitos
            $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiracao = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Salvar código no banco
            $stmt = $conn->prepare("INSERT INTO RESET_SENHA (Id_Usuario, Token, Expiracao) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $usuario['Id'], $codigo, $expiracao);
            
            if ($stmt->execute()) {
                // Armazenar código na sessão
                $_SESSION['reset_code'] = $codigo;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_expiration'] = $expiracao;
                
                // Mostrar o código na tela
                $codigo_gerado = $codigo;
                $_SESSION['success'] = 'Código gerado com sucesso! Use-o para redefinir sua senha.';

            } else {
                $_SESSION['error'] = 'Erro ao processar sua solicitação. Por favor, tente novamente.';
            }
        } else {
            $_SESSION['error'] = 'E-mail não encontrado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <h1>Esqueci minha senha</h1>
            
            <?php include 'layouts/alerts.php'; ?>

            <?php if ($codigo_gerado): ?>
                <div class="code-display">
                    <h2>Seu código de verificação:</h2>
                    <div class="code-box">
                        <?php echo $codigo_gerado; ?>
                    </div>
                    <p class="code-info">
                        Este código expira em 15 minutos.<br>
                        <a href="verificar_codigo.php" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i> Ir para verificação
                        </a>
                    </p>
                </div>
            <?php else: ?>
                <div class="reset-form">
                    <p class="reset-info">
                        Digite seu e-mail cadastrado para receber um código de verificação.
                    </p>

                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       placeholder="Seu e-mail cadastrado">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-key"></i> Gerar código
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="reset-links">
                <a href="login.php" class="link">
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

    .code-display {
        text-align: center;
        margin: 2rem 0;
    }

    .code-display h2 {
        color: var(--text-dark);
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .code-box {
        background: #f8f9fa;
        border: 2px dashed var(--primary-green);
        border-radius: 8px;
        padding: 1.5rem;
        font-size: 2rem;
        font-weight: bold;
        letter-spacing: 0.5rem;
        color: var(--primary-green);
        margin: 1rem 0;
    }

    .code-info {
        color: var(--text-light);
        margin-top: 1rem;
    }

    .code-info .btn {
        margin-top: 1rem;
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