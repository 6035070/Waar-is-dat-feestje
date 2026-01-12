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

$event = null;
if (isset($_GET['event']) && is_numeric($_GET['event'])) {
    $eventId = intval($_GET['event']);
    $popularEvents = $conn->query("
        SELECT e.*, COUNT(t.id) AS signup_count
        FROM events e
        LEFT JOIN tickets t ON t.event_id = e.id
        WHERE e.id = $eventId
        GROUP BY e.id
    ");
    
    $event = $popularEvents->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<template id="linkTemplate">
    <a id='downloadHref'></a> <!-- template voor het downloaden van de gegereeerde post.-->
</template>>
    <main>
        <?php include '../assets/header.php';
        if ($event) {
            echo htmlspecialchars($event['name'] ?? 'Event not found');
        }
        ?>

        <form id="eventBeheer" method="post">
            <img src="<?= $event['img'] ?: '../assets/img/poster-placeholder.png' ?>" alt="Event poster">

            <p><?php echo htmlspecialchars($event['title'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($event['event_date'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($event['start_time'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($event['end_time'] ?? ''); ?></p>

            <input type="button" id="GeneratePost" value="Genereer Post">
        </form>
    </main>
</body>

<script type="module">
    import * as htmlToImage from 'https://cdn.jsdelivr.net/npm/html-to-image@1.11.13/+esm';
    //importeer in library om html elementen in een png te veranderen en gelijk te downloaden.

    let generatePost = document.getElementById('GeneratePost');

    generatePost.addEventListener('click', function () { // voeg een onclick event toe om een iamge te maken
        const eventForm = document.getElementById('eventBeheer');

        if (!eventForm) {
            console.error("Target element 'eventBeheer' not found");
            return;
        }

        htmlToImage.toPng(eventForm)
            .then(function (dataUrl) {
                const link = document.getElementById('linkTemplate').content.cloneNode(true).querySelector('a');
                link.download = 'event_post.png';
                link.innerHTML = 'Download gegeneerde post'
                link.href = dataUrl;
                generatePost.parentNode.appendChild(link); // dit zorgt ervoor dat de template a downloaden kan.
                //link.click(); //download de image
            })
            .catch(function (error) {
                let errorMessage = document.createElement('p');
                errorMessage.innerHTML = 'Er is een fout opgetreden bij het genereren van de afbeelding, check de console voor meldingen.';
                errorMessage.style.color = 'red';
                generatePost.parentNode.appendChild(errorMessage);

                console.error('Error generating image:', error);

            });
    });

</script>

</html>