<?php
require_once "../config/db_connect.php";
session_start();

/*
    Protect page: only logged-in users
*/
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/*
    Get events this user signed up for
*/
$sql = "
    SELECT e.*, t.ticket_code
    FROM tickets t
    JOIN events e ON e.id = t.event_id
    WHERE t.user_id = ?
    ORDER BY e.event_date ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Tickets</title>
<style>
body {
    font-family: Arial, sans-serif;
background: linear-gradient(to bottom, #ffffff, #f1bbffff);    padding: 10px;
}

main {
    display: flex;
    gap: 20px;
}

/* LEFT */
.left-section {
    flex: 2.5;
}

/* RIGHT */
.right-section {
    flex: 1;
}

/* TICKET LIST */
.ticket {
    background:white;
    border-radius:10px;
    padding:16px;
    margin-bottom:14px;
    box-shadow:0 4px 10px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.ticket:hover {
    transform: translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,0.12);
}

.ticket h3 {
    margin:0 0 6px 0;
}

.code {
    font-family: monospace;
    background:#eee;
    padding:6px 10px;
    display:inline-block;
    border-radius:6px;
    margin-top:8px;
}

/* POSTER */
.poster-card {
    position: sticky;
    top: 20px;
    background: #fff;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.poster-card img {
    width: 100%;
    border-radius: 12px;
    object-fit: cover;
    aspect-ratio: 2 / 3;
    margin-bottom: 12px;
}

.poster-card h3 {
    margin: 0 0 6px 0;
}

.poster-card p {
    margin: 4px 0;
    color: #555;
    font-size: 14px;
}
</style>
</head>

<body>

<?php include '../assets/header.php'; ?>

<main>

<section class="left-section"> 

<h1>My Event Tickets</h1>

<?php while ($row = $result->fetch_assoc()): ?>
<div class="ticket"
     data-title="<?= htmlspecialchars($row['title']) ?>"
     data-date="<?= $row['event_date'] ?>"
     data-time="<?= $row['start_time'] ?> - <?= $row['end_time'] ?>"
     data-img="<?= $row['img'] ?: '../assets/img/poster-placeholder.png' ?>"
>
    <h3><?= htmlspecialchars($row['title']) ?></h3>
    <p><strong>Date:</strong> <?= $row['event_date'] ?></p>
    <p><strong>Time:</strong> <?= $row['start_time'] ?> - <?= $row['end_time'] ?></p>
    <div class="code"><?= $row['ticket_code'] ?></div>
</div>
<?php endwhile; ?>

</section>

<section class="right-section"> 

<div class="poster-card">
    <img id="posterImg" src="../assets/img/poster-placeholder.png" alt="Event poster">
    <h3 id="posterTitle">Select a ticket</h3>
    <p id="posterDate"></p>
    <p id="posterTime"></p>
</div>

</section>

</main>

<script>
document.querySelectorAll('.ticket').forEach(ticket => {
    ticket.addEventListener('click', () => {
        document.getElementById('posterTitle').innerText =
            ticket.dataset.title;

        document.getElementById('posterDate').innerText =
            'Date: ' + ticket.dataset.date;

        document.getElementById('posterTime').innerText =
            'Time: ' + ticket.dataset.time;

        document.getElementById('posterImg').src =
            ticket.dataset.img;
    });
});
</script>

</body>
</html>
