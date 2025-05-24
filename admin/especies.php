<?php
session_start();

$title = "Gerenciar Espécies";
$css = ['admin'];
$path = "../";

include '../functions/is_logado.php';
include '../database.php';

include '../functions/is_admin.php';

include '../functions/get_especie.php';
include '../functions/insert_especie.php';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nome_comum = trim($_POST['nome_comum']);
    $nome_cientifico = trim($_POST['nome_cientifico']);
    $familia = trim($_POST['familia']);
    $classificacao = trim($_POST['classificacao']);
    $descricao = trim($_POST['descricao']);
    $status_extincao = trim($_POST['status_extincao']);
    $tipo = trim($_POST['tipo']);

    if (empty($nome_comum) || empty($nome_cientifico) || empty($familia) || empty($classificacao) || empty($tipo) || empty($status_extincao)) {
        $_SESSION['error'] = "Campos obrigatórios não preenchidos";
    } else {
        try {

            $insert = insert_especie($nome_comum, $nome_cientifico, $familia, $classificacao, $descricao, $status_extincao, $tipo);

            if ($insert) 
                $_SESSION['success'] = "Espécie cadastrada com sucesso!";
            else 
                throw new Exception($conn->error);
            
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao cadastrar espécie";
        }
    }
}

// Buscar todas as espécies
$especies = get_especies();

?>

<?php 
include '../layouts/header.php'; 
include '../layouts/navbar_admin.php';
?>

<body>
    <div class="main-content">
        <div class="admin-container">
            <h2>Gerenciar Espécies</h2>

            <?php include '../layouts/alerts.php'; ?>

            <div class="admin-grid">
                <!-- Formulário de Cadastro -->
                <div class="admin-form">
                    <h3>Nova Espécie</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="nome_comum">Nome Comum *</label>
                            <input type="text" id="nome_comum" name="nome_comum" required>
                        </div>

                        <div class="form-group">
                            <label for="nome_cientifico">Nome Científico *</label>
                            <input type="text" id="nome_cientifico" name="nome_cientifico" required>
                        </div>

                        <div class="form-group">
                            <label for="familia">Família *</label>
                            <input type="text" id="familia" name="familia" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="classificacao">Classificação *</label>
                            <input type="text" id="classificacao" name="classificacao" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo">Tipo *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="Fauna">Fauna</option>
                                <option value="Flora">Flora</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="status_extincao">Status de Extinção *</label>
                            <select id="status_extincao" name="status_extincao">
                                <option value="">Selecione...</option>
                                <option value="Em Perigo">Em Perigo</option>
                                <option value="Vulnerável">Vulnerável</option>
                                <option value="Quase Ameaçada">Quase Ameaçada</option>
                                <option value="Pouco Preocupante">Pouco Preocupante</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea id="descricao" name="descricao" rows="4"></textarea>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cadastrar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Lista de Espécies -->
                <div class="admin-list">
                    <h3>Espécies Cadastradas</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nome Comum</th>
                                    <th>Nome Científico</th>
                                    <th>Família</th>
                                    <th>Classificação</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($especie = $especies->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($especie['NomeComum']); ?></td>
                                        <td><em><?php echo htmlspecialchars($especie['NomeCientifico']); ?></em></td>
                                        <td><?php echo htmlspecialchars($especie['Familia']); ?></td>
                                        <td><?php echo htmlspecialchars($especie['Classificacao']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $especie['StatusExtincao'])); ?>">
                                                <?php echo htmlspecialchars($especie['StatusExtincao']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="editar_especie?id=<?php echo $especie['Id']; ?>" class="btn-icon" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="excluir_especie?id=<?php echo $especie['Id']; ?>" class="btn-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta espécie?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 