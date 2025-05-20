<?php

session_start();

include 'functions/is_logado.php';

include 'database.php';

include 'functions/get_usuario.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $texto = trim($_POST['texto']);
    $tipo = $_POST['tipo'];
    $especie_id = isset($_POST['especie_id']) && $_POST['especie_id'] != '' ? $_POST['especie_id'] : 1;
    $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
    $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;
    $descricao_local = isset($_POST['descricao_local']) ? trim($_POST['descricao_local']) : null;
    
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
            $stmt = $conn->prepare("INSERT INTO POSTAGEM (Tipo, Texto, Id_Usuario) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $tipo, $texto, $_SESSION['id']);
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
$especies = $conn->query("SELECT Id, NomeComum, NomeCientifico FROM ESPECIE WHERE NomeComum != 'Desconhecido' ORDER BY NomeComum");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Postagem - JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/postar.css">
</head>

<?php include 'layouts/header.php'; ?>

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
                            <?php while($especie = $especies->fetch_assoc()): ?>
                                <option value="<?php echo $especie['Id']; ?>">
                                    <?php echo htmlspecialchars($especie['NomeComum'] . ' (' . $especie['NomeCientifico'] . ')'); ?>
                                </option>
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
                    <label for="texto">Descrição</label>
                    <textarea id="texto" name="texto" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label>Localização</label>
                    <div class="location-inputs">
                        <input type="number" step="any" name="latitude" placeholder="Latitude" class="location-input">
                        <input type="number" step="any" name="longitude" placeholder="Longitude" class="location-input">
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
        document.getElementById('get-location').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.querySelector('input[name="latitude"]').value = position.coords.latitude;
                    document.querySelector('input[name="longitude"]').value = position.coords.longitude;
                }, function(error) {
                    alert('Erro ao obter localização: ' + error.message);
                });
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
            }
        });

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
    </script>

</body>
</html> 