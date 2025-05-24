<?php
session_start();

$title = "Termos de Uso";
$css = ['termos_privacidade'];

include 'database.php';
?>


<?php
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>

    <div class="container">
        <div class="box-container">
            <h1>Termos de Uso</h1>
            
            <div class="box-section">
                <h2>1. Aceitação dos Termos</h2>
                <p>Ao acessar e usar o JundBio, você concorda em cumprir estes termos de uso. Se você não concordar com qualquer parte destes termos, não poderá acessar o site.</p>
            </div>

            <div class="box-section">
                <h2>2. Uso do Site</h2>
                <p>O JundBio é uma plataforma dedicada à biodiversidade da Serra do Japi. Os usuários podem:</p>
                <ul>
                    <li>Visualizar informações sobre espécies</li>
                    <li>Compartilhar observações e fotos</li>
                    <li>Participar de discussões</li>
                    <li>Contribuir com conteúdo relevante</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>3. Responsabilidades do Usuário</h2>
                <p>Os usuários são responsáveis por:</p>
                <ul>
                    <li>Fornecer informações verdadeiras e precisas</li>
                    <li>Respeitar a propriedade intelectual</li>
                    <li>Manter a confidencialidade de sua conta</li>
                    <li>Não compartilhar conteúdo inadequado</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>4. Conteúdo do Usuário</h2>
                <p>Ao postar conteúdo no JundBio, você:</p>
                <ul>
                    <li>Garante que possui os direitos necessários</li>
                    <li>Concede permissão para uso do conteúdo</li>
                    <li>Concorda com a moderação do conteúdo</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>5. Moderação</h2>
                <p>O JundBio reserva-se o direito de:</p>
                <ul>
                    <li>Remover conteúdo inadequado</li>
                    <li>Suspender ou encerrar contas</li>
                    <li>Moderar discussões</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>6. Alterações nos Termos</h2>
                <p>Estes termos podem ser atualizados periodicamente. Recomendamos que você os revise regularmente.</p>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 