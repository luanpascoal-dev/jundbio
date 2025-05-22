<?php
session_start();
include 'database.php';
include 'functions/is_logado.php';
include 'functions/get_especie.php';
include 'functions/get_image.php';
include 'functions/get_postagens.php';

$especie_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$especie_id) {
    $_SESSION['error'] = 'Espécie não encontrada';
    header('Location: especies');
    exit();
}

$especie = get_especie($especie_id);

if(!$especie) {
    $_SESSION['error'] = 'Espécie não encontrada';
    header('Location: especies');
    exit();
}

$foto = get_especie_image($especie_id);

// Buscar fotos adicionais da espécie
$stmt = $conn->prepare("SELECT URL FROM FOTO WHERE Id_Especie = ? AND Status = 'APROVADO' LIMIT 5");
$stmt->bind_param("i", $especie_id);
$stmt->execute();
$fotos_adicionais = $stmt->get_result();

// Buscar postagens relacionadas à espécie
$postagens = get_postagens_by_especie($especie_id);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($especie['NomeComum']) ?> - JundBio</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/verespecie.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <div class="container">
        <?php include 'layouts/alerts.php'; ?>

        <div class="especie-header">
            <div class="especie-grid">
                <div class="especie-imagem">
                    <?php if($foto && file_exists($foto)): ?>
                        <img src="<?= $foto ?>" alt="<?= htmlspecialchars($especie['NomeComum']) ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="especie-info">
                    <h1><?= htmlspecialchars($especie['NomeComum']) ?></h1>
                    <p class="nome-cientifico"><?= htmlspecialchars($especie['NomeCientifico']) ?></p>

                    <?php if($especie['StatusExtincao']): ?>
                        <span class="status-badge <?= strtolower(str_replace(' ', '-', $especie['StatusExtincao'])) ?>">
                            <?= htmlspecialchars($especie['StatusExtincao']) ?>
                        </span>
                    <?php endif; ?>

                    <div class="info-grid">
                        <div class="info-item">
                            <label><i class="fas fa-tag"></i> Classificação</label>
                            <p><?= htmlspecialchars($especie['Classificacao']) ?></p>
                        </div>

                        <?php if(isset($especie['Familia'])): ?>
                            <div class="info-item">
                                <label><i class="fas fa-sitemap"></i> Família</label>
                                <p><?= htmlspecialchars($especie['Familia']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($especie['Ordem'])): ?>
                            <div class="info-item">
                                <label><i class="fas fa-layer-group"></i> Ordem</label>
                                <p><?= htmlspecialchars($especie['Ordem']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($especie['Habitat'])): ?>
                            <div class="info-item">
                                <label><i class="fas fa-tree"></i> Habitat</label>
                                <p><?= htmlspecialchars($especie['Habitat']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-info-circle"></i> Descrição</h2>
            <p class="descricao"><?= nl2br(htmlspecialchars($especie['Descricao'])) ?></p>
        </div>

        <?php if($fotos_adicionais->num_rows > 0): ?>
            <div class="info-section">
                <h2><i class="fas fa-images"></i> Galeria de Fotos</h2>
                <div class="fotos-adicionais">
                    <?php while($foto = $fotos_adicionais->fetch_assoc()): ?>
                        <div class="foto-item">
                            <img src="<?= htmlspecialchars($foto['URL']) ?>" alt="Foto da espécie">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($postagens->num_rows > 0): ?>
            <div class="info-section">
                <h2><i class="fas fa-camera"></i> Avistamentos Recentes</h2>
                <div class="postagens-relacionadas">
                    <?php while($post = $postagens->fetch_assoc()): ?>
                        <div class="postagem-card">
                            <div class="postagem-header">
                                <?php if($post['Foto_Usuario'] && file_exists($post['Foto_Usuario'])): ?>
                                    <img src="<?= htmlspecialchars($post['Foto_Usuario']) ?>" alt="Foto de perfil">
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h4><?= htmlspecialchars($post['Nome_Usuario']) ?></h4>
                                    <small><?= date('d/m/Y', strtotime($post['DataHora_Envio'])) ?></small>
                                </div>
                            </div>
                            <div class="postagem-conteudo">
                                <p><?= htmlspecialchars(substr($post['Texto'], 0, 150)) ?>...</p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="mapa-section">
            <h2><i class="fas fa-map-marked-alt"></i> Distribuição Geográfica</h2>
            <div class="mapa-container" id="mapa-distribuicao"></div>
            <div class="mapa-info">
                <i class="fas fa-info-circle"></i>
                <span>Clique nos marcadores para ver mais informações sobre os avistamentos</span>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Coordenadas de Jundiaí
        const jundiaiCoords = [-23.1861, -46.8844];
        
        // Inicializa o mapa
        const mapa = L.map('mapa-distribuicao').setView(jundiaiCoords, 12);
        
        // Adiciona o layer do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(mapa);

        // Adiciona marcadores para cada postagem
        <?php 
        $postagens->data_seek(0); // Reseta o ponteiro do resultado
        while($post = $postagens->fetch_assoc()): 
            if(isset($post['Latitude']) && isset($post['Longitude'])):
        ?>
            L.marker([<?= $post['Latitude'] ?>, <?= $post['Longitude'] ?>])
                .bindPopup(`
                    <strong><?= htmlspecialchars($post['Nome_Usuario']) ?></strong><br>
                    <?= date('d/m/Y', strtotime($post['DataHora_Envio'])) ?><br>
                    <?= htmlspecialchars(substr($post['Texto'], 0, 100)) ?>...
                `)
                .addTo(mapa);
        <?php 
            endif;
        endwhile; 
        ?>
    </script>
</body>
</html> 