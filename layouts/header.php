<nav class="navbar">
    <div class="navbar-content">
        <a href="index.php" class="logo">JundBios</a>
        <div class="user-info text-light">
            <i class="fas fa-user-circle fa-2x"></i>
            <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
        </div>
        <a href="logout" class="logout-btn">Sair</a>
    </div>
</nav>