<?php
require_once "../config/db_connect.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* Generate ticket code */
function generateTicketCode($event_id, $user_id) {
    return 'TICKET-' . strtoupper(substr(md5($event_id . '-' . $user_id), 0, 8));
}

/* Handle signup */
if (isset($_POST['signup_event_id'])) {
    $event_id = (int)$_POST['signup_event_id'];

    // Check if already signed up
    $check = $conn->prepare("
        SELECT id FROM tickets
        WHERE user_id = ? AND event_id = ?
    ");
    $check->bind_param("ii", $user_id, $event_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if (!$exists) {
        $ticketCode = generateTicketCode($event_id, $user_id);

        $insert = $conn->prepare("
            INSERT INTO tickets (user_id, event_id, ticket_code)
            VALUES (?, ?, ?)
        ");
        $insert->bind_param("iis", $user_id, $event_id, $ticketCode);
        $insert->execute();
    }

    exit(); // AJAX signup will not reload page
}

/* If this is an AJAX request for search */
if (isset($_GET['q']) && isset($_GET['ajax'])) {
    $searchQuery = "%{$_GET['q']}%";

    $stmt = $conn->prepare("
        SELECT e.*,
        (SELECT id FROM tickets t
         WHERE t.user_id = ? AND t.event_id = e.id
         LIMIT 1) AS signed_up
        FROM events e
        WHERE e.title LIKE ?
        ORDER BY event_date ASC
    ");
    $stmt->bind_param("is", $user_id, $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($events);
    exit();
}

/* Get all events initially */
$events = $conn->query("
    SELECT e.*,
    (SELECT id FROM tickets t
     WHERE t.user_id = $user_id AND t.event_id = e.id
     LIMIT 1) AS signed_up
    FROM events e
    ORDER BY event_date ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Events</title>
<style>
body { font-family: Arial, sans-serif;   background: linear-gradient(to bottom, #ffffff, #f1bbffff);
     padding: 10px; }

.search-bar { margin-bottom: 20px; }
.search-bar input[type="text"] {
    padding: 10px;
    width: 300px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

.event { background:white; padding:16px; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.08); margin-bottom:16px; display:flex; gap:16px; }
.event img { width:120px; height:160px; object-fit:cover; border-radius:10px; }
.event-content { flex:1; }
button { background:#5a3160; color:white; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; font-weight:600; }
button[disabled] { background:#ccc; cursor:not-allowed; }
</style>
</head>
<body>

<?php include '../assets/header.php'; ?>

<h1>Available Events</h1>

<!-- Search Bar -->
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search events...">
</div>

<div id="eventsContainer">
<?php while ($event = $events->fetch_assoc()): ?>
    <div class="event">
        <img src="<?= $event['img'] ?: '../assets/img/poster-placeholder.png' ?>">
        <div class="event-content">
            <h3><?= htmlspecialchars($event['title']) ?></h3>
            <p><?= $event['event_date'] ?></p>
            <p><?= $event['start_time'] ?> - <?= $event['end_time'] ?></p>
            <form method="POST" class="signupForm">
                <input type="hidden" name="signup_event_id" value="<?= $event['id'] ?>">
                <?php if ($event['signed_up']): ?>
                    <button disabled>Already signed up</button>
                <?php else: ?>
                    <button type="submit">Sign up</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
<?php endwhile; ?>
</div>

<script>
// Live search
const searchInput = document.getElementById('searchInput');
const eventsContainer = document.getElementById('eventsContainer');

searchInput.addEventListener('input', () => {
    const query = searchInput.value.trim();

    fetch(`events.php?ajax=1&q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            eventsContainer.innerHTML = '';
            if (data.length === 0) {
                eventsContainer.innerHTML = '<p>No events found.</p>';
                return;
            }
            data.forEach(event => {
                const div = document.createElement('div');
                div.className = 'event';
                div.innerHTML = `
                    <img src="${event.img || '../assets/img/poster-placeholder.png'}">
                    <div class="event-content">
                        <h3>${event.title}</h3>
                        <p>${event.event_date}</p>
                        <p>${event.start_time} - ${event.end_time}</p>
                        <form method="POST" class="signupForm">
                            <input type="hidden" name="signup_event_id" value="${event.id}">
                            <button ${event.signed_up ? 'disabled' : ''}>${event.signed_up ? 'Already signed up' : 'Sign up'}</button>
                        </form>
                    </div>
                `;
                eventsContainer.appendChild(div);
            });
        });
});

// AJAX sign up
document.addEventListener('click', function(e) {
    if (e.target.tagName === 'BUTTON' && e.target.closest('.signupForm')) {
        e.preventDefault();
        const form = e.target.closest('.signupForm');
        const formData = new FormData(form);

        fetch('events.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            e.target.disabled = true;
            e.target.innerText = 'Already signed up';
        });
    }
});
</script>

</body>
</html>
