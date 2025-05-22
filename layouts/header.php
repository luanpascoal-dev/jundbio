
<nav class="navbar">
    <div class="container navbar-container">
        <a href="./" class="navbar-brand">
            <i class="fas fa-leaf"></i>
            <span>JundBio</span>
        </a>

        <button class="navbar-toggle" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="navbar-menu">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="./" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Início</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="especies" class="nav-link">
                        <i class="fas fa-paw"></i>
                        <span>Espécies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="mapa" class="nav-link">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Mapa</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sobre" class="nav-link">
                        <i class="fas fa-info-circle"></i>
                        <span>Sobre</span>
                    </a>
                </li>
            </ul>

            <div class="navbar-user">
                <?php if(isset($_SESSION['id'])): ?>
                    <div class="user-menu">
                        <button class="user-menu-button">
                            <?php if(isset($_SESSION['foto']) && !empty($_SESSION['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($_SESSION['foto']); ?>" alt="Foto de perfil" class="user-avatar">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($_SESSION['usuario']) ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown">
                            <?php if($_SESSION['tipo'] == 'ADMIN'): ?>
                                <a href="admin" class="dropdown-item">
                                    <i class="fa-solid fa-user-tie text-danger"></i>
                                    <span class="text-danger text-bold">Painel Admin</span>
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a href="perfil" class="dropdown-item">
                                <i class="fas fa-user"></i>
                                <span>Meu Perfil</span>
                            </a>
                            <a href="minhas-postagens" class="dropdown-item">
                                <i class="fas fa-list"></i>
                                <span>Minhas Postagens</span>
                            </a>
                            <a href="configuracoes" class="dropdown-item">
                                <i class="fas fa-cog"></i>
                                <span>Configurações</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sair</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login" class="btn btn-outline">Entrar</a>
                    <a href="cadastro" class="btn btn-primary">Cadastrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.querySelector('.navbar-toggle').addEventListener('click', function() {
        document.querySelector('.navbar-menu').classList.toggle('active');
        this.classList.toggle('active');
    });

    // User dropdown toggle
    const userMenuButton = document.querySelector('.user-menu-button');
    if (userMenuButton) {
        userMenuButton.addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.user-menu')) {
                document.querySelector('.user-dropdown').classList.remove('active');
            }
        });
    }
</script>

<style>
    
.default-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-green);
    font-size: 1.2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover; /* Fazer a imagem cobrir a área */
    border: 2px solid white;
}
</style>
