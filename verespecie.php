<?php
session_start();

$title = "Espécie";
$css = ['verespecie'];

include 'database.php';

include 'functions/get_especie.php';
include 'functions/get_image.php';
include 'functions/get_postagens.php';
include 'functions/get_usuario.php';

$especie_id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$especie_id || $especie_id == 1) {
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
$fotos_adicionais = get_especie_foto($especie_id, 5);

// Buscar postagens relacionadas à espécie
$postagens = get_postagens_by_especie($especie_id);

?>


<?php 
$title = htmlspecialchars($especie['NomeComum']);
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>

    <div class="container">
        <?php include 'layouts/alerts.php'; ?>

        <div class="especie-header card-white">
            <div class="especie-grid grid-2cols">
                <div class="especie-imagem img-rounded">
                    <?php if($foto && file_exists($foto)): ?>
                        <img src="<?= $foto ?>" class="img-cover" alt="<?= htmlspecialchars($especie['NomeComum']) ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-leaf"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="especie-info">
                    <h1 class="title-large"><?= htmlspecialchars($especie['NomeComum']) ?></h1>
                    <p class="nome-cientifico text-light italic"><?= htmlspecialchars($especie['NomeCientifico']) ?></p>

                    <?php if($especie['StatusExtincao']): ?>
                        <span class="status-badge badge badge-<?= strtolower(str_replace(' ', '-', $especie['StatusExtincao'])) ?>">
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

        <div class="info-section card-white">
            <h2><i class="fas fa-info-circle"></i> Descrição</h2>
            <p class="descricao"><?= nl2br(htmlspecialchars($especie['Descricao'])) ?></p>
        </div>

        <?php if($fotos_adicionais->num_rows > 0): ?>
            <div class="info-section card-white">
                <h2><i class="fas fa-images"></i> Galeria de Fotos</h2>
                <div class="fotos-adicionais">
                    <?php while($foto = $fotos_adicionais->fetch_assoc()): ?>
                        <div class="foto-item img-rounded">
                            <img src="<?= htmlspecialchars($foto['URL']) ?>" class="img-cover" alt="Foto da espécie">
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if($postagens->num_rows > 0): ?>
            <div class="info-section card-white">
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
                                    <h4><?= htmlspecialchars($post['Nome_Usuario']) ?>
                                    <?php if(is_especialista($post['Id_Usuario'])): ?>
                                            <i class="fas fa-check-circle verified-badge" title="Especialista verificado"></i>
                                        <?php endif; ?>
                                </h4>
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

        <div class="mapa-section card-white">
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
        const jundiaiCoords = [-23.2369, -46.9376];
        
        // Inicializa o mapa
        const mapa = L.map('mapa-distribuicao').setView(jundiaiCoords, 12);
        
        // Adiciona o layer do OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
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
                mapa.setView([<?= $post['Latitude'] ?>, <?= $post['Longitude'] ?>], 14);
        <?php 
            endif;
        endwhile; 
        ?>


        // Adicionar controle de escala
        L.control.scale({
            position: 'bottomleft',
            metric: true,
            imperial: false
        }).addTo(mapa);
    </script>
</body>
</html> 