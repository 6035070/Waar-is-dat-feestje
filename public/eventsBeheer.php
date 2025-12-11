<?php
include '../config/db_connect.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ID = $_GET['id'] ?? null;

    $EventName = $_POST['EventName'];
    $EventDesc = $_POST['EventDesc'];
    $EventTijd = $_POST['EventTijd'];
    $EventDate = $_POST['EventDate'];
    $EventLocation = $_POST['EventLocation'];
    $EventActivity = $_POST['EventActivity'];
    $EventStatus = $_POST['EventStatus'];
    $EventNotes = '';
    $EventDeelnemers = '';

    // Check if naam exists
    $check = $conn->prepare("SELECT ID FROM Events WHERE ID = ?");
    $check->bind_param("s", $ID);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // update event als de naam overeen komt
        $stmt = $conn->prepare("UPDATE Events SET Name = ?, Description = ?, time = ?, Date = ?, Location = ?, Activity = ?, Status = ?, Notes = ?, Deelnemers = ? WHERE ID = ?");
        $stmt->bind_param("ssisssssss", $EventName, $EventDesc, $EventTijd, $EventDate, $EventLocation, $EventActivity, $EventStatus, $EventNotes, $EventDeelnemers, $ID);

        if ($stmt->execute()) {
            echo 'Event bijgewerkt!';
            $message = "Event succesvol bijgewerkt";
            $toastClass = "bg-success";
        } else {
            echo " Error: " . $stmt->error;
            $message = "Er ging iets mis";
            $toastClass = "bg-danger";
        }

        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO Events (Name, Description, time, Date, Location, Activity, Status, Notes, Deelnemers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssss", $EventName, $EventDesc, $EventTijd, $EventDate, $EventLocation, $EventActivity, $EventStatus, $EventNotes, $EventDeelnemers);

        if ($stmt->execute()) {
            echo 'Event aangemaakt!';
            $message = "Account succesvol aangemaakt";
            $toastClass = "bg-success";
        } else {
            echo " Error: " . $stmt->error;
            $message = "Er ging iets mis";
            $toastClass = "bg-danger";
        }

        $stmt->close();
    }

    $check->close();
    $conn->close();
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

    <main>
        <?php include '../assets/header.php'; ?>

        <form id="eventBeheer" method="post">
            <h1>Event Beheer</h1>
            <img class="divider" src="../assets/img/snakeSecondary.png">
            <div class="ItemGroup">
                <label for="EventName">Event Naam:</label>
                <input id="EventName" type="text" name="EventName" required>
            </div>

            <div class="ItemGroup">
                <label for="EventDesc">Omschrijving:</label>
                <input id="EventDesc" type="text" name="EventDesc" required>
            </div>

            <div class="ItemGroup">
                <label for="EventTijd">Tijd:</label>
                <input id="EventTijd" type="time" name="EventTijd" required>

                <label for="EventDate">Datum:</label>
                <input id="EventDate" type="date" name="EventDate" required>
            </div>

            <div class="ItemGroup">
                <label for="EventLocation">Locatie:</label>
                <input id="EventLocation" type="text" name="EventLocation" required>
            </div>

            <div class="ItemGroup">
                <label for="EventActivity">Activiteit:</label>
                <input id="EventActivity" type="text" name="EventActivity" required>
            </div>


            <div class="ItemGroup">
                <label for="EventStatus">Status:</label>
                <input id="EventStatus" type="text" name="EventStatus" required>
            </div>

            <input id="SubmitEvent" type="submit" value="Save">
        </form>
    </main>

</body>

</html>