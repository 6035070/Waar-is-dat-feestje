<?php
if (isset($_GET["action"]) && $_GET["action"] === "add_event") {
    
    $input = json_decode(file_get_contents("php://input"), true);

    $title  = trim($input["title"] ?? "");
    $date   = trim($input["date"] ?? "");
    $start  = trim($input["start_time"] ?? "");
    $end    = trim($input["end_time"] ?? "");
    $notes  = trim($input["notes"] ?? "");
    $img    = trim($input["img"] ?? "");

    $sql = "
        INSERT INTO events (title, event_date, start_time, end_time, notes, img)
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $title, $date, $start, $end, $notes, $img);

    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}
?>
