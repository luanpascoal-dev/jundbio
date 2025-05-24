<?php

session_start();

$title = "Nova Postagem";
$css = ['postar'];

include 'functions/is_logado.php';

include 'database.php';

include 'functions/get_usuario.php';

include 'functions/get_especie.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $texto = trim($_POST['texto']);
    $tipo = $_POST['tipo'];
    $especie_id = isset($_POST['especie_id']) && $_POST['especie_id'] != '' ? $_POST['especie_id'] : 1;
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
    $descricao_local = isset($_POST['descricao_local']) ? trim($_POST['descricao_local']) : null;

    $texto = str_replace(chr(10), '', $texto);
    
    // Validações
    if(empty($texto) || empty($tipo)) {
        $_SESSION['error'] = "Texto e tipo são campos obrigatórios";
    } else {
        try {
            $conn->begin_transaction();

            // 1. Inserir localização se fornecida
            $localizacao_id = null;
            if ($latitude && $longitude) {
                $stmt = $conn->prepare("INSERT INTO LOCALIZACAO (Latitude, Longitude, Descricao) VALUES (?, ?, ?)");
                $stmt->bind_param("dds", $latitude, $longitude, $descricao_local);
                $stmt->execute();
                $localizacao_id = $conn->insert_id;
            }

            // 2. Inserir postagem
            $stmt = $conn->prepare("INSERT INTO POSTAGEM (Tipo, Texto, Id_Usuario, Status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", strtoupper($tipo), $texto, $_SESSION['id'], 'APROVADO');
            $stmt->execute();
            $postagem_id = $conn->insert_id;


            // 3. Processar e inserir fotos
            if(isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $upload_dir = 'uploads/posts/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                foreach($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
                    $file_name = $_FILES['fotos']['name'][$key];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $new_file_name = uniqid() . '.' . $file_ext;
                    $file_path = $upload_dir . $new_file_name;


                    if(move_uploaded_file($tmp_name, $file_path)) {
                        $stmt = $conn->prepare("INSERT INTO FOTO (URL, Descricao, Id_Especie, Id_Localizacao, Id_Postagem, Status) VALUES (?, ?, ?, ?, ?, 'PENDENTE')");
                        $stmt->bind_param("ssiii", $file_path, $texto, $especie_id, $localizacao_id, $postagem_id);
                        $stmt->execute();
                    }
                }
            }

            $conn->commit();

            $_SESSION['success'] = "Postagem criada com sucesso! Aguardando aprovação.";
            

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Erro ao criar postagem";
        }
    }
}

// Buscar espécies para o select
$especies = $conn->query("SELECT Id, NomeComum, NomeCientifico FROM ESPECIE ORDER BY NomeComum");
?>



<?php 
include 'layouts/header.php'; 
include 'layouts/navbar.php';
?>

<body>
    <div class="main-content">
        <div class="form-container">
            <h2>Nova Postagem</h2>

            <?php include 'layouts/alerts.php'; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo de Postagem</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecione o tipo</option>
                            <option value="avistamento">Avistamento</option>
                            <option value="atropelamento">Atropelamento</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="especie_id">Espécie (opcional)</label>
                        <select id="especie_id" name="especie_id">
                            <option value="">Selecione a espécie</option>
                            <option value="1">
                                    <?php echo get_especie(1)['NomeComum']; ?>
                            </option>
                            <?php while($especie = $especies->fetch_assoc()): 
                                if($especie['Id'] != 1): ?>
                                <option value="<?php echo $especie['Id']; ?>">
                                    <?php echo htmlspecialchars($especie['NomeComum'] . ($especie['NomeCientifico'] != null ? ' (' . $especie['NomeCientifico'] . ')' : '')); ?>
                                </option>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="fotos">Fotos (máximo 5)</label>
                    <div class="foto-upload-container">
                        <div class="foto-preview-container" id="fotoPreviewContainer">
                            <!-- As previews das fotos serão inseridas aqui -->
                        </div>
                        <input type="file" id="fotos" name="fotos[]" accept="image/*" multiple onchange="previewFotos(this)">
                        <small>Formatos permitidos: JPG, JPEG, PNG. Tamanho máximo: 5MB por foto</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="texto">Descrição <span id="charCount">(0/1024)</span></label>
                    <textarea id="texto" name="texto" rows="4" maxlength="1024" required></textarea>
                </div>

                <div class="form-group">
                    <label>Localização</label>

                    <div class="mapa-section">
                        <div class="mapa-container" id="mapa-distribuicao"></div>
                        <div class="mapa-info">
                            <i class="fas fa-info-circle"></i>
                            <span>Clique em um lugar no mapa para definir a localização</span>
                        </div>
                    </div>
                    
                    <div class="loading">
                        <i class="fa-solid fa-spinner" id="loading-spinner"></i>
                        <span>Carregando...</span>
                    </div>

                    <div class="location-inputs">
                        <input type="number" name="latitude" step="0.0000001" placeholder="Latitude" class="location-input" required>
                        <input type="number" name="longitude" step="0.0000001" placeholder="Longitude" class="location-input" required>
                    </div>
                    <input type="text" name="descricao_local" placeholder="Descrição do local (opcional)" class="location-desc">
                    <button type="button" id="get-location" class="btn btn-light">
                        <i class="fas fa-map-marker-alt"></i> Usar minha localização
                    </button>
                    
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Publicar
                    </button>
                    <a href="./" class="btn btn-light">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>

        function previewFotos(input) {
            const container = document.getElementById('fotoPreviewContainer');
            const maxFotos = 5;
            
            if (input.files) {
                // Verificar número total de fotos
                const totalFotos = container.children.length + input.files.length;
                if (totalFotos > maxFotos) {
                    alert(`Você pode adicionar no máximo ${maxFotos} fotos.`);
                    input.value = '';
                    return;
                }

                Array.from(input.files).forEach((file, index) => {
                    if (file.size > 5 * 1024 * 1024) { // 5MB
                        alert(`A foto ${file.name} excede o tamanho máximo permitido de 5MB.`);
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'foto-preview';
                        previewDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="remove-foto" onclick="removerFoto(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        container.appendChild(previewDiv);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }

        function removerFoto(button) {
            const previewDiv = button.parentElement;
            previewDiv.remove();
        }

        // Atualizar o contador de fotos restantes
        function atualizarContadorFotos() {
            const container = document.getElementById('fotoPreviewContainer');
            const fotosAtuais = container.children.length;
            const maxFotos = 5;
            const fotosRestantes = maxFotos - fotosAtuais;
            
            const contador = document.createElement('div');
            contador.className = 'foto-contador';
            contador.textContent = `${fotosRestantes} foto(s) restante(s)`;
            
            // Remover contador anterior se existir
            const contadorAnterior = document.querySelector('.foto-contador');
            if (contadorAnterior) {
                contadorAnterior.remove();
            }
            
            document.querySelector('.foto-upload-container').appendChild(contador);
        }

        document.getElementById('texto').addEventListener('input', function() {
            const maxLength = this.getAttribute('maxlength');
            const currentLength = this.value.length;
            document.getElementById('charCount').textContent = `${currentLength}/${maxLength}`;
        });
    </script>

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

        var marker = null;
        
        function onMapClick(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        }

        function updateMarker(latitude, longitude) {
            if(marker == null) {
                marker = L.marker([latitude, longitude]).addTo(mapa);
            } else {
                marker.setLatLng([latitude, longitude]);
            }
            console.log(typeof latitude);
            console.log(typeof longitude);

            document.querySelector('input[name="latitude"]').value = new Number(latitude).toFixed(7);
            document.querySelector('input[name="longitude"]').value = new Number(longitude).toFixed(7);
        }

        document.getElementById('get-location').addEventListener('click', function() {
            document.querySelector('.loading').style.display = 'flex';
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    updateMarker(position.coords.latitude, position.coords.longitude);
                    document.querySelector('.loading').style.display = 'none';
                }, function(error) {
                    alert('Erro ao obter localização: ' + error.message);
                    document.querySelector('.loading').style.display = 'none';
                });
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
                document.querySelector('.loading').style.display = 'none';
            }
            
        });

        function updateInputs() {
            let lat = document.querySelector('input[name="latitude"]').value;
            let long = document.querySelector('input[name="longitude"]').value;
            updateMarker(Number(lat), Number(long));
        }

        document.querySelector('input[name="latitude"]').addEventListener('input', updateInputs);
        document.querySelector('input[name="longitude"]').addEventListener('input', updateInputs);


        mapa.on('click', onMapClick);
    </script>
</body>
</html> 