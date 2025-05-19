<?php

session_start();

include 'functions/is_logado.php';

include 'database.php';

include 'functions/get_usuario.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $texto = trim($_POST['texto']);
    $foto = $_POST['foto'];
    $tipo = $_POST['tipo'];
    
    // Validações
    if(empty($texto) || empty($tipo)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {

        $stmt = $conn->prepare("INSERT INTO POSTAGEM (Tipo, Texto, Id_Usuario) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $tipo, $texto, $usuario);

        if($stmt->execute()) {
            $success_message = "Usuário criado com sucesso!";
            // Limpar os campos após sucesso
            $nome = $usuario = $email = '';
        } else {
            $error_message = "Erro ao criar usuário: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JundBio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>

<?php include 'layouts/header.php'; ?>

<body>

    <div class="main-content">
        <h2>Postagem</h2>

        <div class="form-container">

            <form method="POST">
                <div class="form-group">
                    <label for="foto">Foto</label>
                    <input type="image" id="foto" name="foto" required>
                </div>

                <div class="form-group">
                    <label for="texto">Texto</label>
                    <input type="text" id="texto" name="texto" equired>
                </div>

                <div class="form-group">
                    <label for="nivel_acesso">Tipo</label>
                    <select id="nivel_acesso" name="nivel_acesso">
                        <option value="">Selecione um Tipo</option>
                        <option value="avistamento">Avistamento</option>
                        <option value="atropelamento">Atropelamento</option>
                    </select>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Postar</button>
                    <a href="./" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    </div>

</body>
</html> 