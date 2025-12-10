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

        <form id="eventBeheer" action="post">
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