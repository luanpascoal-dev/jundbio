<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - JundBio</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/error.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <main class="main-content">
        <div class="error-container">
            <div class="error-content">
                <div class="error-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h1>404</h1>
                <h2>Página não encontrada</h2>
                <p>Ops! Parece que a página que você está procurando não existe ou foi movida.</p>
                <div class="error-actions">
                    <a href="javascript:history.back()" class="btn btn-light">
                        <i class="fas fa-arrow-left"></i>
                        Voltar para a página anterior
                    </a>
                    <a href="./" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Voltar para a página inicial
                    </a>
                </div>
            </div>
        </div>
    </main>

</body>
</html> 