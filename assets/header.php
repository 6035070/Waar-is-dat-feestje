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
            } 
            elseif (in_array($current_page, ['register.php'])) {
                echo '<a href="../public/login.php">Login</a>';
            }
            else {
                // Normal navigation for all other pages
                echo '
                    <a href="../public/index.php">Home</a>
                    <a href="../public/recipeinputheader.php">Recipe Input</a>
                    <a href="../public/#.php">Saved Recipes</a>
                    <a href="../public/logout.php">Logout</a>

                ';
            }
        ?>
    </div>

</nav>



<style> 

.navlogo {
    flex-grow: 1;
}

.navlogo img {
    height: 60px;
    width: 100px;
}
.navbar {
    width: 100%;
    background-color: #fffef9; /* off-white background */
    display: flex;
    align-items: center;
    gap: 40px;
    justify-content: flex-end;
    padding-top: 1rem;
    padding-bottom: 1rem;
    font-family: 'Poppins', sans-serif;
    position: sticky;
    top: 0;
    z-index: 100;
}

.logo {
    height: 36px;
}

.nav-right {
    display: flex;
    gap: 2rem;
    padding-right: 2rem;
}

.navbar a {
    text-decoration: none;
    color: #432447ff;
    font-weight: 600;
    font-size: 0.95rem;
    position: relative;
    transition: color 0.2s ease;
}

.navbar a::after {
    content: "";
    position: absolute;
    width: 0%;
    height: 2px;
    bottom: -4px;
    left: 0;
    background-color: #8C4F96;
    transition: width 0.25s ease;
}

.navbar a:hover {
    color: #8C4F96;
}

.navbar a:hover::after {
    width: 100%;
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
        padding: 1rem 1.5rem;
    }

    .nav-right {
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .navbar a {
        font-size: 0.9rem;
    }
}

header {
    height
}


</style>