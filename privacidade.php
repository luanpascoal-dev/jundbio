<?php
session_start();
include 'database.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="privacy-container">
            <h1>Política de Privacidade</h1>
            
            <div class="privacy-section">
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

            <div class="privacy-section">
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

            <div class="privacy-section">
                <h2>3. Proteção de Dados</h2>
                <p>O JundBio se compromete a:</p>
                <ul>
                    <li>Proteger suas informações pessoais</li>
                    <li>Não compartilhar dados com terceiros sem consentimento</li>
                    <li>Utilizar medidas de segurança adequadas</li>
                    <li>Respeitar a LGPD (Lei Geral de Proteção de Dados)</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>4. Cookies</h2>
                <p>Utilizamos cookies para:</p>
                <ul>
                    <li>Manter sua sessão ativa</li>
                    <li>Lembrar suas preferências</li>
                    <li>Melhorar a performance do site</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>5. Seus Direitos</h2>
                <p>Você tem direito a:</p>
                <ul>
                    <li>Acessar seus dados pessoais</li>
                    <li>Corrigir informações imprecisas</li>
                    <li>Solicitar a exclusão de seus dados</li>
                    <li>Revogar seu consentimento</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>6. Contato</h2>
                <p>Para questões sobre privacidade, entre em contato através do e-mail: privacidade@jundbio.com.br</p>
            </div>
        </div>
    </div>

    <style>
    .privacy-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .privacy-container h1 {
        color: var(--primary-green);
        margin-bottom: 2rem;
        text-align: center;
    }

    .privacy-section {
        margin-bottom: 2rem;
    }

    .privacy-section h2 {
        color: var(--text-dark);
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .privacy-section p {
        color: var(--text-light);
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .privacy-section ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        color: var(--text-light);
    }

    .privacy-section li {
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    @media (max-width: 768px) {
        .privacy-container {
            margin: 1rem;
            padding: 1.5rem;
        }
    }
    </style>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 