<?php
require_once "../config/db_connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Get upcoming event */
$today = date('Y-m-d');
$eventStmt = $conn->prepare("
    SELECT * FROM events
    WHERE (user_id = ? OR user_id IS NULL)
    AND event_date >= ?
    ORDER BY event_date ASC
    LIMIT 1
");
$eventStmt->bind_param("is", $user_id, $today);
$eventStmt->execute();
$upcomingEvent = $eventStmt->get_result()->fetch_assoc();

/* Get ticket count */
$ticketStmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM tickets
    WHERE user_id = ?
");
$ticketStmt->bind_param("i", $user_id);
$ticketStmt->execute();
$ticketCount = $ticketStmt->get_result()->fetch_assoc()['total'];

/* Popular events: top 5 by sign-ups */
$popularEvents = $conn->query("
    SELECT e.*, COUNT(t.id) AS signup_count
    FROM events e
    LEFT JOIN tickets t ON t.event_id = e.id
    GROUP BY e.id
    ORDER BY signup_count DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(to bottom, #ffffff, #f1bbffff);

            padding: 10px;
            margin: 0;
            height: 100vh;
        }

        h2 {
            font-size: 20px;
        }

        /* POPULAR EVENTS CAROUSEL */
        .popular-events {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .popular-events::-webkit-scrollbar {
            height: 3px;
        }

        .popular-events::-webkit-scrollbar-thumb {
            background: #ffffffff;
            border-radius: 4px;
        }

        .popular-events::-webkit-scrollbar-track {
            background: transparent;
        }

        .popular-events .event-card {
            flex: 0 0 220px;
            background: white;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .popular-events .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .event-card img {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            aspect-ratio: 2/3;
            margin-bottom: 10px;
        }

        .event-card h3 {
            margin: 0 0 6px 0;
            font-size: 16px;
        }

        .event-card p {
            margin: 4px 0;
            font-size: 14px;
            color: #555;
        }

        /* GRID WIDGETS */
        .widgetholder {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .widget {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
        }

        .widget h3 {
            margin: 0 0 10px;
            font-size: 18px;
        }

        .widget p {
            margin: 6px 0;
            color: #555;
        }

        .widget-icon {
            font-size: 32px;
            position: absolute;
            top: 18px;
            right: 20px;
            opacity: 0.15;
        }

        .big-number {
            font-size: 36px;
            font-weight: 700;
            margin-top: 10px;
        }

       .event-card a {
            background: #5a3160;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <?php include '../assets/header.php'; ?>

    <h2>Events everyone is going to </h2>
    <section class="popular-events">
        <?php while ($event = $popularEvents->fetch_assoc()): ?>
            <div class="event-card">
                <img src="<?= $event['img'] ?: '../assets/img/poster-placeholder.png' ?>" alt="Event poster">
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p><?= $event['event_date'] ?> | <?= $event['start_time'] ?>-<?= $event['end_time'] ?></p>
                <p><strong><?= $event['signup_count'] ?></strong> people signed up</p>
                <a href="./postGenerator.php?event=<?= $event['id'] ?>" id="generatePost">Generate post</a>
            </div>
        <?php endwhile; ?>
    </section>
    <br>
    <br>

    <div class="widgetholder">
        <!-- UPCOMING EVENT -->
        <div class="widget">
            <div class="widget-icon">📅</div>
            <h3>Upcoming Event</h3>
            <?php if ($upcomingEvent): ?>
                <p><strong><?= htmlspecialchars($upcomingEvent['title']) ?></strong></p>
                <p><?= $upcomingEvent['event_date'] ?></p>
                <p><?= $upcomingEvent['start_time'] ?> - <?= $upcomingEvent['end_time'] ?></p>
            <?php else: ?>
                <p>No upcoming events</p>
            <?php endif; ?>
        </div>

        <!-- TICKETS -->
        <div class="widget">
            <div class="widget-icon">🎟</div>
            <h3>Your Tickets</h3>
            <div class="big-number"><?= $ticketCount ?></div>
            <p>Total tickets owned</p>
        </div>

        <!-- WEATHER WIDGET -->
        <div class="widget" id="weatherWidget">
            <div class="widget-icon">⛅</div>
            <h3>Weather</h3>
            <p id="weatherTemp"><strong>--°C</strong></p>
            <p id="weatherDesc">Loading...</p>
        </div>
    </div>

    <script>
        const apiKey = "YOUR_OPENWEATHERMAP_KEY"; // replace with your key
        const city = "Amsterdam";
        const units = "metric";

        fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&units=${units}&appid=${apiKey}`)
            .then(res => res.json())
            .then(data => {
                const temp = Math.round(data.main.temp);
                const desc = data.weather[0].description;
                document.getElementById('weatherTemp').innerHTML = `<strong>${temp}°C</strong>`;
                document.getElementById('weatherDesc').innerText = desc.charAt(0).toUpperCase() + desc.slice(1);
                const icon = data.weather[0].icon;
                const iconMap = {
                    "01d": "☀️", "01n": "🌙",
                    "02d": "🌤", "02n": "🌤",
                    "03d": "☁️", "03n": "☁️",
                    "04d": "☁️", "04n": "☁️",
                    "09d": "🌧", "09n": "🌧",
                    "10d": "🌦", "10n": "🌦",
                    "11d": "⛈", "11n": "⛈",
                    "13d": "❄️", "13n": "❄️",
                    "50d": "🌫", "50n": "🌫"
                };
                document.querySelector('#weatherWidget .widget-icon').innerText = iconMap[icon] || "🌤";
            })
            .catch(err => {
                console.error("Weather API error:", err);
                document.getElementById('weatherTemp').innerHTML = "--°C";
                document.getElementById('weatherDesc').innerText = "Could not load weather";
            });
    </script>

</body>

</html>