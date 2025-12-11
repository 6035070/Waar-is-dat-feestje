<?php
include '../config/db_connect.php';

$message = "";
$toastClass = "";
$eventData = null;

// Fetch event data if ID is provided (for editing)
$ID = $_GET['id'] ?? null;
if ($ID) {
    $fetch = $conn->prepare("SELECT Name, Description, time, Date, Location, Activity, Status FROM Events WHERE ID = ?");
    $fetch->bind_param("s", $ID);
    $fetch->execute();
    $eventData = $fetch->get_result()->fetch_assoc();
    $fetch->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $EventName = $_POST['EventName'];
    $EventDesc = $_POST['EventDesc'];
    $EventTijd = $_POST['EventTijd'];
    $EventDate = $_POST['EventDate'];
    $EventLocation = $_POST['EventLocation'];
    $EventActivity = $_POST['EventActivity'];
    $EventStatus = $_POST['EventStatus'];
    $EventNotes = '';
    $EventDeelnemers = '';

    // Check if event exists
    $check = $conn->prepare("SELECT ID FROM Events WHERE ID = ?");
    $check->bind_param("s", $ID);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Update event
        $stmt = $conn->prepare("UPDATE Events SET Name = ?, Description = ?, time = ?, Date = ?, Location = ?, Activity = ?, Status = ?, Notes = ?, Deelnemers = ? WHERE ID = ?");
        $stmt->bind_param("ssisssssss", $EventName, $EventDesc, $EventTijd, $EventDate, $EventLocation, $EventActivity, $EventStatus, $EventNotes, $EventDeelnemers, $ID);

        if ($stmt->execute()) {
            $message = "Event succesvol bijgewerkt";
            $toastClass = "bg-success";
        } else {
            $message = "Er ging iets mis";
            $toastClass = "bg-danger";
        }
        $stmt->close();
    } else {
        // Insert new event
        $stmt = $conn->prepare("INSERT INTO Events (Name, Description, time, Date, Location, Activity, Status, Notes, Deelnemers) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssss", $EventName, $EventDesc, $EventTijd, $EventDate, $EventLocation, $EventActivity, $EventStatus, $EventNotes, $EventDeelnemers);

        if ($stmt->execute()) {
            $message = "Event succesvol aangemaakt";
            $toastClass = "bg-success";
        } else {
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
                <input id="EventName" type="text" name="EventName" value="<?php echo htmlspecialchars($eventData['Name'] ?? ''); ?>" required>
            </div>

            <div class="ItemGroup">
                <label for="EventDesc">Omschrijving:</label>
                <input id="EventDesc" type="text" name="EventDesc" value="<?php echo htmlspecialchars($eventData['Description'] ?? ''); ?>" required>
            </div>

            <div class="ItemGroup">
                <label for="EventTijd">Tijd:</label>
                <input id="EventTijd" type="time" name="EventTijd" value="<?php echo htmlspecialchars($eventData['time'] ?? ''); ?>" required>

                <label for="EventDate">Datum:</label>
                <input id="EventDate" type="date" name="EventDate" value="<?php echo htmlspecialchars($eventData['Date'] ?? ''); ?>" required>
            </div>

            <div class="ItemGroup">
                <label for="EventLocation">Locatie:</label>
                <input id="EventLocation" type="text" name="EventLocation" value="<?php echo htmlspecialchars($eventData['Location'] ?? ''); ?>" required>
            </div>

            <div class="ItemGroup">
                <label for="EventActivity">Activiteit:</label>
                <input id="EventActivity" type="text" name="EventActivity" value="<?php echo htmlspecialchars($eventData['Activity'] ?? ''); ?>" required>
            </div>

            <div class="ItemGroup">
                <label for="EventStatus">Status:</label>
                <input id="EventStatus" type="text" name="EventStatus" value="<?php echo htmlspecialchars($eventData['Status'] ?? ''); ?>" required>
            </div>

            <input id="SubmitEvent" type="submit" value="<?php echo $ID ? 'Update' : 'Create'; ?>">
        </form>
    </main>
</body>
</html>
