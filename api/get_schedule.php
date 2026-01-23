<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // Query to fetch class info + Day Name + Time formatted (e.g., 06:00 PM)
    $sql = "
        SELECT 
            c.title, 
            c.class_type, 
            DATE_FORMAT(c.start_time, '%h:%i %p') as time_slot, 
            DAYNAME(c.start_time) as day_name
        FROM class_schedule c
        ORDER BY TIME(c.start_time) ASC
    ";

    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll();

    // Reformat data for the frontend grid
    // Format: '06:00 PM' => { 'Monday': {title, type}, 'Tuesday': ... }
    $schedule_grid = [];

    foreach ($classes as $class) {
        $time = $class['time_slot'];
        
        // Ensure this time slot exists in our array
        if (!isset($schedule_grid[$time])) {
            $schedule_grid[$time] = [
                'time' => $time,
                'Monday' => null, 'Tuesday' => null, 'Wednesday' => null, 
                'Thursday' => null, 'Friday' => null, 'Saturday' => null, 'Sunday' => null
            ];
        }

        // Place the class in the correct day slot
        $schedule_grid[$time][$class['day_name']] = [
            'title' => $class['title'],
            'type'  => $class['class_type'] // Used for coloring (Boxing vs Muay_Thai)
        ];
    }

    echo json_encode(array_values($schedule_grid));

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>