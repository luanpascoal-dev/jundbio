<?php
session_start();

$title = "Política de Privacidade";
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
            <h1>Política de Privacidade</h1>
            
            <div class="box-section">
                <h2>1. Coleta de Informações</h2>
                <p>O JundBio coleta as seguintes informações:</p>
                <ul>
                    <li>Nome completo</li>
                    <li>Endereço de e-mail</li>
                    <li>Data de registro</li>
                    <li>Conteúdo das postagens</li>
                    <li>Fotos e observações compartilhadas</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>2. Uso das Informações</h2>
                <p>As informações coletadas são utilizadas para:</p>
                <ul>
                    <li>Identificar e autenticar usuários</li>
                    <li>Personalizar a experiência do usuário</li>
                    <li>Compartilhar conteúdo relevante</li>
                    <li>Melhorar nossos serviços</li>
                    <li>Comunicar atualizações importantes</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>3. Proteção de Dados</h2>
                <p>O JundBio se compromete a:</p>
                <ul>
                    <li>Proteger suas informações pessoais</li>
                    <li>Não compartilhar dados com terceiros sem consentimento</li>
                    <li>Utilizar medidas de segurança adequadas</li>
                    <li>Respeitar a LGPD (Lei Geral de Proteção de Dados)</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>4. Cookies</h2>
                <p>Utilizamos cookies para:</p>
                <ul>
                    <li>Manter sua sessão ativa</li>
                    <li>Lembrar suas preferências</li>
                    <li>Melhorar a performance do site</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>5. Seus Direitos</h2>
                <p>Você tem direito a:</p>
                <ul>
                    <li>Acessar seus dados pessoais</li>
                    <li>Corrigir informações imprecisas</li>
                    <li>Solicitar a exclusão de seus dados</li>
                    <li>Revogar seu consentimento</li>
                </ul>
            </div>

            <div class="box-section">
                <h2>6. Contato</h2>
                <p>Para questões sobre privacidade, entre em contato através do e-mail: privacidade@jundbio.com.br</p>
            </div>
        </div>
    </div>


    <?php include 'layouts/footer.php'; ?>
</body>
</html> 