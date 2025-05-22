<?php
session_start();
include 'database.php';
include 'functions/is_logado.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre - JundBio</title>
    <link rel="stylesheet" href="css/sobre.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <div class="about-section">
            <div class="about-header">
                <h1>Sobre o JundBio</h1>
                <p class="subtitle">Conectando pessoas e natureza na Serra do Japi</p>
            </div>

            <div class="about-content">
                <p>
                    O JundBio é uma plataforma dedicada à preservação e documentação da biodiversidade da Serra do Japi, 
                    um dos últimos remanescentes de Mata Atlântica do interior paulista. Nossa missão é conectar 
                    pesquisadores, especialistas e entusiastas da natureza em um espaço colaborativo para compartilhar 
                    conhecimento e promover a conservação ambiental.
                </p>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <h3>Documentação</h3>
                        <p>Registre avistamentos e contribua para o mapeamento da biodiversidade local</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Comunidade</h3>
                        <p>Conecte-se com especialistas e entusiastas da natureza</p>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa-solid fa-tree"></i>
                        </div>
                        <h3>Conservação</h3>
                        <p>Ajude a preservar a biodiversidade da Serra do Japi</p>
                    </div>
                </div>

                <p>
                    A plataforma permite que usuários compartilhem fotos e informações sobre espécies encontradas na Serra do Japi, 
                    contribuindo para um banco de dados colaborativo que auxilia pesquisadores e conservacionistas. 
                    Especialistas verificados garantem a precisão das informações compartilhadas.
                </p>

                <div class="team-section">
                    <h2>Nossa Equipe</h2>
                    <div class="team-grid">
                        <div class="team-member">
                            <img src="assets/team/member1.jpg" alt="Membro da equipe">
                            <h3>Luan Pascoal</h3>
                            <p>N/A</p>
                        </div>
                        <div class="team-member">
                            <img src="assets/team/member2.jpg" alt="Membro da equipe">
                            <h3>Tomás Wong</h3>
                            <p>N/A</p>
                        </div>
                        <div class="team-member">
                            <img src="assets/team/member3.jpg" alt="Membro da equipe">
                            <h3>Heitor Lima</h3>
                            <p>N/A</p>
                        </div>
                        <div class="team-member">
                            <img src="assets/team/member4.jpg" alt="Membro da equipe">
                            <h3>Eric Rodrigues</h3>
                            <p>N/A</p>
                        </div>
                        <div class="team-member">
                            <img src="assets/team/member5.jpg" alt="Membro da equipe">
                            <h3>Vinícius Tega</h3>
                            <p>N/A</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; ?>
</body>
</html> 