<?php
// Start Session
session_start();
require 'db.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Collect Data
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];

    // 2. Basic Validation
    if ($password !== $confirm) {
        die("<script>alert('Passwords do not match'); window.history.back();</script>");
    }

    // 3. Check if Email Already Exists
    $stmt = $pdo->prepare("SELECT member_id FROM members WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        die("<script>alert('Email is already registered!'); window.history.back();</script>");
    }

    // 4. Hash the Password (SECURITY CRITICAL)
    // Never store plain text passwords. password_hash() creates a secure hash.
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert into Database
    try {
        $sql = "INSERT INTO members (first_name, last_name, email, phone, password_hash, status) 
                VALUES (?, ?, ?, ?, ?, 'Active')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$first_name, $last_name, $email, $phone, $password_hash])) {
            echo "<script>
                    alert('Registration Successful! Welcome to FYTT.');
                    window.location.href = '../login.html';
                  </script>";
        } else {
            echo "<script>alert('Database Error. Please try again.'); window.history.back();</script>";
        }

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>