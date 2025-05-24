<?php
session_start();

$title = 'Página não encontrada';
$error = '404';
$description = 'Ops! Parece que a página que você está procurando não existe ou foi movida.';
$css = ['error'];
$path = "../"

?>

<?php include '../layouts/header.php'; ?>
<body>
    <main class="main-content">
        <div class="error-container">
            <div class="error-content">
                <div class="error-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h1><?= $error ?></h1>
                <h2><?= $title ?></h2>
                <p><?= $description ?></p>
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