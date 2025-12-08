<header>
    <nav class="navbar">

        <article class="navlogo">
            <img src="../assets/img/waarishetfeestje_logo.png" alt="Leftovers Logo" class="logo">
        </article>

        <div class="nav-right">
            <?php
            // Get the current page name (e.g., login.php, register.php, resetpassword.php)
            $current_page = basename($_SERVER['PHP_SELF']);

            // Check if we are on one of the limited pages
            if (in_array($current_page, ['login.php'])) {
                echo '<a href="../public/register.php">Registreer</a>';
            } elseif (in_array($current_page, ['register.php'])) {
                echo '<a href="../public/login.php">Login</a>';
            }
            ?>
        </div>

                    <div id="accountPanel">
            </div>

    </nav>
</header>