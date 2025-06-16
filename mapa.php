<?php
session_start();

$title = "Mapa Interativo";
$css = ['mapa'];

include 'database.php';

include 'functions/get_postagens.php';
include 'functions/get_especie.php';

// Buscar posts com coordenadas para exibir no mapa
$posts_mapa = get_postagens_avistamento();

// Buscar posts com coordenadas para exibir no mapa
$atropelamento_mapa = get_postagens_atropelamento();


// Buscar espécies com coordenadas
$especies_mapa = get_especies_mapa();

?>

<?php
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>
    <div class="main-content">
        <div class="page-header">
            <div class="container">    
                <h1>🗺️ Mapa Interativo da Serra do Japi</h1>
                <p>Explore os registros e descobertas de nossa comunidade espalhados pela região</p>
            </div>
        </div>

        <div class="mapa-wrapper">
            <div class="mapa-controles">
                <div class="filtro-grupo">
                    <label>Filtros:</label>
                    <div class="filtro-grupo">
                        <input type="checkbox" id="mostrar-posts" class="checkbox-custom" checked>
                        <label for="mostrar-posts">Posts</label>
                    </div>
                    <div class="filtro-grupo">
                        <input type="checkbox" id="mostrar-especies" class="checkbox-custom" checked>
                        <label for="mostrar-especies">Espécies</label>
                    </div>
                    <div class="filtro-grupo">
                        <input type="checkbox" id="mostrar-atropelamento" class="checkbox-custom" checked>
                        <label for="mostrar-atropelamento">Atropelamentos</label>
                    </div>
                </div>
                
                <div class="legenda">
                    <div class="legenda-item">
                        <div class="legenda-cor legenda-posts"></div>
                        <span>Posts da Comunidade</span>
                    </div>
                    <div class="legenda-item">
                        <div class="legenda-cor legenda-especies"></div>
                        <span>Registros de Espécies</span>
                    </div>
                    <div class="legenda-item">
                        <div class="legenda-cor legenda-atropelamento"></div>
                        <span>Atropelamentos</span>
                    </div>
                </div>
                <div class="loading" id="loading">
                    <i class="fa-solid fa-spinner" id="loading-spinner"></i>
                    <span>Carregando...</span>
                </div>
            </div>
            
            <div id="mapa"></div>

            <div class="estatisticas">
                <h2 style="color: #2d5a27; margin-bottom: 20px;">Estatísticas do Mapa</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count($posts_mapa); ?></span>
                        <div class="stat-label">Postagens</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count($especies_mapa); ?></span>
                        <div class="stat-label">Espécies</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo count($atropelamento_mapa); ?></span>
                        <div class="stat-label">Atropelamentos</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php 
                            $autores = array_unique(array_merge(
                                array_column($posts_mapa, 'autor_nome'),
                                array_column($especies_mapa, 'autor_nome'),
                                array_column($atropelamento_mapa, 'autor_nome')
                            ));
                            echo count($autores);
                        ?></span>
                        <div class="stat-label">Colaboradores Ativos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script>
        // Dados dos posts
        const postsData = <?php echo json_encode($posts_mapa); ?>;
        const especiesData = <?php echo json_encode($especies_mapa); ?>;
        const atropelamentoData = <?php echo json_encode($atropelamento_mapa); ?>;

        // Inicializar mapa centralizado na Serra do Japi
        const mapa = L.map('mapa').setView([-23.2369, -46.9376], 12);

        // Adicionar camada do mapa
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(mapa);

        // Grupos de clusters para organizar os marcadores
        const postsCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div style="background: #e74c3c; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">' + cluster.getChildCount() + '</div>',
                    className: 'custom-cluster-icon',
                    iconSize: L.point(40, 40)
                });
            }
        });

        const atropelamentoCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div style="background: #3c94e7; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">' + cluster.getChildCount() + '</div>',
                    className: 'custom-cluster-icon',
                    iconSize: L.point(40, 40)
                });
            }
        });

        const especiesCluster = L.markerClusterGroup({
            iconCreateFunction: function(cluster) {
                return L.divIcon({
                    html: '<div style="background: #27ae60; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">' + cluster.getChildCount() + '</div>',
                    className: 'custom-cluster-icon',
                    iconSize: L.point(40, 40)
                });
            }
        });

        // Ícones personalizados
        const postIcon = L.divIcon({
            html: '<div style="background: #e74c3c; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            className: 'custom-marker',
            iconSize: [25, 25],
            iconAnchor: [12, 12]
        });

        const atropelamentoIcon = L.divIcon({
            html: '<div style="background: #3c94e7; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            className: 'custom-marker',
            iconSize: [25, 25],
            iconAnchor: [12, 12]
        });

        const especieIcon = L.divIcon({
            html: '<div style="background: #27ae60; width: 25px; height: 25px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>',
            className: 'custom-marker',
            iconSize: [25, 25],
            iconAnchor: [12, 12]
        });

        // Função para formatar data
        function formatarData(dataString) {
            const data = new Date(dataString);
            return data.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Adicionar marcadores de posts
        postsData.forEach(post => {
            const marker = L.marker([post.Latitude, post.Longitude], {icon: postIcon});
            
            let popupContent = `
                <div class="popup-content">
                    <div class="popup-header">📝 Post da Comunidade</div>
                    <div class="popup-info">
                        ${post.Texto ? post.Texto.substring(0, 100) + '...' : 'Sem descrição'}
                    </div>
            `;
            
            if (post.Foto) {
                popupContent += `<img src="${post.Foto}" alt="Imagem do post" class="popup-imagem">`;
            }
            
            let nome = post.autor_nome ? `<a href="perfil?id=${post.Id_Usuario}">${post.autor_nome}</a>` : '<b>Usuário Deletado</b>';

            popupContent += `
                    <div class="popup-autor">Por ${nome}</div>
                    <div class="popup-data">${formatarData(post.DataHora_Envio)}</div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            postsCluster.addLayer(marker);
        });

        // Adicionar marcadores de atropelamentos
        atropelamentoData.forEach(post => {
            const marker = L.marker([post.Latitude, post.Longitude], {icon: atropelamentoIcon});
            
            let popupContent = `
                <div class="popup-content">
                    <div class="popup-header">🚨 Atropelamento</div>
                    <div class="popup-info">
                        <strong>Nome Comum:</strong> ${post.NomeComum ? post.NomeComum : ''}
                        ${post.NomeCientifico ? `<strong>Nome Científico:</strong> ${post.NomeCientifico}<br>` : ''}
                        ${post.Familia ? `<strong>Família:</strong> ${post.Familia}<br>` : ''}
                        ${post.Descricao ? post.Descricao.substring(0, 100) + '...' : ''}
                    </div>
            `;
            let nome = post.autor_nome ? `<a href="perfil?id=${post.Id_Usuario}">${post.autor_nome}</a>` : '<b>Usuário Deletado</b>';

            popupContent += `
                    <div class="popup-autor">Por ${nome}</div>
                    <div class="popup-data">${formatarData(post.DataHora_Envio)}</div>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            atropelamentoCluster.addLayer(marker);
        });

        // Adicionar marcadores de espécies
        especiesData.forEach(especie => {
            const marker = L.marker([especie.Latitude, especie.Longitude], {icon: especieIcon});
            
            let popupContent = `
                <div class="popup-content">
                    <div class="popup-header">🌿 ${especie.NomeComum || especie.NomeCientifico}</div>
                    <div class="popup-info">
                        ${especie.NomeCientifico ? `<strong>Nome Científico:</strong> ${especie.NomeCientifico}<br>` : ''}
                        ${especie.Familia ? `<strong>Família:</strong> ${especie.Familia}<br>` : ''}
                        ${especie.Descricao ? especie.Descricao.substring(0, 100) + '...' : ''}
                    </div>
            `;
            
            if (especie.Foto) {
                popupContent += `<img src="${especie.Foto}" alt="${especie.NomeComum}" class="popup-imagem">`;
            }
            
            popupContent += `
                    <div class="popup-autor">Registrado por <a href="perfil?id=${especie.autor_id}">${especie.autor_nome}</a></div>
                    <div class="popup-data">${formatarData(especie.DataHora_Registro)}</div>
                </div>
            `;

            marker.bindPopup(popupContent);
            especiesCluster.addLayer(marker);
        });

        // Adicionar clusters ao mapa
        mapa.addLayer(postsCluster);
        mapa.addLayer(atropelamentoCluster);
        mapa.addLayer(especiesCluster);

        // Controles de filtro
        document.getElementById('mostrar-posts').addEventListener('change', function() {
            if (this.checked) {
                mapa.addLayer(postsCluster);
            } else {
                mapa.removeLayer(postsCluster);
            }
        });

        document.getElementById('mostrar-atropelamento').addEventListener('change', function() {
            if (this.checked) {
                mapa.addLayer(atropelamentoCluster);
            } else {
                mapa.removeLayer(atropelamentoCluster);
            }
        });

        document.getElementById('mostrar-especies').addEventListener('change', function() {
            if (this.checked) {
                mapa.addLayer(especiesCluster);
            } else {
                mapa.removeLayer(especiesCluster);
            }
        });

        // Adicionar controle de escala
        L.control.scale({
            position: 'bottomleft',
            metric: true,
            imperial: false
        }).addTo(mapa);

        // Adicionar controle de localização (se suportado)
        if (navigator.geolocation) {
            const locateControl = L.control({position: 'topright'});
            locateControl.onAdd = function(map) {
                const div = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                div.innerHTML = '<a href="#" style="background: white; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #333; font-size: 16px;">📍</a>';
                div.onclick = function() {
                    document.getElementById('loading').style.display = 'block';
                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        mapa.setView([lat, lng], 15);
                        L.marker([lat, lng]).addTo(mapa)
                            .bindPopup('Você está aqui!')
                            .openPopup();
                        document.getElementById('loading').style.display = 'none';
                    }, function(error) {
                        document.getElementById('loading').style.display = 'none';
                    });
                };
                return div;
            };
            locateControl.addTo(mapa);
        }

        // Ajustar o mapa para mostrar todos os pontos
        if (postsData.length > 0 || especiesData.length > 0 || atropelamentoData.length > 0) {
            const group = new L.featureGroup([postsCluster, especiesCluster, atropelamentoCluster]);
            mapa.fitBounds(group.getBounds().pad(0.1));
        }
    </script>
</body>
</html>