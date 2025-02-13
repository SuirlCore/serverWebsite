<!-- navigation.php -->
 <?php
 if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userLevel = $_SESSION['userLevel'] ?? 0;
?>
<style>
body {
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    }

.nav-container {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

.menu-button {
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    flex-shrink: 0;
}

.menu-button:hover {
}

.user-info {
    margin-left: auto; /* Pushes the user-info section to the far right */
    font-size: 16px;
}

.user-name {
    font-weight: bold;
}

.nav-menu {
    display: none;
    position: absolute;
    top: 50px;
    left: 0;
    background-color: white;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    width: 200px;
    z-index: 1000;
}

.nav-menu a {
    display: block;
    padding: 10px;
    text-decoration: none;
    color: black;
}

.nav-menu a:hover {
    background-color: #f0f0f0;
}

</style>

<div class="nav-container">
    <button class="menu-button" onclick="toggleMenu()">☰ Menu</button>
    <div class="user-info">
            Apache on Odin: - <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
    </div>
    <div class="nav-menu" id="navMenu">
        <a href="welcome.php">Welcome Page</a>
        <a href="ftp.php">FTP Page</a>
        <a href="scripts/logout.php">Logout</a>
        <?php if ($userLevel == 1): ?>
            <a href="">Link to SU stuff</a>
        <?php endif; ?>
    </div>
</div>
<script>
    function toggleMenu() {
        const menu = document.getElementById('navMenu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }

    // Close the menu if clicked outside
    window.addEventListener('click', function(event) {
        const menu = document.getElementById('navMenu');
        const button = document.querySelector('.menu-button');
        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.style.display = 'none';
        }
    });
</script>

