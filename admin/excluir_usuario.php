<?php
session_start();

$path = "../"; // Definindo o caminho para includes

include '../database.php';
include '../functions/is_logado.php'; //
include '../functions/is_admin.php';   //
include_once '../functions/get_usuario.php'; // Para buscar dados do usuário antes de excluir

$usuario_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        $_SESSION['error'] = "ID de usuário inválido para exclusão.";
        header('Location: usuarios');
        exit;
    }
    $usuario_id = (int)$_POST['id'];
}


// Validações Críticas
if ($usuario_id <= 0) {
    $_SESSION['error'] = "ID de usuário inválido.";
    header('Location: usuarios');
    exit;
}

if ($usuario_id === 1) {
    $_SESSION['error'] = "O administrador principal (ID 1) não pode ser excluído.";
    header('Location: usuarios');
    exit;
}

if ($usuario_id === $_SESSION['id']) {
    $_SESSION['error'] = "Você não pode excluir sua própria conta através desta funcionalidade.";
    header('Location: usuarios');
    exit;
}

// Buscar dados do usuário para, por exemplo, excluir a foto de perfil do servidor
$usuario = get_usuario($usuario_id); 

if (!$usuario) {
    $_SESSION['error'] = "Usuário não encontrado para exclusão.";
    header('Location: usuarios');
    exit;
}

// Iniciar transação para garantir atomicidade se houver múltiplas operações
$conn->begin_transaction();

try {
    // 1. (Opcional) Excluir a foto de perfil do servidor
    if (!empty($usuario['Foto']) && file_exists($path . $usuario['Foto'])) {
        if (!unlink($path . $usuario['Foto'])) {
            // Logar o erro mas não necessariamente impedir a exclusão do usuário do DB
            error_log("Falha ao excluir arquivo de foto do servidor: " . $path . $usuario['Foto']);
            // $_SESSION['warning'] = "Atenção: Não foi possível excluir o arquivo da foto de perfil do servidor, mas o usuário será removido do banco.";
        }
    }

    // 2. Excluir o usuário do banco de dados
    // As chaves estrangeiras com ON DELETE CASCADE e ON DELETE SET NULL cuidarão das tabelas relacionadas.
    $stmt = $conn->prepare("DELETE FROM USUARIO WHERE Id = ?");
    if (!$stmt) {
        throw new Exception("Erro na preparação da query de exclusão: " . $conn->error);
    }
    $stmt->bind_param("i", $usuario_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            $_SESSION['success'] = "Usuário '" . htmlspecialchars($usuario['Nome']) . "' (ID: {$usuario_id}) foi excluído com sucesso.";
        } else {
            // Isso pode acontecer se o usuário já foi excluído em outra requisição
            $conn->rollback();
            $_SESSION['error'] = "Nenhum usuário encontrado com o ID {$usuario_id} para excluir, ou já foi excluído.";
        }
    } else {
        throw new Exception("Erro ao executar a exclusão do usuário: " . $stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Falha ao excluir usuário: ";
    // Log detalhado do erro no servidor
    error_log("Erro ao excluir usuário ID {$usuario_id}: " . $e->getMessage());
}

header('Location: usuarios');
exit;
?>