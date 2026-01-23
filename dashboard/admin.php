<?php
session_start();
require '../api/db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit;
}

// 2. DETERMINE CURRENT VIEW
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// 3. HANDLE ACTIONS (Delete Users)
if (isset($_GET['delete']) && isset($_GET['type']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $type = $_GET['type'];
    
    if ($type === 'member') $sql = "DELETE FROM members WHERE member_id = ?";
    if ($type === 'coach') $sql = "DELETE FROM coaches WHERE coach_id = ?";
    // Note: We generally don't allow deleting admins via UI for safety, but you can add it if needed.
    
    if (isset($sql)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    }
    
    header("Location: admin.php?view=" . $type . "s"); 
    exit;
}

// 4. FETCH DATA
try {
    // Dashboard Data
    if ($view === 'dashboard') {
        $total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
        $active_members = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'Active'")->fetchColumn();
        $logs = $pdo->query("
            SELECT a.tapped_at, a.access_granted, a.denial_reason, m.first_name, m.last_name 
            FROM access_logs a JOIN members m ON a.member_id = m.member_id 
            ORDER BY a.tapped_at DESC LIMIT 5")->fetchAll();
    }

    // User Lists
    if ($view === 'members') $members = $pdo->query("SELECT * FROM members ORDER BY created_at DESC")->fetchAll();
    if ($view === 'coaches') $coaches = $pdo->query("SELECT * FROM coaches")->fetchAll();
    if ($view === 'admins')  $admins  = $pdo->query("SELECT * FROM admins")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center | FYTT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Teko:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: '#E60000' }, fontFamily: { display: ['Teko', 'sans-serif'], sans: ['Inter', 'sans-serif'] } } }
        }
    </script>
</head>
<body class="bg-black text-white font-sans bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">

    <div class="flex h-screen overflow-hidden">
        
        <aside class="w-64 bg-gray-900/90 backdrop-blur-md border-r border-white/10 flex-shrink-0 hidden md:flex flex-col">
            <div class="p-6 border-b border-white/10">
                <h1 class="text-3xl font-display font-bold italic tracking-wider">FYTT<span class="text-brand">ADMIN</span></h1>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest">System Online</p>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-2 text-xs font-bold uppercase tracking-widest text-gray-400">
                <a href="admin.php?view=dashboard" class="flex items-center gap-3 px-4 py-3 rounded transition <?php echo $view == 'dashboard' ? 'bg-brand text-white shadow-lg' : 'hover:bg-white/5 hover:text-white'; ?>">
                    <i class="fa-solid fa-gauge-high text-lg"></i> Dashboard
                </a>
                
                <p class="px-4 pt-4 pb-2 text-[10px] text-gray-600 font-bold">MANAGEMENT</p>
                
                <a href="admin.php?view=members" class="flex items-center gap-3 px-4 py-3 rounded transition <?php echo in_array($view, ['members', 'coaches', 'admins']) ? 'bg-white/10 text-white border-l-2 border-brand' : 'hover:bg-white/5 hover:text-white'; ?>">
                    <i class="fa-solid fa-users text-lg"></i> Users & Staff
                </a>
                
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 hover:text-white rounded transition opacity-50 cursor-not-allowed">
                    <i class="fa-solid fa-calendar-days text-lg"></i> Schedule
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 hover:text-white rounded transition opacity-50 cursor-not-allowed">
                    <i class="fa-solid fa-cash-register text-lg"></i> Financials
                </a>
            </nav>

            <div class="p-4 border-t border-white/10">
                <a href="../api/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-500/10 rounded transition font-bold uppercase text-xs tracking-widest">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-screen overflow-y-auto relative">
            <div class="absolute top-0 left-0 w-full h-96 bg-brand/5 blur-[100px] pointer-events-none"></div>

            <header class="bg-gray-900/50 backdrop-blur-md border-b border-white/10 p-6 flex justify-between items-center sticky top-0 z-20">
                <h2 class="text-2xl font-display font-bold uppercase tracking-wide text-white">
                    <?php 
                        if(in_array($view, ['members', 'coaches', 'admins'])) echo 'User Management';
                        else echo 'Dashboard';
                    ?>
                </h2>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-white">Admin User</p>
                        <p class="text-[10px] text-brand uppercase tracking-widest">Super Admin</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-gray-300">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </header>

            <div class="p-8 relative z-10">

                <?php if ($view === 'dashboard'): ?>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gray-900/80 border border-white/10 p-6 rounded-xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-brand/10 rounded-bl-full"></div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Total Members</p>
                            <h3 class="text-4xl font-display font-bold text-white"><?php echo $total_members; ?></h3>
                        </div>
                        <div class="bg-gray-900/80 border border-white/10 p-6 rounded-xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-green-500/10 rounded-bl-full"></div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Active</p>
                            <h3 class="text-4xl font-display font-bold text-white"><?php echo $active_members; ?></h3>
                        </div>
                        <div class="bg-gray-900/80 border border-white/10 p-6 rounded-xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-500/10 rounded-bl-full"></div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Revenue (Est)</p>
                            <h3 class="text-4xl font-display font-bold text-white">₱124k</h3>
                        </div>
                        <div class="bg-gray-900/80 border border-white/10 p-6 rounded-xl relative overflow-hidden group">
                            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-500/10 rounded-bl-full"></div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Visits Today</p>
                            <h3 class="text-4xl font-display font-bold text-white">42</h3>
                        </div>
                    </div>

                    <div class="grid lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2 bg-gray-900/80 border border-white/10 rounded-xl overflow-hidden">
                            <div class="p-6 border-b border-white/10 flex justify-between items-center">
                                <h3 class="font-bold text-white uppercase tracking-wider text-sm flex items-center gap-2">
                                    <i class="fa-solid fa-satellite-dish text-brand animate-pulse"></i> Live Access Feed
                                </h3>
                            </div>
                            <div class="divide-y divide-white/5">
                                <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                        <div class="p-4 flex items-center justify-between hover:bg-white/5 transition">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white font-bold border border-white/10">
                                                    <?php echo substr($log['first_name'], 0, 1) . substr($log['last_name'], 0, 1); ?>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-sm text-white"><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></p>
                                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider"><?php echo date('h:i A', strtotime($log['tapped_at'])); ?> • Front Door</p>
                                                </div>
                                            </div>
                                            <?php if ($log['access_granted']): ?>
                                                <span class="bg-green-500/10 text-green-500 border border-green-500/20 text-[10px] px-3 py-1 rounded font-bold uppercase tracking-wider">Granted</span>
                                            <?php else: ?>
                                                <span class="bg-brand/10 text-brand border border-brand/20 text-[10px] px-3 py-1 rounded font-bold uppercase tracking-wider">Denied</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-8 text-center text-gray-600 text-sm">No recent check-ins detected.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="bg-gray-900/80 border border-white/10 rounded-xl p-6">
                            <h3 class="font-bold text-white uppercase tracking-wider text-sm mb-4">Traffic (7 Days)</h3>
                            <canvas id="trafficChart" height="200"></canvas>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if (in_array($view, ['members', 'coaches', 'admins'])): ?>
                    <div class="flex items-center justify-between mb-6">
                        
                        <div class="relative group">
                            <label class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-1 block">View Database</label>
                            <select onchange="window.location.href='admin.php?view=' + this.value" 
                                    class="bg-gray-900 border border-white/20 text-white text-sm font-bold uppercase tracking-wider rounded py-3 px-4 pr-8 focus:outline-none focus:border-brand transition cursor-pointer appearance-none min-w-[200px]">
                                <option value="members" <?php if($view == 'members') echo 'selected'; ?>>Members List</option>
                                <option value="coaches" <?php if($view == 'coaches') echo 'selected'; ?>>Coaches Roster</option>
                                <option value="admins" <?php if($view == 'admins') echo 'selected'; ?>>System Admins</option>
                            </select>
                            <div class="absolute top-[28px] right-3 pointer-events-none text-brand">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
                        </div>

                        <button class="bg-white text-black font-bold uppercase text-xs px-6 py-3 rounded hover:bg-gray-200 transition">
                            <i class="fa-solid fa-plus mr-2"></i> Add New <?php echo substr(ucfirst($view), 0, -1); ?>
                        </button>
                    </div>
                <?php endif; ?>


                <?php if ($view === 'members'): ?>
                    <div class="bg-gray-900/80 border border-white/10 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm text-gray-400">
                            <thead class="bg-white/5 text-xs uppercase font-bold text-white">
                                <tr>
                                    <th class="p-6">Member</th>
                                    <th class="p-6">Contact</th>
                                    <th class="p-6">Status</th>
                                    <th class="p-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($members as $m): ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-6">
                                        <p class="font-bold text-white text-base"><?php echo $m['first_name'] . ' ' . $m['last_name']; ?></p>
                                        <p class="text-xs">ID: #<?php echo $m['member_id']; ?></p>
                                    </td>
                                    <td class="p-6">
                                        <p class="text-white"><?php echo $m['email']; ?></p>
                                        <p class="text-xs"><?php echo $m['phone']; ?></p>
                                    </td>
                                    <td class="p-6">
                                        <span class="bg-green-500/10 text-green-500 px-3 py-1 rounded text-xs font-bold uppercase border border-green-500/20">Active</span>
                                    </td>
                                    <td class="p-6 text-right">
                                        <a href="admin.php?delete=true&type=member&id=<?php echo $m['member_id']; ?>" onclick="return confirm('Delete this member?');" class="text-red-500 hover:text-white transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>


                <?php if ($view === 'coaches'): ?>
                    <div class="grid md:grid-cols-3 gap-6">
                        <?php foreach ($coaches as $c): ?>
                        <div class="bg-gray-900/80 border border-white/10 rounded-xl p-6 flex flex-col items-center text-center hover:border-brand/50 transition group relative">
                            <div class="w-20 h-20 bg-gray-800 rounded-full mb-4 flex items-center justify-center text-2xl text-gray-500 border border-white/10 group-hover:border-brand group-hover:text-brand transition">
                                <i class="fa-solid fa-user-ninja"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white"><?php echo $c['full_name']; ?></h3>
                            <p class="text-brand text-xs font-bold uppercase tracking-widest mb-4"><?php echo $c['specialties']; ?></p>
                            <p class="text-gray-400 text-sm mb-6"><?php echo $c['email']; ?></p>
                            <a href="admin.php?delete=true&type=coach&id=<?php echo $c['coach_id']; ?>" onclick="return confirm('Remove coach?');" class="text-xs text-red-500 border border-red-500/30 px-4 py-2 rounded hover:bg-red-500 hover:text-white transition uppercase font-bold">Remove</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


                <?php if ($view === 'admins'): ?>
                    <div class="bg-gray-900/80 border border-white/10 rounded-xl overflow-hidden max-w-4xl mx-auto">
                        <table class="w-full text-left text-sm text-gray-400">
                            <thead class="bg-white/5 text-xs uppercase font-bold text-white">
                                <tr>
                                    <th class="p-6">Username</th>
                                    <th class="p-6">Role</th>
                                    <th class="p-6 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach ($admins as $a): ?>
                                <tr class="hover:bg-white/5 transition">
                                    <td class="p-6 font-bold text-white"><?php echo $a['username']; ?></td>
                                    <td class="p-6 text-brand font-bold uppercase text-xs tracking-wider"><?php echo $a['role']; ?></td>
                                    <td class="p-6 text-right"><span class="text-green-500 text-xs font-bold uppercase"><i class="fa-solid fa-circle text-[8px] mr-1"></i> Online</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <?php if ($view === 'dashboard'): ?>
    <script>
        const ctx = document.getElementById('trafficChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
                datasets: [{
                    label: 'Visits',
                    data: [12, 19, 3, 5, 20, 30, 45],
                    backgroundColor: '#E60000',
                    borderRadius: 2,
                    hoverBackgroundColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false, drawBorder: false }, ticks: { color: '#666' } },
                    y: { beginAtZero: true, grid: { color: '#333', borderDash: [5, 5] }, ticks: { color: '#666' } }
                }
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>