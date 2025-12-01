<?php
include '../config/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute
    $stmt = $conn->prepare("SELECT password FROM userdata WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        if ($password === $db_password) {
            $message = "Login successful";
            $toastClass = "bg-success";
            // Start the session and redirect to the dashboard or home page
            session_start();
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Incorrect password";
            $toastClass = "bg-danger";
        }
    } else {
        $message = "Email not found";
        $toastClass = "bg-warning";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  

    
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #fefdf9;
        color: #222;
    }
    .flexbox-logging {
        margin: 0;
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .container {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        max-width: 900px;
        width: 100%;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 4px 25px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .login-section {
        flex: 1;
        padding: 3rem 2rem;

    }

    .image-section {
        flex: 1;
        height: 100%;
        background: url('../assets/img/partpicture_1.jpg') center/cover no-repeat;
        min-height: 500px;
    }

    h2 {
        font-weight: 700;
        font-size: 1.8rem;
        margin-bottom: 1.8rem;
        text-align: left;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    label {
        font-weight: 600;
        font-size: 0.9rem;
    }

    input {
        width: 100%;
        padding: 0.9rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: border 0.2s ease;
    }

    input:focus {
        border-color: #8C4F96;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 177, 79, 0.15);
    }

    .btn-primary {
        background-color: #5a3160ff;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 0.9rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
    }

    .btn-primary:hover {
        background-color: #58315fff;
        transform: translateY(-1px);
    }

    .links {
        text-align: center;
        margin-top: 1rem;
    }

    .links a {
        display: block;
        color: #8C4F96;
        text-decoration: none;
        font-weight: 600;
        margin-top: 0.3rem;
    }

    .links a:hover {
        text-decoration: underline;
    }

    .toast {
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 0.9rem;
        text-align: center;
    }

    .toast.error {
        background: #fdecea;
        color: #b00020;
    }

    .toast.warning {
        background: #fff8e1;
        color: #795548;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        .image-section {
            display: none;
        }
        .login-section {
            width: 100%;
        }
    }
</style>
</head>

<body>

<header>
<?php include '../assets/header.php'; ?>
</header>


<article class="flexbox-logging">


    <div class="container">
        <div class="login-section">
            <h2>Inloggen</h2>

            <?php if ($message): ?>
                <div class="toast <?php echo $toastClass; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div>
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" placeholder="Voer je e-mail in" required>
                </div>

                <div>
                    <label for="password">Wachtwoord</label>
                    <input type="password" id="password" name="password" placeholder="Voer je wachtwoord in" required>
                </div>

                <button type="submit" class="btn-primary">Inloggen</button>

                <div class="links">
                    <a href="./resetpassword.php">Wachtwoord vergeten?</a>
                    <a href="./register.php">Nieuw account aanmaken</a>
                </div>
            </form>
        </div>

        <div class="image-section"></div>
    </div>


    
    <script>
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>









<!-- the balloon intro -->

<script>
document.addEventListener("DOMContentLoaded", () => {
    const balloonSrc = "../assets/img/balloon.png";
    const balloonCount = 20;

    // hue ranges for purple / pink / warm tones
    const hueRanges = [
        [260, 300], // purple
        [300, 340], // magenta / pink
        [340, 360], // warm pink
        [0, 20]     // warm red/pink
    ];

    for (let i = 0; i < balloonCount; i++) {
        setTimeout(() => createBalloon(), i * 300); // staggered appearance
    }

    function createBalloon() {
        const balloon = document.createElement("img");
        balloon.src = balloonSrc;
        balloon.className = "floating-balloon";

        // random left spacing
        balloon.style.left = (Math.random() * 80 + 5) + "%";

        // random scale
        balloon.style.scale = (Math.random() * 0.4 + 0.6);

        // occasional horizontal flip
        if (Math.random() > 0.5) balloon.style.transform = "scaleX(-1)";

        // random animation duration
        const duration = Math.random() * 4 + 6; 
        balloon.style.animationDuration = duration + "s";

        // pick a warm/pink/purple hue band
        const range = hueRanges[Math.floor(Math.random() * hueRanges.length)];
        const hue = Math.floor(Math.random() * (range[1] - range[0]) + range[0]);

        // apply warm color filter
        balloon.style.filter = `hue-rotate(${hue}deg)`;

        document.body.appendChild(balloon);

        balloon.addEventListener("animationend", () => balloon.remove());
    }
});
</script>


<style>
.floating-balloon {
    position: fixed;
    bottom: -200px;
    pointer-events: none;
    width: 80px;
    opacity: 0;
    animation: floatUp linear forwards;
    z-index: -1;
}

@keyframes floatUp {
    0% {
        transform: translateY(0);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    100% {
        transform: translateY(-140vh);
        opacity: 0;
    }
}
</style>

</body>


</html>