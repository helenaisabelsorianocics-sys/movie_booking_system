<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinema Management Engine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: #f8f9fa;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .content-wrapper {
            display: flex !important;
            flex-direction: row !important;
            width: 100%;
            min-height: 100vh;
            align-items: stretch;
        }
        .sidebar { 
            width: 260px !important; 
            background: #1e293b !important; /* Premium dark slate */
            color: #f8fafc !important; 
            min-height: 100vh !important; 
            flex-shrink: 0 !important; 
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            z-index: 1030;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar .nav-link { 
            color: #cbd5e1 !important; 
            padding: 0.8rem 1.5rem !important; 
            display: flex !important; 
            align-items: center !important; 
            border-radius: 0.375rem !important; 
            margin: 0.2rem 1rem !important; 
            transition: all 0.2s ease-in-out !important; 
            text-decoration: none !important; 
        }

        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active { 
            background: #334155 !important; 
            color: #ffffff !important; 
        }

        .sidebar .nav-link i {
            width: 20px !important;
            text-align: center !important;
        }
        .main-content { 
            flex-grow: 1 !important; 
            padding: 2.5rem !important; 
            width: calc(100% - 260px) !important;
            background-color: #f8f9fa !important;
            min-height: 100vh !important;
        }

        .transition-all {
            transition: all 0.25s ease-in-out;
        }
        .transition-all:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,0.08)!important;
        }
    </style>
</head>
<body>

<div class="content-wrapper">