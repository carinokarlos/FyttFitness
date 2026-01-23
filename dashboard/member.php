<?php
session_start();
require '../api/db.php'; // Go up one level to find api/db.php

// 1. SECURITY CHECK
// If the user is not logged in, kick them back to login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: ../login.html");
    exit;
}

// 2. GET USER DETAILS
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Default values if data is missing
$name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
$rfid = $user['card_uid'] ? $user['card_uid'] : 'Not Assigned';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Portal | FYTT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-black text-white font-sans min-h-screen bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">

    <nav class="bg-gray-900 border-b border-white/10 p-4 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold italic tracking-wider">FYTT<span class="text-red-600">MEMBER</span></h1>
            <div class="flex items-center gap-4">
                <span class="text-sm hidden md:inline">Welcome, <span class="font-bold text-red-500"><?php echo $name; ?></span></span>
                <a href="../api/logout.php" class="text-xs uppercase font-bold text-gray-400 border border-gray-600 px-4 py-2 rounded hover:bg-white hover:text-black transition">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto p-6 grid md:grid-cols-3 gap-6 mt-6">
        
        <div class="bg-gray-900 rounded-xl p-6 border border-white/10 shadow-lg relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-20 h-20 bg-white/5 rounded-bl-full transition-all group-hover:bg-red-600/20"></div>
            
            <h2 class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-4">My Profile</h2>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gray-700 to-black border border-white/20 flex items-center justify-center text-2xl">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <p class="font-bold text-xl"><?php echo $name; ?></p>
                    <span class="inline-flex items-center gap-1 bg-green-500/10 text-green-500 text-[10px] px-2 py-1 rounded border border-green-500/20 font-bold uppercase">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Active
                    </span>
                </div>
            </div>
            
            <div class="bg-black/50 border border-white/5 p-4 rounded text-center">
                <p class="text-xs text-gray-500 uppercase mb-1">RFID Access Code</p>
                <p class="font-mono text-lg tracking-widest text-red-500"><?php echo $rfid; ?></p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-900 to-black rounded-xl p-6 border border-white/10 relative overflow-hidden shadow-lg flex flex-col justify-between">
            <div>
                <h2 class="text-white/70 text-xs font-bold uppercase tracking-widest mb-1">Class Credits</h2>
                <div class="flex items-end gap-2">
                    <span class="text-6xl font-bold text-white leading-none">0</span>
                    <span class="text-sm text-gray-300 mb-2">/ Unlimited</span>
                </div>
                <p class="text-xs text-red-200 mt-2">Plan: No Active Plan</p>
            </div>
            
            <button class="mt-6 w-full bg-white text-black font-bold uppercase text-xs py-3 rounded hover:bg-gray-200 transition">
                Top Up / Buy Plan
            </button>

            <div class="absolute -right-4 -bottom-4 text-9xl text-white/5 pointer-events-none">
                <i class="fa-solid fa-ticket"></i>
            </div>
        </div>

        <div class="bg-gray-900 rounded-xl p-6 border border-white/10 shadow-lg flex flex-col justify-between">
            <h2 class="text-gray-500 text-xs font-bold uppercase tracking-widest mb-4">Quick Actions</h2>
            
            <div class="grid grid-cols-2 gap-4">
                <a href="../index.html#schedule" class="flex flex-col items-center justify-center bg-black/40 border border-white/5 rounded p-4 hover:border-red-600/50 hover:bg-red-600/10 transition-all group">
                    <i class="fa-solid fa-calendar-days text-2xl mb-2 text-gray-400 group-hover:text-red-500"></i>
                    <span class="text-xs font-bold uppercase text-gray-300">Book Class</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center bg-black/40 border border-white/5 rounded p-4 hover:border-red-600/50 hover:bg-red-600/10 transition-all group">
                    <i class="fa-solid fa-shop text-2xl mb-2 text-gray-400 group-hover:text-red-500"></i>
                    <span class="text-xs font-bold uppercase text-gray-300">Buy Gear</span>
                </a>
            </div>
        </div>

        <div class="md:col-span-3 bg-gray-900 rounded-xl border border-white/10 overflow-hidden shadow-lg mt-4">
            <div class="p-6 border-b border-white/5 flex justify-between items-center">
                <h2 class="text-gray-400 text-xs font-bold uppercase tracking-widest">Upcoming Classes</h2>
                <a href="#" class="text-xs text-red-500 hover:text-white transition">View All</a>
            </div>
            
            <table class="w-full text-left text-sm">
                <thead class="bg-black/20 text-gray-500">
                    <tr>
                        <th class="p-4 pl-6">Class</th>
                        <th class="p-4">Time</th>
                        <th class="p-4">Coach</th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr>
                        <td class="p-6 text-gray-500 text-center italic" colspan="4">
                            No upcoming bookings found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>