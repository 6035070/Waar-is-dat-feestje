<?php if (isset($_GET["action"]) && $_GET["action"] === "get_events") {

    $year  = intval($_GET["year"]);
    $month = intval($_GET["month"]);

    $sql = "
        SELECT id, title, event_date, start_time, end_time, notes, img 
        FROM events 
        WHERE YEAR(event_date) = ? 
        AND MONTH(event_date) = ?
        ORDER BY start_time ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    header("Content-Type: application/json");
    echo json_encode(["success"=>true, "events"=>$events]);
    exit;
}?>