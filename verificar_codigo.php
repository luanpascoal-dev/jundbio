<?php
session_start();
include 'database.php';

// Verificar se existe um código na sessão
if (!isset($_SESSION['reset_code'])) {
    header('Location: esqueci_senha.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_digitado = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
    
    if (empty($codigo_digitado)) {
        $mensagem = 'Por favor, insira o código de verificação.';
        $tipo_mensagem = 'erro';
    } else {
        // Verificar se o código está correto e não expirou
        if ($codigo_digitado === $_SESSION['reset_code'] && strtotime($_SESSION['reset_expiration']) > time()) {
            // Redirecionar para a página de redefinição de senha
            header('Location: redefinir_senha?token=' . $_SESSION['reset_code']);
            exit;
        } else {
            $mensagem = 'Código inválido ou expirado. Por favor, tente novamente.';
            $tipo_mensagem = 'erro';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <h1>Verificar Código</h1>
            
            <?php include 'layouts/alerts.php'; ?>

            <div class="reset-form">
                <p class="reset-info">
                    Digite o código de 6 dígitos gerado para sua conta.
                </p>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="codigo">Código de Verificação</label>
                        <div class="input-group">
                            <i class="fas fa-key"></i>
                            <input type="text" id="codigo" name="codigo" required 
                                   placeholder="Digite o código de 6 dígitos"
                                   maxlength="6" pattern="[0-9]{6}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-check"></i> Verificar Código
                    </button>
                </form>

                <div class="reset-links">
                    <a href="esqueci_senha.php" class="link">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
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
        text-align: center;
        letter-spacing: 0.5rem;
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

    <script>
    // Formatar o input para aceitar apenas números
    document.getElementById('codigo').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    </script>
</body>
</html> 