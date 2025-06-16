<?php
session_start();

$title = "Configurações da Conta";
$css = ['configuracoes']; // Vamos criar um CSS básico para esta página

include 'database.php';
include 'functions/is_logado.php'; //
include 'functions/get_usuario.php'; //

$usuario_id = $_SESSION['id'];
$usuario = get_usuario($usuario_id); //

// Processar formulário de alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'alterar_senha') {
        $senha_atual = $_POST['senha_atual'];
        $nova_senha = $_POST['nova_senha'];
        $confirmar_nova_senha = $_POST['confirmar_nova_senha'];

        if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_nova_senha)) {
            $_SESSION['error'] = "Todos os campos de senha são obrigatórios.";
        } elseif (!password_verify($senha_atual, $usuario['Senha'])) {
            $_SESSION['error'] = "A senha atual está incorreta.";
        } elseif (strlen($nova_senha) < 6) {
            $_SESSION['error'] = "A nova senha deve ter pelo menos 6 caracteres.";
        } elseif ($nova_senha !== $confirmar_nova_senha) {
            $_SESSION['error'] = "A nova senha e a confirmação não coincidem.";
        } else {
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE USUARIO SET Senha = ? WHERE Id = ?");
            $stmt->bind_param("si", $nova_senha_hash, $usuario_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Senha alterada com sucesso!";
            } else {
                $_SESSION['error'] = "Erro ao alterar a senha. Tente novamente.";
            }
            $stmt->close();
        }
        header("Location: configuracoes.php#secao-senha"); // Redireciona para a seção de senha
        exit;
    }

    // Processar solicitação de exclusão de conta
    if ($_POST['action'] === 'excluir_conta') {
        $senha_confirmacao_exclusao = $_POST['senha_confirmacao_exclusao'];

        if (empty($senha_confirmacao_exclusao)) {
            $_SESSION['error'] = "Você precisa confirmar sua senha para excluir a conta.";
        } elseif (!password_verify($senha_confirmacao_exclusao, $usuario['Senha'])) {
            $_SESSION['error'] = "Senha de confirmação incorreta.";
        } else {
            // Implementar a lógica de exclusão de conta
            // CUIDADO: Esta é uma ação destrutiva.
            // Opção 1: Desativar a conta (recomendado inicialmente)
            $stmt = $conn->prepare("UPDATE USUARIO SET Ativo = 0, Nome = 'Usuário Excluído', Email = CONCAT('excluido_', Id, '@jundbio.invalid'), Foto = NULL, Biografia = NULL, Ocupacao = NULL WHERE Id = ?");
            $stmt->bind_param("i", $usuario_id);

            // Opção 2: Excluir permanentemente (mais complexo devido a chaves estrangeiras)
            // $stmt = $conn->prepare("DELETE FROM USUARIO WHERE Id = ?");
            // $stmt->bind_param("i", $usuario_id);
            // Antes de excluir, você precisaria tratar as dependências:
            // - Postagens: SET Id_Usuario = NULL (se permitido) ou DELETE CASCADE (perigoso)
            // - Comentários: SET Id_Usuario = NULL ou DELETE CASCADE
            // - Curtidas: DELETE CASCADE
            // - Fotos associadas a postagens do usuário, etc.

            if ($stmt->execute()) {
                // Deslogar o usuário
                session_unset();
                session_destroy();
                setcookie(session_name(), '', time() - 3600, '/');

                // Não podemos definir $_SESSION['success'] aqui pois a sessão foi destruída
                // Redirecionar com uma query string de sucesso
                header("Location: login.php?conta_excluida=1");
                exit;
            } else {
                $_SESSION['error'] = "Erro ao tentar excluir a conta. Tente novamente.";
            }
            $stmt->close();
        }
        header("Location: configuracoes.php#secao-excluir"); // Redireciona para a seção de exclusão
        exit;
    }
}

?>

<?php
include 'layouts/header.php'; //
include 'layouts/navbar.php'; //
?>

<body>
    <div class="main-content">
        <div class="page-header">
            <div class="container">
                <h1>Configurações da Conta</h1>
                <p class="subtitle">Gerencie suas preferências e dados da conta.</p>
            </div>
        </div>

        <div class="container">
            <?php include 'layouts/alerts.php'; ?>

            <div class="config-container">
                <aside class="config-sidebar">
                    <nav class="config-nav">
                        <a href="#secao-geral" class="config-nav-item active"><i class="fas fa-cog"></i> Geral</a>
                        <a href="#secao-senha" class="config-nav-item"><i class="fas fa-lock"></i> Alterar Senha</a>
                        <!-- <a href="#secao-notificacoes" class="config-nav-item"><i class="fas fa-bell"></i> Notificações</a> -->
                        <!-- <a href="#secao-privacidade" class="config-nav-item"><i class="fas fa-user-shield"></i> Privacidade</a> -->
                        <a href="#secao-excluir" class="config-nav-item config-nav-item-danger"><i class="fas fa-trash-alt"></i> Excluir Conta</a>
                    </nav>
                </aside>

                <main class="config-content">
                    <section id="secao-geral" class="config-section">
                        <h2><i class="fas fa-cog"></i> Geral</h2>
                        <p>Ajustes gerais da sua conta e perfil.</p>
                        <div class="form-group">
                            <label>Nome de Usuário:</label>
                            <p><?php echo htmlspecialchars($usuario['Nome']); ?></p>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <p><?php echo htmlspecialchars($usuario['Email']); ?></p>
                        </div>
                        <a href="editar_perfil.php" class="btn btn-secondary"><i class="fas fa-edit"></i> Editar Perfil Completo</a>
                    </section>

                    <section id="secao-senha" class="config-section">
                        <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                        <form method="POST">
                            <input type="hidden" name="action" value="alterar_senha">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" class="form-control" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label for="confirmar_nova_senha">Confirmar Nova Senha</label>
                                <input type="password" id="confirmar_nova_senha" name="confirmar_nova_senha" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Nova Senha</button>
                        </form>
                    </section>

                    <section id="secao-notificacoes" class="config-section">
                        <h2><i class="fas fa-bell"></i> Preferências de Notificações</h2>
                        <p>Escolha como você gostaria de ser notificado. (Funcionalidade futura)</p>
                        <form>
                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notif_curtidas" checked disabled>
                                    <span>Receber notificações por email sobre novas curtidas em minhas postagens.</span>
                                </label>
                            </div>
                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notif_comentarios" checked disabled>
                                    <span>Receber notificações por email sobre novos comentários em minhas postagens.</span>
                                </label>
                            </div>
                            <div class="form-group checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notif_newsletter" disabled>
                                    <span>Receber newsletter e atualizações do JundBio.</span>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary" disabled><i class="fas fa-save"></i> Salvar Preferências de Notificação</button>
                             <small class="text-muted d-block mt-2">As opções de notificação ainda não estão ativas, mas estarão disponíveis em breve.</small>
                        </form>
                    </section>

                    <section id="secao-privacidade" class="config-section">
                        <h2><i class="fas fa-user-shield"></i> Privacidade</h2>
                        <p>Gerencie suas configurações de privacidade.</p>
                        <div class="form-group">
                            <label>Visibilidade do Perfil</label>
                            <select class="form-control" disabled>
                                <option selected>Público (Visível para todos)</option>
                                <option>Privado (Visível apenas para conexões - funcionalidade futura)</option>
                            </select>
                            <small class="text-muted d-block mt-1">Mais opções de privacidade estarão disponíveis em breve.</small>
                        </div>
                        <a href="privacidade.php" target="_blank" class="link-external">Leia nossa Política de Privacidade <i class="fas fa-external-link-alt"></i></a>
                    </section>

                    <section id="secao-excluir" class="config-section section-danger">
                        <h2><i class="fas fa-trash-alt"></i> Excluir Conta</h2>
                        <p class="warning-text"><strong>Atenção:</strong> Esta ação é irreversível. Ao excluir sua conta, todos os seus dados, incluindo postagens e comentários, serão anonimizados ou removidos permanentemente (conforme nossa política de retenção). Você perderá o acesso ao JundBio.</p>
                        <form method="POST" onsubmit="return confirm('TEM CERTEZA ABSOLUTA que deseja excluir sua conta? Esta ação não pode ser desfeita.');">
                            <input type="hidden" name="action" value="excluir_conta">
                            <div class="form-group">
                                <label for="senha_confirmacao_exclusao">Confirme sua Senha para Excluir</label>
                                <input type="password" id="senha_confirmacao_exclusao" name="senha_confirmacao_exclusao" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-user-times"></i> Excluir Minha Conta Permanentemente</button>
                        </form>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <?php include 'layouts/footer.php'; // ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.config-nav-item');
    const sections = document.querySelectorAll('.config-section');

    function setActiveSection(hash) {
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === hash) {
                link.classList.add('active');
            }
        });

        sections.forEach(section => {
            if ('#' + section.id === hash) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });
    }

    // Mostra a seção correta com base no hash da URL ou a primeira por padrão
    let currentHash = window.location.hash;
    if (!currentHash || !document.querySelector(currentHash)) {
        currentHash = '#secao-geral'; // Seção padrão
    }
    setActiveSection(currentHash);

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // e.preventDefault(); // Não previne o default para permitir que o hash mude na URL
            const targetId = this.getAttribute('href');
            setActiveSection(targetId);
            // Opcional: Rolar para o topo da seção
            // document.querySelector(targetId).scrollIntoView({ behavior: 'smooth' });
        });
    });

    // Ouve mudanças no hash da URL (botões de voltar/avançar do navegador)
    window.addEventListener('hashchange', function() {
        setActiveSection(window.location.hash);
    });
});
</script>

<style>
/* CSS para /configuracoes.php - Adicionar a um arquivo css/pages/configuracoes.css e incluir no header.php */
.config-container {
    display: flex;
    gap: 2rem; /* Aumentado o gap */
    margin-top: 2rem;
    margin-bottom: 2rem;
    align-items: flex-start; /* Alinha o sidebar e content no topo */
}

.config-sidebar {
    flex: 0 0 220px; /* Largura fixa para o sidebar */
    background-color: #fff;
    padding: 1.5rem; /* Aumentado o padding */
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}

.config-nav {
    display: flex;
    flex-direction: column;
    gap: 0.5rem; /* Espaço entre os itens de navegação */
}

.config-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem; /* Aumentado o gap para o ícone */
    padding: 0.8rem 1rem; /* Padding ajustado */
    color: var(--text-dark);
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500; /* Fonte um pouco mais forte */
    transition: background-color 0.2s ease, color 0.2s ease;
}

.config-nav-item i {
    width: 18px; /* Tamanho fixo para alinhar ícones */
    text-align: center;
    color: var(--text-light); /* Cor mais suave para ícones */
    transition: color 0.2s ease;
}

.config-nav-item:hover {
    background-color: var(--cream); /* Cor de hover mais suave */
    color: var(--primary-green);
}
.config-nav-item:hover i {
    color: var(--primary-green);
}

.config-nav-item.active {
    background-color: var(--primary-green);
    color: white;
    font-weight: 600; /* Mais destaque para o item ativo */
}
.config-nav-item.active i {
    color: white;
}

.config-nav-item-danger:hover {
    background-color: var(--danger-color);
    color: white;
}
.config-nav-item-danger:hover i {
    color: white;
}
.config-nav-item-danger.active {
    background-color: var(--danger-color);
    opacity: 0.8; /* Para diferenciar do hover */
}


.config-content {
    flex: 1;
    background-color: #fff;
    padding: 2rem; /* Aumentado o padding */
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e0e0e0;
}

.config-section {
    margin-bottom: 2.5rem; /* Aumentado o margin-bottom */
    padding-bottom: 2rem; /* Aumentado o padding-bottom */
    border-bottom: 1px solid #f0f0f0; /* Linha divisória mais sutil */
}
.config-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.config-section h2 {
    font-size: 1.5rem; /* Tamanho do título da seção */
    color: var(--primary-green);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.config-section h2 i {
    font-size: 1.3rem; /* Ícone do título da seção */
}

.config-section p {
    margin-bottom: 1.5rem;
    color: var(--text-light);
    line-height: 1.7; /* Maior espaçamento entre linhas */
}
.config-section .form-group {
    margin-bottom: 1.25rem; /* Ajustado margin-bottom para form-group */
}
.config-section .form-group label:not(.checkbox-label) {
    font-weight: 600; /* Labels dos forms mais fortes */
    color: var(--text-dark);
    margin-bottom: 0.35rem;
}

.form-control { /* Classe para inputs e selects */
    width: 100%;
    padding: 0.65rem 1rem; /* Padding ajustado */
    font-size: 0.95rem;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: var(--secondary-green); /* Cor de foco mais suave */
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(74, 120, 86, 0.25); /* Sombra de foco */
}
.form-control[disabled] {
    background-color: #e9ecef;
    opacity: 1;
}

.checkbox-group .checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.95rem;
    color: var(--text-dark);
}
.checkbox-group .checkbox-label input[type="checkbox"] {
    margin-right: 0.75rem; /* Mais espaço para o checkbox */
    width: auto; /* Reset do global.css */
    height: auto;
    accent-color: var(--primary-green); /* Cor do check */
}

.section-danger h2 {
    color: var(--danger-color);
}
.section-danger .warning-text {
    color: var(--danger-color);
    background-color: rgba(231, 76, 60, 0.05); /* Fundo sutil para o aviso */
    border: 1px solid rgba(231, 76, 60, 0.2);
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}
.section-danger .warning-text strong {
    font-weight: 600;
}

.link-external {
    color: var(--primary-green);
    text-decoration: none;
    font-size: 0.9rem;
}
.link-external i {
    margin-left: 0.25rem;
    font-size: 0.8rem;
}
.link-external:hover {
    text-decoration: underline;
}

/* Responsividade */
@media (max-width: 992px) {
    .config-sidebar {
        flex: 0 0 200px; /* Reduz a largura do sidebar */
    }
}

@media (max-width: 768px) {
    .config-container {
        flex-direction: column; /* Empilha sidebar e content */
        gap: 1.5rem;
    }
    .config-sidebar {
        width: 100%;
        flex-basis: auto; /* Permite que o sidebar ocupe a largura total */
    }
    .config-nav {
        flex-direction: row; /* Navegação horizontal no mobile */
        overflow-x: auto; /* Permite scroll horizontal se os itens não couberem */
        padding-bottom: 0.5rem; /* Espaço para a barra de rolagem */
    }
    .config-nav-item {
        white-space: nowrap; /* Impede que o texto do link quebre */
    }
    .config-content {
        padding: 1.5rem; /* Reduz o padding do conteúdo */
    }
    .config-section h2 {
        font-size: 1.3rem;
    }
}
</style>
</body>
</html>