<?php
session_start();
include 'database.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="terms-container">
            <h1>Termos de Uso</h1>
            
            <div class="terms-section">
                <h2>1. Aceitação dos Termos</h2>
                <p>Ao acessar e usar o JundBio, você concorda em cumprir estes termos de uso. Se você não concordar com qualquer parte destes termos, não poderá acessar o site.</p>
            </div>

            <div class="terms-section">
                <h2>2. Uso do Site</h2>
                <p>O JundBio é uma plataforma dedicada à biodiversidade da Serra do Japi. Os usuários podem:</p>
                <ul>
                    <li>Visualizar informações sobre espécies</li>
                    <li>Compartilhar observações e fotos</li>
                    <li>Participar de discussões</li>
                    <li>Contribuir com conteúdo relevante</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>3. Responsabilidades do Usuário</h2>
                <p>Os usuários são responsáveis por:</p>
                <ul>
                    <li>Fornecer informações verdadeiras e precisas</li>
                    <li>Respeitar a propriedade intelectual</li>
                    <li>Manter a confidencialidade de sua conta</li>
                    <li>Não compartilhar conteúdo inadequado</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>4. Conteúdo do Usuário</h2>
                <p>Ao postar conteúdo no JundBio, você:</p>
                <ul>
                    <li>Garante que possui os direitos necessários</li>
                    <li>Concede permissão para uso do conteúdo</li>
                    <li>Concorda com a moderação do conteúdo</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>5. Moderação</h2>
                <p>O JundBio reserva-se o direito de:</p>
                <ul>
                    <li>Remover conteúdo inadequado</li>
                    <li>Suspender ou encerrar contas</li>
                    <li>Moderar discussões</li>
                </ul>
            </div>

            <div class="terms-section">
                <h2>6. Alterações nos Termos</h2>
                <p>Estes termos podem ser atualizados periodicamente. Recomendamos que você os revise regularmente.</p>
            </div>
        </div>
    </div>

    <style>
    .terms-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .terms-container h1 {
        color: var(--primary-green);
        margin-bottom: 2rem;
        text-align: center;
    }

    .terms-section {
        margin-bottom: 2rem;
    }

    .terms-section h2 {
        color: var(--text-dark);
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .terms-section p {
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .terms-section ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        color: var(--text-light);
    }

    .terms-section li {
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    @media (max-width: 768px) {
        .terms-container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
    </style>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 