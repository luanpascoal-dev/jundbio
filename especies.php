<?php
session_start();

include 'database.php';

include 'functions/get_especie.php';

include 'functions/get_image.php';

// Buscar todas as espécies
$especies = get_especies();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espécies - JundBio</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/especies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'layouts/header.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Espécies da Serra do Japi</h1>
            <p class="subtitle">Conheça a rica biodiversidade da nossa região</p>
        </div>

        <div class="container">
            <div class="actions-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar espécies...">
                </div>
                
                <div class="filter-group">
                    <select class="filter-select" id="classificacaoFilter">
                        <option value="">Todas as classificações</option>
                        <option value="fauna">Fauna</option>
                        <option value="flora">Flora</option>
                    </select>
                    
                    <select class="filter-select" id="statusFilter">
                        <option value="">Todos os status</option>
                        <option value="em perigo">Em Perigo</option>
                        <option value="vulnerável">Vulnerável</option>
                        <option value="quase ameaçada">Quase Ameaçada</option>
                        <option value="pouco preocupante">Pouco Preocupante</option>
                    </select>
                </div>
            </div>

            <div class="especies-grid">

                <?php while ($especie = $especies->fetch_assoc()): 
                    $foto = get_especie_image($especie['Id']);
                    ?>
                    <div class="especie-card" 
                         data-classificacao="<?= strtolower($especie['Tipo']) ?>"
                         data-status="<?= strtolower($especie['StatusExtincao']) ?>">
                        <div class="especie-imagem">
                            <?php if ($foto && file_exists($foto)): ?>
                                <img src="<?= $foto ?>" alt="<?= htmlspecialchars($especie['NomeComum']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($especie['StatusExtincao']): ?>
                                <span class="status-badge <?= strtolower(str_replace(' ', '-', $especie['StatusExtincao'])) ?>">
                                    <?= htmlspecialchars($especie['StatusExtincao']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="especie-info">
                            <h3><?= htmlspecialchars($especie['NomeComum']) ?></h3>
                            <p class="nome-cientifico"><?= htmlspecialchars($especie['NomeCientifico']) ?></p>
                            
                            <div class="especie-meta">
                                <span class="classificacao">
                                    <i class="fas fa-tag"></i>
                                    <?= htmlspecialchars($especie['Classificacao']) ?>
                                </span>
                                
                                <?php if ($especie['Familia']): ?>
                                    <span class="familia">
                                        <i class="fas fa-sitemap"></i>
                                        <?= htmlspecialchars($especie['Familia']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="descricao">
                                <?= htmlspecialchars(substr($especie['Descricao'], 0, 150)) ?>...
                            </p>

                            <a href="verespecie?id=<?= $especie['Id'] ?>" class="btn btn-primary">
                                <i class="fas fa-info-circle"></i>
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($especies->num_rows === 0): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Nenhuma espécie encontrada</h3>
                    <p>Tente ajustar os filtros ou realizar uma nova busca</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'layouts/footer.php'; ?>

    <script>
        // Filtros e busca
        const searchInput = document.getElementById('searchInput');
        const classificacaoFilter = document.getElementById('classificacaoFilter');
        const statusFilter = document.getElementById('statusFilter');
        const cards = document.querySelectorAll('.especie-card');

        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase();
            const classificacaoValue = classificacaoFilter.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();

            cards.forEach(card => {
                const nome = card.querySelector('h3').textContent.toLowerCase();
                const descricao = card.querySelector('.descricao').textContent.toLowerCase();
                const classificacao = card.dataset.classificacao;
                const status = card.dataset.status;

                const matchesSearch = nome.includes(searchTerm) || descricao.includes(searchTerm);
                const matchesClassificacao = !classificacaoValue || classificacao === classificacaoValue;
                const matchesStatus = !statusValue || status === statusValue;

                card.style.display = matchesSearch && matchesClassificacao && matchesStatus ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterCards);
        classificacaoFilter.addEventListener('change', filterCards);
        statusFilter.addEventListener('change', filterCards);
    </script>
</body>
</html> 