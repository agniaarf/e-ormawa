<?php
declare(strict_types=1);
if (!isset($page_title)) $page_title = APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/assets/images/orbita-logo.png">
    <meta property="og:image" content="<?= BASE_URL ?>/assets/images/orbita-logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=3">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#244539',
                            light: '#3B5D50',
                            dim: '#aacfbe',
                        },
                        secondary: {
                            DEFAULT: '#3b6756',
                            light: '#4E7A68',
                            container: '#bdedd7',
                        },
                        accent: {
                            DEFAULT: '#D4B483',
                            dark: '#513b15',
                        },
                        surface: {
                            DEFAULT: '#f8f9fa',
                            dim: '#d9dadb',
                            bright: '#f8f9fa',
                            low: '#f3f4f5',
                            high: '#e7e8e9',
                            highest: '#e1e3e4',
                        },
                        'on-surface': '#191c1d',
                        'on-surface-variant': '#414845',
                        outline: '#717974',
                        'outline-variant': '#c1c8c3',
                        error: '#ba1a1a',
                    },
                    fontFamily: {
                        jakarta: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    boxShadow: {
                        'card': '0px 4px 20px rgba(59, 93, 80, 0.05)',
                        'card-hover': '0px 12px 32px rgba(59, 93, 80, 0.12)',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
        .sidebar-active { background-color: #3B5D50; border-left: 4px solid #D4B483; }
        .sidebar-item:hover { background-color: #3B5D50; }
        .input-focus:focus { border-color: #244539; box-shadow: 0 0 0 3px rgba(36, 69, 57, 0.15); outline: none; }
    </style>
</head>
<body class="font-jakarta text-on-surface antialiased">
