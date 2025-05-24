
    <nav class="admin-nav">
        <div class="admin-nav-container">
            <div class="admin-nav-brand">
                <a href="../" class="admin-nav-logo">
                    <i class="fas fa-leaf"></i>
                    JundBio
                </a>
                <span class="admin-nav-subtitle">Painel Administrativo</span>
            </div>

            <div class="admin-nav-menu">
                <a href="../admin/" class="admin-nav-item">
                    <i class="fas fa-chart-line"></i>
                    Dashboard
                </a>
                <a href="../admin/especies" class="admin-nav-item">
                    <i class="fas fa-paw"></i>
                    Espécies
                </a>
                <a href="../admin/usuarios" class="admin-nav-item">
                    <i class="fas fa-users"></i>
                    Usuários
                </a>
                <a href="../admin/postagens" class="admin-nav-item">
                    <i class="fas fa-image"></i>
                    Postagens
                </a>
                <a href="../admin/comentarios" class="admin-nav-item">
                    <i class="fas fa-comments"></i>
                    Comentários
                </a>
            </div>

            <div class="admin-nav-user">
                <div class="admin-nav-user-info">
                    
                    <?php if(isset($_SESSION['foto']) && !empty($_SESSION['foto'])): ?>
                        <img src="../<?php echo htmlspecialchars($_SESSION['foto']); ?>" alt="Foto de perfil" class="user-avatar">
                    <?php else: ?>
                        <div class="default-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($_SESSION['usuario']) ?></span>
                </div>
                <div class="admin-nav-dropdown">
                    <a href="../perfil" class="admin-nav-dropdown-item">
                        <i class="fas fa-user-cog"></i>
                        Perfil
                    </a>
                    <a href="../admin/configuracoes" class="admin-nav-dropdown-item">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                        <a href="../logout" class="admin-nav-dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>
<style>
    
.default-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    border-radius: 50%;
    background: var(--primary-green);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--cream);
    font-size: 1.2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.user-avatar {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    border-radius: 50%;
    object-fit: cover; /* Fazer a imagem cobrir a área */
    border: 2px solid white;
}

</style>