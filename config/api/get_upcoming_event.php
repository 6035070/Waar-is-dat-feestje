<?php
if (isset($_GET["action"]) && $_GET["action"] === "get_upcoming_event") {

    $today = date("Y-m-d");

    $sql = "
        SELECT *
        FROM events
        WHERE event_date >= ?
        ORDER BY event_date ASC, start_time ASC
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();

    $result = $stmt->get_result();
    $event = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "event" => $event
    ]);
    exit;
}
