<?php
session_start();
require '../api/db.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header("Location: ../login.html");
    exit;
}

$coach_id = $_SESSION['user_id'];
$coach_name = $_SESSION['name'];

// 2. Get Coach's Schedule (FIXED SQL)
try {
    // We select DAYNAME(start_time) as 'day_of_week' so the rest of the code works
    $sql = "
        SELECT 
            schedule_id, 
            title, 
            class_type, 
            start_time, 
            max_capacity,
            DAYNAME(start_time) as day_of_week 
        FROM class_schedule 
        WHERE coach_id = ? 
        ORDER BY FIELD(DAYNAME(start_time), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), TIME(start_time)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$coach_id]);
    $my_classes = $stmt->fetchAll();
    
    // Count classes
    $class_count = count($my_classes);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Portal | FYTT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: '#E60000' }, fontFamily: { display: ['Teko', 'sans-serif'] } } }
        }
    </script>
</head>
<body class="bg-gray-900 text-white font-sans min-h-screen bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">

    <nav class="bg-black/80 backdrop-blur border-b border-white/10 p-4 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-display font-bold italic tracking-wider">FYTT<span class="text-brand">COACH</span></h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400 hidden sm:inline">Coach <span class="text-white font-bold"><?php echo htmlspecialchars($coach_name); ?></span></span>
                <a href="../api/logout.php" class="text-xs font-bold uppercase text-red-500 border border-red-500/50 px-4 py-2 rounded hover:bg-red-500 hover:text-white transition">Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto p-6 md:p-10">
        
        <div class="flex justify-between items-end mb-8">
            <div>
                <h2 class="text-4xl font-display font-bold text-white mb-1">MY ROSTER</h2>
                <p class="text-gray-400 text-sm">You have <span class="text-brand font-bold"><?php echo $class_count; ?></span> active classes this week.</p>
            </div>
            <button class="bg-white text-black font-bold uppercase text-xs px-5 py-3 rounded hover:bg-gray-200 transition">
                <i class="fa-solid fa-plus mr-1"></i> Request Sub
            </button>
        </div>

        <div class="grid gap-4">
            <?php if ($class_count > 0): ?>
                <?php foreach ($my_classes as $class): ?>
                    
                    <div class="bg-black/60 border border-white/10 rounded-xl p-6 flex flex-col md:flex-row justify-between items-center hover:border-brand/50 transition-colors group">
                        
                        <div class="flex items-center gap-6 w-full md:w-auto">
                            <div class="text-center min-w-[80px]">
                                <span class="block text-brand font-bold uppercase text-sm tracking-widest"><?php echo $class['day_of_week']; ?></span>
                                <span class="block text-2xl font-display font-bold text-white"><?php echo date('g:i A', strtotime($class['start_time'])); ?></span>
                            </div>
                            
                            <div class="h-10 w-[1px] bg-white/10 hidden md:block"></div>
                            
                            <div>
                                <h3 class="text-xl font-bold text-white group-hover:text-brand transition-colors">
                                    <?php echo htmlspecialchars($class['title']); ?>
                                </h3>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wide">
                                    <?php echo $class['class_type']; ?> • Cap: <?php echo $class['max_capacity']; ?>
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 md:mt-0 w-full md:w-auto flex gap-3">
                            <button class="flex-1 md:flex-none border border-white/20 text-gray-300 px-4 py-2 rounded text-xs font-bold uppercase hover:bg-white hover:text-black transition">
                                View Attendees
                            </button>
                            <button class="flex-1 md:flex-none bg-brand text-white px-4 py-2 rounded text-xs font-bold uppercase hover:bg-red-700 transition shadow-lg shadow-red-900/20">
                                Start Class
                            </button>
                        </div>

                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-12 text-center border border-dashed border-white/10 rounded-xl">
                    <p class="text-gray-500">No classes assigned to you yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>