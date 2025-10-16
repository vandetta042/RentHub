<?php session_start();
include('../includes/header.php')

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | Affordable Student Housing Transparency System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #f7fafc 0%, #e3e7ed 100%);
            min-height: 100vh;
            color: #263238;
        }

        .landing-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .landing-header {
            margin-top: 60px;
            text-align: center;
        }

        .landing-title {
            font-size: 2.7rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .landing-subtitle {
            font-size: 1.25rem;
            color: #607d8b;
            margin-bottom: 32px;
        }

        .landing-gallery {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto 32px auto;
            text-align: center;
        }

        .gallery-title {
            font-size: 1.25rem;
            color: #45608aff;
            font-weight: 600;
            margin-bottom: 18px;
            letter-spacing: 0.5px;
        }

        .gallery-row {
            display: flex;
            justify-content: center;
            gap: 44px;
            margin-bottom: 38px;
        }

        .gallery-img {
            width: 280px;
            height: 220px;
            object-fit: cover;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.10);
            border: 2px solid #e3e7ed;
            background: #eceff1;
            transition: transform 0.18s, box-shadow 0.18s;
        }

        .gallery-img:hover {
            transform: scale(1.04);
            box-shadow: 0 4px 18px rgba(44, 62, 80, 0.18);
        }

        .landing-actions {
            display: flex;
            gap: 24px;
            margin-bottom: 48px;
            justify-content: center;
        }

        .landing-btn {

            color: #374151;
            border: none;
            border-radius: 8px;
            padding: 16px 38px;
            font-size: 1.18rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(254, 255, 255, 0.08);
            transition: background 0.18s, transform 0.18s;
        }

        .landing-btn:hover {
            transform: translateY(-2px) scale(1.04);
        }

        .landing-footer {
            margin-top: auto;
            text-align: center;
            color: #90a4ae;
            font-size: 1rem;
            padding-bottom: 18px;
        }

        @media (max-width: 900px) {
            .gallery-row {
                flex-direction: column;
                gap: 18px;
            }

            .gallery-img {
                width: 98vw;
                max-width: 340px;
            }

            .landing-actions {
                flex-direction: column;
                gap: 18px;
            }
        }
    </style>
</head>

<body>
    <div class="landing-container">
        <div class="landing-header">
            <div class="landing-title">Find Your Next Cozy Home</div>
            <div class="landing-subtitle">Welcome to the Affordable Student Housing Transparency System.<br>Agents, landlords, and tenants connect here to discover, list, and manage homes with comfort and trust.</div>
            <div class="landing-actions">
                <a href="login.php" class="landing-btn">Login</a>
                <a href="register.php" class="landing-btn">Register</a>
            </div>
        </div>
        <div class="landing-gallery">
            <div class="gallery-title">Featured Homes &amp; Spaces</div>
            <div class="gallery-row">
                <img src="index_images/img1.jpg" alt="House 1" class="gallery-img">
                <img src="index_images/img2.jpg" alt="House 2" class="gallery-img">
                <img src="index_images/img3.jpg" alt="House 3" class="gallery-img">
            </div>
            <div class="gallery-row">
                <img src="index_images/img4.jpg" alt="House 4" class="gallery-img">
                <img src="index_images/img5.jpg" alt="House 5" class="gallery-img">
                <img src="index_images/img6.jpg" alt="House 6" class="gallery-img">
            </div>
        </div>

        <div class="landing-footer">
            &copy; <?php echo date('Y'); ?> Affordable Student Housing Transparency System &mdash; All rights reserved.
        </div>
    </div>
    <?php include('../includes/footer.php') ?>