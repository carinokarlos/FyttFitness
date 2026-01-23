<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // ===============================================
        // 1. CHECK MEMBERS
        // ===============================================
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['member_id'];
            $_SESSION['name'] = $user['first_name'];
            $_SESSION['role'] = 'member';
            header("Location: ../dashboard/member.php");
            exit;
        }

        // ===============================================
        // 2. CHECK COACHES (New!)
        // ===============================================
        $stmt = $pdo->prepare("SELECT * FROM coaches WHERE email = ?");
        $stmt->execute([$email]);
        $coach = $stmt->fetch();

        if ($coach && password_verify($password, $coach['password_hash'])) {
            $_SESSION['user_id'] = $coach['coach_id'];
            $_SESSION['name'] = $coach['full_name'];
            $_SESSION['role'] = 'coach';
            header("Location: ../dashboard/coach.php"); // We will create this next
            exit;
        }

        // ===============================================
        // 3. CHECK ADMINS
        // ===============================================
        // Admins can login with Username OR Email
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? OR username = ?"); 
        // Note: Ideally you'd have an email column for admins too, but we used username in setup
        $stmt->execute([$email, $email]); 
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['user_id'] = $admin['admin_id'];
            $_SESSION['name'] = $admin['username'];
            $_SESSION['role'] = 'admin';
            header("Location: ../dashboard/admin.php");
            exit;
        }

        // ===============================================
        // 4. FAILED
        // ===============================================
        echo "<script>alert('Access Denied: Invalid Credentials'); window.location.href='../login.html';</script>";

    } catch (PDOException $e) {
        echo "System Error: " . $e->getMessage();
    }
}
?>