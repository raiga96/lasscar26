<?php
/**
 * Templat Header Admin Panel - SukanJTS Sarawak
 * Memulakan sesi, menyemak autentikasi, memuatkan CSS Bootstrap, dan menu navbar atas.
 */

// Pastikan sesi aktif dan pengguna disahkan sebelum fail dimuatkan
require_once __DIR__ . '/auth-check.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

// Jana token CSRF untuk kegunaan global dalam borang
$csrf_token = generate_csrf_token();

// Tangkap mesej sesi untuk paparan SweetAlert2 sebelum dinyahset oleh halaman CRUD individu
$swal_success = $_SESSION['success_msg'] ?? '';
$swal_error = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' - Pentadbir LASSCAR 2026' : 'Panel Pentadbir LASSCAR 2026'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Google Fonts Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-blue: #0a2540;
            --navy-light: #1e3a5f;
            --gold: #ffd700;
            --light-bg: #f3f4f6;
            --sidebar-width: 260px;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            overflow-x: hidden;
        }
        /* Layout Grid */
        #wrapper {
            display: flex;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
        }
        #sidebar-wrapper {
            width: var(--sidebar-width);
            background-color: var(--navy-blue);
            color: #ffffff;
            flex-shrink: 0;
            height: 100%;
            overflow-y: auto;
            border-right: 4px solid var(--gold);
            transition: all 0.3s;
        }
        #page-content-wrapper {
            flex-grow: 1;
            height: 100%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        /* Top Navbar */
        .navbar-admin {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 0.75rem 1.5rem;
            flex-shrink: 0;
        }
        .admin-content {
            padding: 1.5rem;
            flex-grow: 1;
        }
        /* Sidebar Links */
        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .sidebar-nav {
            padding: 1rem 0;
            list-style: none;
            margin: 0;
        }
        .sidebar-nav .nav-item {
            margin: 0.25rem 0.75rem;
        }
        .sidebar-nav .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
        }
        .sidebar-nav .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.08);
        }
        .sidebar-nav .nav-link.active {
            color: var(--navy-blue);
            background-color: var(--gold);
            font-weight: 600;
        }
        .sidebar-nav .nav-link.active i {
            color: var(--navy-blue) !important;
        }
        /* Card Designs */
        .card-admin {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            background: #ffffff;
        }
        /* Buttons */
        .btn-navy {
            background-color: var(--navy-blue);
            color: #ffffff;
        }
        .btn-navy:hover {
            background-color: var(--navy-light);
            color: #ffffff;
        }
        .btn-gold {
            background-color: var(--gold);
            color: var(--navy-blue);
            font-weight: 600;
        }
        .btn-gold:hover {
            background-color: #e6c200;
            color: var(--navy-blue);
        }
        /* Badges */
        .badge-live {
            background-color: #ef4444;
            color: #ffffff;
            animation: pulse-red 1.5s infinite;
        }
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body>

<div id="wrapper">
    <!-- Sidebar wrapper akan dimasukkan di fail sidebar.php -->
