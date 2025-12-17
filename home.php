<?php
require_once __DIR__ . '/config.php';

// Fetch rooms from database with availability
$rooms_query = $conn->query("SELECT id, name, category, price, description, image_url, features, total_rooms, available_rooms FROM rooms ORDER BY category, price");
$rooms = [];
if ($rooms_query) {
    while ($room = $rooms_query->fetch_assoc()) {
        $room['features'] = json_decode($room['features'], true);
        $rooms[] = $room;
    }
}

// Fetch menu items
$menu_query = $conn->query("SELECT id, name, description, price, category FROM menu WHERE available = TRUE ORDER BY category, name");
$menu_items = [];
if ($menu_query) {
    while ($item = $menu_query->fetch_assoc()) {
        $menu_items[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMUNRA | Luxury Egyptian Resort</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables */
        :root {
            --primary-color: #c19a53;
            /* Gold */
            --secondary-color: #f5f5dc;
            /* Beige */
            --accent-color: #8b7355;
            /* Darker gold/brown */
            --text-color: #333;
            --light-text: #fff;
            --dark-bg: #1a1a1a;
            --section-padding: 80px 0;
            --transition: all 0.3s ease;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #fff;
            overflow-x: hidden;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1,
        h2,
        h3,
        h4 {
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        p {
            margin-bottom: 15px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
            text-align: center;
        }

        .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: var(--box-shadow);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--light-text);
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--primary-color);
        }

        .section-title p {
            max-width: 600px;
            margin: 0 auto;
            color: #666;
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: rgba(26, 26, 26, 0.9);
            transition: var(--transition);
        }

        header.scrolled {
            background-color: var(--dark-bg);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--light-text);
        }

        .logo span {
            color: var(--primary-color);
        }

        .nav-links {
            display: flex;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            color: var(--light-text);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .hamburger {
            display: none;
            color: var(--light-text);
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Hero Section */
        #hero {
            height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            color: var(--light-text);
        }

        .hero-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.3));
            z-index: -1;
        }

        .hero-content {
            max-width: 600px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
        }

        .hero-content h1 span {
            color: var(--primary-color);
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        /* About Section */
        #about {
            padding: var(--section-padding);
            background-color: var(--secondary-color);
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-text h3 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .about-image {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }

        .about-image::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid var(--primary-color);
            border-radius: var(--border-radius);
            z-index: -1;
        }

        /* Rooms Section */
        #rooms {
            padding: var(--section-padding);
        }

        .rooms-filter {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .filter-btn {
            padding: 10px 20px;
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            font-weight: 500;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background-color: var(--primary-color);
            color: var(--light-text);
        }

        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .room-card {
            background-color: #fff;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .room-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .room-image {
            height: 250px;
            overflow: hidden;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .room-card:hover .room-image img {
            transform: scale(1.1);
        }

        .room-info {
            padding: 25px;
        }

        .room-info h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .room-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent-color);
            margin-bottom: 15px;
        }

        .room-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .room-feature {
            background-color: var(--secondary-color);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .room-actions {
            display: flex;
            gap: 10px;
        }

        .room-actions .btn {
            flex: 1;
            padding: 10px 15px;
            font-size: 0.9rem;
        }

        .room-availability {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 12px;
        }

        .availability-available {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .availability-unavailable {
            background-color: #ffebee;
            color: #c62828;
        }

        .room-card.unavailable {
            opacity: 0.6;
            pointer-events: none;
        }

        .room-card.unavailable .room-actions .btn {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Amenities Section */
        #amenities {
            padding: var(--section-padding);
            background-color: var(--secondary-color);
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }

        .amenity-card {
            text-align: center;
            padding: 30px 20px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .amenity-card:hover {
            transform: translateY(-5px);
        }

        .amenity-icon {
            width: 70px;
            height: 70px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
        }

        .amenity-card h3 {
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        /* Gallery Section */
        #gallery {
            padding: var(--section-padding);
        }

        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .gallery-item {
            position: relative;
            border-radius: var(--border-radius);
            overflow: hidden;
            height: 250px;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(193, 154, 83, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }

        .gallery-overlay i {
            color: var(--light-text);
            font-size: 2rem;
        }

        /* Testimonials Section */
        #testimonials {
            padding: var(--section-padding);
            background-color: var(--secondary-color);
        }

        .testimonials-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .testimonial-slider {
            overflow: hidden;
        }

        .testimonial-track {
            display: flex;
            transition: transform 0.5s ease;
        }

        .testimonial-card {
            min-width: 100%;
            padding: 30px;
            background-color: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 20px;
            position: relative;
        }

        .testimonial-text::before,
        .testimonial-text::after {
            content: '"';
            font-size: 3rem;
            color: var(--primary-color);
            position: absolute;
            opacity: 0.3;
        }

        .testimonial-text::before {
            top: -20px;
            left: -10px;
        }

        .testimonial-text::after {
            bottom: -40px;
            right: -10px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .author-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 15px;
        }

        .author-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .testimonial-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .testimonial-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ddd;
            cursor: pointer;
            transition: var(--transition);
        }

        .testimonial-dot.active {
            background-color: var(--primary-color);
        }

        /* Contact Section */
        #contact {
            padding: var(--section-padding);
        }

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
        }

        .contact-info h3 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .contact-details {
            margin: 30px 0;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.2rem;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .social-links a:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
        }

        .contact-form .form-group {
            margin-bottom: 20px;
        }

        .contact-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 1rem;
        }

        .form-message {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: var(--border-radius);
        }

        /* Footer */
        footer {
            background-color: var(--dark-bg);
            color: var(--light-text);
            padding: 60px 0 30px;
        }

        .footer-content {
            text-align: center;
        }

        .footer-content .logo {
            margin-bottom: 20px;
        }

        .footer-content p {
            max-width: 600px;
            margin: 0 auto 30px;
            color: #aaa;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-links a {
            color: #aaa;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .copyright {
            color: #777;
            font-size: 0.9rem;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--primary-color);
            color: var(--light-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            z-index: 999;
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background-color: var(--accent-color);
        }

        #coming-soon {
            padding: 80px 0;
            background: #f8f4ef;
        }

        #coming-soon .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .coming-soon-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .coming-soon-text {
            flex: 1;
            min-width: 300px;
            padding: 20px;
        }

        .coming-soon-text h3 {
            font-size: 28px;
            color: #a88b56;
            margin-bottom: 15px;
        }

        .coming-soon-text p {
            color: #444;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .coming-soon-text ul {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
        }

        .coming-soon-text ul li {
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }

        .coming-soon-text ul li i {
            color: #d4af37;
            margin-right: 8px;
        }

        .coming-soon-image {
            flex: 1;
            min-width: 300px;
            text-align: center;
        }

        .coming-soon-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-learn-more {
            display: inline-block;
            padding: 12px 28px;
            background: #a88b56;
            color: #fff;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-learn-more:hover {
            background: #d4af37;
        }

        /* Responsive Design */
        @media (max-width: 992px) {

            .about-content,
            .contact-container {
                grid-template-columns: 1fr;
            }

            .about-image {
                order: -1;
            }

            .hero-content h1 {
                font-size: 2.8rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                position: fixed;
                top: 80px;
                left: 0;
                width: 100%;
                background-color: var(--dark-bg);
                flex-direction: column;
                align-items: center;
                padding: 20px 0;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: var(--transition);
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-links.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }

            .nav-links li {
                margin: 15px 0;
            }

            .hamburger {
                display: block;
            }

            .hero-content h1 {
                font-size: 2.2rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .rooms-filter {
                flex-wrap: wrap;
            }

            .about-features {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
            margin-left: auto;
            /* Pushes it to the right */
        }

        .user-name {
            color: #c19a53;
            font-weight: 600;
            text-decoration: none;

            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        .user-name:hover {
            background: rgba(193, 154, 83, 0.15);
            color: #fff;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 200px;
            background: #111;
            border: 1px solid #333;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 999;
            list-style: none;
            padding: 0.75rem 0;
            margin: 0;
        }

        .user-dropdown:hover .dropdown-menu,
        .user-dropdown:focus-within .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu li {
            margin: 0;
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.2rem;
            color: #eee;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .dropdown-menu a:hover {
            background: #c19a53;
            color: #000;
        }

        .dropdown-menu a i {
            margin-right: 10px;
            width: 16px;
        }

        .dropdown-divider {
            height: 1px;
            background: #444;
            margin: 0.5rem 0;
            border: none;
        }

        .logout-link {
            color: #ff6b6b !important;
        }

        .logout-link:hover {
            background: #ff6b6b !important;
            color: #fff !important;
        }

        /* Mobile fallback - dropdown becomes clickable */
        @media (max-width: 992px) {
            .user-name[aria-expanded="true"]+.dropdown-menu {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
                position: static;
                box-shadow: none;
                border: none;
                background: rgba(255, 255, 255, 0.05);
            }

            .user-name::after {
                content: " ▼";
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .section-title h2 {
                font-size: 2rem;
            }

            .rooms-grid {
                grid-template-columns: 1fr;
            }

            .gallery-container {
                grid-template-columns: 1fr;
            }
        }

        .logo-text {
            color: var(--primary-color);
            font-size: 1.2rem !important;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <header id="header" class="site-header" aria-label="Main navigation">
        <div class="container">
            <nav class="main-navigation" aria-label="Primary">
                <!-- Logo -->
                <a href="home.php" class="site-logo" aria-label="Amunra Hotel - Home">
                    <span class="logo-text">AMUNRA</span>
                </a>

                <!-- Desktop Navigation -->
                <ul class="nav-links" id="primary-menu">
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#rooms">Rooms</a></li>
                    <li><a href="#amenities">Amenities</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <ul>
                    <!-- User Menu (Logged In) -->
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="user-dropdown">
                            <a href="javascript:void(0)" class="user-name" aria-haspopup="true" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest', ENT_QUOTES, 'UTF-8'); ?>
                                <i class="fas fa-chevron-down" style="font-size: 0.8rem; margin-left: 6px;"></i>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li><a href="profile.php" role="menuitem">My Profile</a></li>
                                <li><a href="bookings.php" role="menuitem">My Bookings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a href="logout.php" role="menuitem" class="logout-link">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Guest Links -->
                        <li class="auth-links">
                            <a href="login.php" class="btn-login">Login</a>
                        </li>
                        <li>
                            <a href="register.php" class="btn-register">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Mobile Menu Toggle -->
                <button class="hamburger" id="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false"
                    aria-controls="primary-menu">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                    <span class="sr-only">Menu</span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero">
        <div class="hero-slider">
            <div class="slide active"
                style="background-image: url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
            </div>
            <div class="slide"
                style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
            </div>
            <div class="slide"
                style="background-image: url('https://images.unsplash.com/photo-1611892440504-42a792e24d32?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
            </div>
            <div class="slide"
                style="background-image: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
            </div>
        </div>
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1>Experience <span>Egyptian</span> Luxury</h1>
                <p>where Egyptian elegance meets Greek divine grandeur. Experience the mystique of the Nile, the glory
                    of Olympus, and the finest in modern luxury.</p>
                <div class="btn-group">
                    <a href="#rooms" class="btn">View Rooms</a>

                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <div class="container">
            <div class="section-title">
                <h2>About AMUNRA</h2>
                <p>Discover the story behind our luxurious Egyptian-themed resort</p>
            </div>
            <div class="about-content">
                <div class="about-text">
                    <h3>A Sanctuary of Luxury</h3>
                    <p>Hotel Amunra is more than a luxury escape—it is the awakening of a legendary world where Greek
                        grandeur and Egyptian mystique converge in perfect harmony. Rising with timeless elegance,
                        Amunra stands as a living tribute to gods, pharaohs, and ancient artistry reimagined for the
                        modern traveller.

                        Within its walls, every corridor tells a story and every experience is crafted to enchant. From
                        divine dining rituals and immersive cultural journeys to indulgent suites inspired by myth and
                        royalty, Amunra invites you to step into a universe where history becomes immersive luxury.

                        Welcome to Amunra—where myth becomes reality, and every moment feels eternal.
                    </p>
                    
                    <div class="about-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Royal Treatment</h4>
                                <p>Experience service fit for pharaohs</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Prime Location</h4>
                                <p>Steps away from historic landmarks</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-utensils"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Gourmet Dining</h4>
                                <p>Authentic Egyptian & international cuisine</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-spa"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Luxury Spa</h4>
                                <p>Ancient wellness treatments</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Nile Palace Lobby">
                </div>
            </div>
        </div>
    </section>
    <!-- ======= Coming Soon Section ======= -->
    <section id="coming-soon">
        <div style="border: 2px solid #000000ff; padding: 20px; border-radius: 5px;" class="container">
            <div class="section-title">
                <h2>Coming Soon</h2>
                <p>We're expanding the legacy of luxury to a new destination</p>
            </div>

            <div class="coming-soon-content">
                <div class="coming-soon-text">
                    <h3>New Branch Opening – AMUNRA Golden Shore</h3>
                    <p>Get ready to experience the magic of Egypt in a whole new light.
                        Our brand-new **AMUNRA Desert Oasis** is opening soon, offering
                        guests an unforgettable blend of serenity, culture, and elegance amidst
                        the golden sands.</p>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Location: Galle, Sri Lanka</li>
                        <li><i class="fas fa-calendar-alt"></i> Opening: Summer 2026</li>
                        <li><i class="fas fa-gift"></i> Exclusive offers for early bookings!</li>
                    </ul>
                    <a href="#contact" class="btn-learn-more">Get Notified</a>
                </div>

                <div class="coming-soon-image">
                    <img src="https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="AMUNRA Desert Oasis Opening Soon">
                </div>
            </div>
        </div>
    </section>


    <!-- Rooms Section -->
    <section id="rooms">
        <div class="container">
            <div class="section-title">
                <h2>Luxury Accommodations</h2>
                <p>Choose from our exquisite selection of Egyptian-themed rooms and suites</p>
            </div>
            <div class="rooms-filter">
                <button class="filter-btn active" data-filter="all">All Rooms</button>
                <button class="filter-btn" data-filter="standard">Standard</button>
                <button class="filter-btn" data-filter="deluxe">Deluxe</button>
                <button class="filter-btn" data-filter="suite">Presidential Suites</button>
            </div>
            <div class="rooms-grid">
                <?php foreach ($rooms as $room): ?>
                    <div class="room-card <?php echo $room['available_rooms'] === 0 ? 'unavailable' : ''; ?>"
                        data-category="<?php echo htmlspecialchars($room['category']); ?>">
                        <div class="room-image">
                            <img src="<?php echo htmlspecialchars($room['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($room['name']); ?>">
                            <?php if ($room['available_rooms'] === 0): ?>
                                <div
                                    style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 10;">
                                    <span
                                        style="background: #c62828; color: #fff; padding: 12px 20px; border-radius: 8px; font-weight: 600; font-size: 1rem;">SOLD
                                        OUT</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="room-info">
                            <h3><?php echo htmlspecialchars($room['name']); ?></h3>
                            <div
                                class="room-availability <?php echo $room['available_rooms'] > 0 ? 'availability-available' : 'availability-unavailable'; ?>">
                                <?php if ($room['available_rooms'] > 0): ?>
                                    <i class="fas fa-check-circle"></i> <?php echo $room['available_rooms']; ?> of
                                    <?php echo $room['total_rooms']; ?> Available
                                <?php else: ?>
                                    <i class="fas fa-times-circle"></i> No Rooms Available
                                <?php endif; ?>
                            </div>
                            <div class="room-price">LKR <?php echo number_format($room['price'], 2); ?> <span>/ night</span>
                            </div>
                            <p><?php echo htmlspecialchars($room['description']); ?></p>
                            <div class="room-features">
                                <?php foreach ($room['features'] as $feature): ?>
                                    <span class="room-feature"><?php echo htmlspecialchars($feature); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="room-actions">
                                <a href="#" class="btn">View Details</a>
                                <?php if ($room['available_rooms'] > 0): ?>
                                    <a href="#" class="btn btn-outline open-book"
                                        data-room="<?php echo htmlspecialchars($room['name']); ?>"
                                        data-room-id="<?php echo $room['id']; ?>"
                                        data-price="<?php echo $room['price']; ?>">Book Now</a>
                                <?php else: ?>
                                    <button class="btn" disabled style="opacity: 0.5; cursor: not-allowed;">Unavailable</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Restaurant Section -->
    <section id="restaurants">
        <div class="container">
            <div class="section-title">
                <h2>Signature Dining Experiences</h2>
                <p>Savor world-class cuisine inspired by Egyptian heritage and global flavors</p>
            </div>

            <div class="rooms-grid">

                <!-- Restaurant 1 -->
                <div class="room-card">
                    <div class="room-image">
                        <img src="images/img1.jpg" alt="Pharaoh’s Feast">
                    </div>

                    <div class="room-info">
                        <h3>Pharaoh’s Feast</h3>

                        <div class="room-availability availability-available">
                            <i class="fas fa-check-circle"></i> Open Now
                        </div>

                        <div class="room-price">
                            $$$ <span> · Fine Dining</span>
                        </div>

                        <p>
                            An opulent fine-dining restaurant serving authentic cuisine with a modern twist.
                        </p>

                        <div class="room-features">
                            <span class="room-feature">Egyptian Cuisine</span>
                            <span class="room-feature">Live Music</span>

                        </div>

                        <div class="room-actions">
                            <a href="#" class="btn">View Menu</a>
                            <a href="#" class="btn btn-outline">Reserve Table</a>
                        </div>
                    </div>
                </div>

                <!-- Restaurant 2 -->
                <div class="room-card">
                    <div class="room-image">
                        <img src="images/img2.jpg" alt="Nile Breeze Café">
                    </div>

                    <div class="room-info">
                        <h3>Nile Breeze Café</h3>

                        <div class="room-availability availability-available">
                            <i class="fas fa-check-circle"></i> Open Now
                        </div>

                        <div class="room-price">
                            $$ <span> · Café & Lounge</span>
                        </div>

                        <p>
                            A relaxed café overlooking the gardens, perfect for fresh coffee, juices, and light meals.
                        </p>

                        <div class="room-features">
                            <span class="room-feature">Fresh Coffee</span>
                            <span class="room-feature">Outdoor Seating</span>
                            <span class="room-feature">Free Wi-Fi</span>
                        </div>

                        <div class="room-actions">
                            <a href="#" class="btn">View Menu</a>
                            <a href="#" class="btn btn-outline">Reserve Table</a>
                        </div>
                    </div>
                </div>

                <!-- Restaurant 3 (Closed) -->
                <div class="room-card">
                    <div class="room-image">
                        <img src="images/img3.jpg" alt="Golden Pyramid Grill">


                    </div>

                    <div class="room-info">
                        <h3>Golden Pyramid Grill</h3>

                        <div class="room-availability availability-unavailable">
                            <i class="fas fa-times-circle"></i> Currently Closed
                        </div>

                        <div class="room-price">
                            $$$ <span> · International Buffet</span>
                        </div>

                        <p>
                            A lavish buffet offering international flavors, live cooking stations, and family dining.
                        </p>

                        <div class="room-features">
                            <span class="room-feature">Buffet</span>
                            <span class="room-feature">Live Cooking</span>
                            <span class="room-feature">Family Friendly</span>
                        </div>

                        <div class="room-actions">
                            <a href="#" class="btn">View Menu</a>
                            <button class="btn">
                                Not Available
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Spa Section -->
    <section id="spa" style="padding: 80px 0; background: #f8f4ef;">
        <div style="border: 2px solid #c19a53; padding: 20px; border-radius: 5px;" class="container">
            <div class="section-title">
                <h2>Luxury Spa & Wellness</h2>
                <p>Rejuvenate your body and soul with ancient Egyptian treatments</p>
            </div>

            <div class="coming-soon-content">
                <div class="coming-soon-text">
                    <h3>AMUNRA Wellness Sanctuary</h3>
                    <p>Escape to our world-class spa, where ancient Egyptian wellness practices meet modern luxury.
                        Our expert therapists blend traditional techniques with contemporary treatments to create an
                        unforgettable journey of rejuvenation and relaxation amidst serene surroundings inspired by
                        the mystique of the Nile.</p>
                    <ul>
                        <li><i class="fas fa-spa"></i> Traditional Egyptian Therapies</li>
                        <li><i class="fas fa-leaf"></i> Organic & Natural Products</li>
                        <li><i class="fas fa-gem"></i> Signature Treatments</li>
                        <li><i class="fas fa-hot-tub"></i> Steam & Sauna Facilities</li>
                        <li><i class="fas fa-massage"></i> Professional Massage Therapists</li>
                    </ul>
                    <a href="#contact" class="btn-learn-more">Book a Treatment</a>
                </div>

                <div class="coming-soon-image">
                    <img src="images/img4.jpg" alt="AMUNRA Wellness Sanctuary">
                </div>
            </div>
        </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities">
        <div class="container">
            <div class="section-title">
                <h2>Hotel Amenities</h2>
                <p>Discover the world-class facilities that make AMUNRA an unforgettable experience</p>
            </div>
            <div class="amenities-grid">
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <h3>Infinity Pool</h3>
                    <p>Relax in our stunning infinity pool with breathtaking views of the Nile River.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Luxury Spa</h3>
                    <p>Rejuvenate with ancient Egyptian wellness treatments and modern therapies.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Fine Dining</h3>
                    <p>Savor authentic Egyptian cuisine and international dishes at our restaurants.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Fitness Center</h3>
                    <p>Stay active in our state-of-the-art gym with personal training available.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <h3>24/7 Concierge</h3>
                    <p>Our dedicated staff is available around the clock to assist with your needs.</p>
                </div>
                <div class="amenity-card">
                    <div class="amenity-icon">
                        <i class="fas fa-shuttle-van"></i>
                    </div>
                    <h3>Tour Services</h3>
                    <p>Explore Egypt's wonders with our curated tours and transportation services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="gallery">
        <div class="container">
            <div class="section-title">
                <h2>Photo Gallery</h2>
                <p>Take a visual journey through our luxurious Egyptian-themed resort</p>
            </div>
            <div class="gallery-container">
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Hotel Lobby">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Pool Area">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Restaurant">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1584132967334-10e028bd69f7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Spa">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Bedroom">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1549451371-64aa98a6f660?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80"
                        alt="Bathroom">
                    <div class="gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Guest Reviews</h2>
                <p>See what our guests have to say about their experience at AMUNRA</p>
            </div>
            <div class="testimonials-container">
                <div class="testimonial-slider">
                    <div class="testimonial-track">
                        <div class="testimonial-card">
                            <p class="testimonial-text">"Our stay at AMUNRA was absolutely magical. The attention
                                to detail in the Egyptian-themed decor was incredible, and the service was beyond
                                compare. We felt like royalty from the moment we arrived."</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sarah Johnson">
                                </div>
                                <div class="author-info">
                                    <h4>Sarah Johnson</h4>
                                    <p>New York, USA</p>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <p class="testimonial-text">"The infinity pool with views of the Nile was worth the trip
                                alone. Combined with the exquisite dining and luxurious spa treatments, this was the
                                most relaxing vacation we've ever had."</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Michael Brown">
                                </div>
                                <div class="author-info">
                                    <h4>Michael Brown</h4>
                                    <p>London, UK</p>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <p class="testimonial-text">"As a history enthusiast, I was blown away by how authentically
                                the hotel captured the essence of ancient Egypt while providing all modern comforts. The
                                guided tours arranged by the hotel were exceptional."</p>
                            <div class="testimonial-author">
                                <div class="author-image">
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Emma Wilson">
                                </div>
                                <div class="author-info">
                                    <h4>Emma Wilson</h4>
                                    <p>Toronto, Canada</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="testimonial-nav">
                    <div class="testimonial-dot active" data-slide="0"></div>
                    <div class="testimonial-dot" data-slide="1"></div>
                    <div class="testimonial-dot" data-slide="2"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Contact Us</h2>
                <p>Get in touch to book your stay or inquire about our services</p>
            </div>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Plan Your Egyptian Getaway</h3>
                    <p>Our dedicated team is ready to help you plan the perfect stay at AMUNRA. Whether you're
                        looking for a romantic getaway, family vacation, or business trip, we'll ensure your experience
                        is unforgettable.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Location</h4>
                                <p>At Port City - Colombo</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Email</h4>
                                <a href="mailto:reservations@nilepalace.com">amunracmb@gmail.com</a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Phone</h4>
                                <a href="tel:+201234567890">+94 771232323</a>
                            </div>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="TripAdvisor"><i class="fab fa-tripadvisor"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="form" action="#" method="POST">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                    <div id="form-message" class="form-message"
                        style="display: none; margin-top: 1rem; padding: 1rem; border-radius: 8px;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <a href="#" class="logo"><span>AMUNRA</span></a>
                <p>Experience the luxury and mystique of ancient Egypt at our premier resort on the banks of the
                    legendary Nile River.</p>
                <ul class="footer-links">
                    <li><a href="#hero">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#rooms">Rooms</a></li>
                    <li><a href="#amenities">Amenities</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <p class="copyright">© <span id="year"></span> AMUNRA. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Booking Modal -->
    <div id="booking-modal"
        style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
        <div
            style="background: #fff; width: 100%; max-width: 600px; border-radius: 12px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">

            <button id="close-modal"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                <i class="fas fa-times"></i>
            </button>

            <h2 style="color: #c19a53; margin-bottom: 10px; font-size: 1.8rem;">Book Your Room</h2>
            <p style="color: #666; margin-bottom: 25px;">Reserve your luxurious stay at AMUNRA</p>

            <form id="booking-form">
                <!-- Room Name (Hidden) -->
                <input type="hidden" name="room_name" id="form-room-name">
                <input type="hidden" name="room_id" id="form-room-id">
                <input type="hidden" name="price" id="form-price">

                <div
                    style="background: #f5f5dc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c19a53;">
                    <p style="color: #666; font-size: 0.9rem; margin: 0;">Selected Room</p>
                    <h3 id="modal-room-name" style="color: #c19a53; margin: 5px 0; font-size: 1.3rem;">Room Name</h3>
                    <p id="modal-room-price" style="color: #8b7355; font-weight: 600; margin: 5px 0;">LKR 0 / night</p>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Check-in
                        Date</label>
                    <input type="date" name="checkin" id="checkin" required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Check-in
                        Time</label>
                    <select name="checkin_time" id="checkin_time" required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
                        <option value="">Select time</option>
                        <option value="14:00">2:00 PM</option>
                        <option value="15:00">3:00 PM</option>
                        <option value="16:00">4:00 PM</option>
                        <option value="17:00">5:00 PM</option>
                        <option value="18:00">6:00 PM</option>
                        <option value="19:00">7:00 PM</option>
                    </select>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Check-out
                        Date</label>
                    <input type="date" name="checkout" id="checkout" required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Check-out
                        Time</label>
                    <select name="checkout_time" id="checkout_time" required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
                        <option value="">Select time</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="12:00">12:00 PM (Noon)</option>
                        <option value="13:00">1:00 PM</option>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Number of
                        Guests</label>
                    <input type="number" name="guests" id="guests" value="2" min="1" max="4" required
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; box-sizing: border-box;">
                </div>

                <div id="booking-message"
                    style="margin-bottom: 20px; padding: 12px; border-radius: 6px; display: none; font-weight: 500;">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button type="submit"
                        style="flex: 1; padding: 14px; background: #c19a53; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                        Confirm Reservation
                    </button>
                    <button type="button" id="cancel-modal"
                        style="flex: 1; padding: 14px; background: #f5f5dc; color: #c19a53; border: 2px solid #c19a53; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Food Menu Modal -->
    <div id="food-modal"
        style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2001; align-items: center; justify-content: center; padding: 20px;">
        <div
            style="background: #fff; width: 100%; max-width: 700px; border-radius: 12px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">

            <button id="close-food-modal"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                <i class="fas fa-times"></i>
            </button>

            <h2 style="color: #c19a53; margin-bottom: 10px; font-size: 1.8rem;">Order Food & Beverages</h2>
            <p style="color: #666; margin-bottom: 25px;">Add items to your reservation</p>

            <div id="menu-container"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <?php foreach ($menu_items as $item): ?>
                    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; text-align: center; cursor: pointer; transition: all 0.3s;"
                        class="menu-item" data-id="<?php echo $item['id']; ?>"
                        data-name="<?php echo htmlspecialchars($item['name']); ?>"
                        data-price="<?php echo $item['price']; ?>">
                        <h4 style="color: #c19a53; margin-bottom: 8px; font-size: 0.95rem;">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </h4>
                        <p style="color: #666; font-size: 0.85rem; margin-bottom: 10px;">
                            <?php echo htmlspecialchars($item['description'] ?? ''); ?>
                        </p>
                        <p style="color: #8b7355; font-weight: 600; font-size: 1.1rem;">
                            LKR <?php echo number_format($item['price'], 2); ?></p>
                        <button type="button" class="add-to-order"
                            style="width: 100%; padding: 8px; background: #c19a53; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; margin-top: 10px;">
                            + Add
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="background: #f5f5dc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: #c19a53; margin-bottom: 15px;">Your Order</h3>
                <div id="order-items-list" style="margin-bottom: 15px;"></div>
                <div
                    style="border-top: 2px solid #ddd; padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #333; margin: 0;">Order Total:</h3>
                    <p style="color: #8b7355; font-weight: 600; font-size: 1.3rem; margin: 0;">LKR <span
                            id="order-total">0.00</span></p>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" id="confirm-food-order"
                    style="flex: 1; padding: 14px; background: #c19a53; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                    Add to Reservation
                </button>
                <button type="button" id="close-food-btn"
                    style="flex: 1; padding: 14px; background: #f5f5dc; color: #c19a53; border: 2px solid #c19a53; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 1rem; text-transform: uppercase;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <div class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Mobile Navigation
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.innerHTML = navLinks.classList.contains('active') ?
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });

        // Close mobile menu when clicking a nav link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                hamburger.innerHTML = '<i class="fas fa-bars"></i>';
            });
        });

        // Header scroll effect
        const header = document.getElementById('header');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Back to top button
        const backToTopButton = document.querySelector('.back-to-top');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('active');
            } else {
                backToTopButton.classList.remove('active');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Room filtering
        const filterButtons = document.querySelectorAll('.filter-btn');
        const roomCards = document.querySelectorAll('.room-card');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const filterValue = button.getAttribute('data-filter');

                // Filter rooms
                roomCards.forEach(card => {
                    if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Hero Slider
        const slides = document.querySelectorAll('.slide');
        let currentSlide = 0;

        function showSlide(index) {
            slides.forEach(slide => slide.classList.remove('active'));
            slides[index].classList.add('active');
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        // Auto slide every 5 seconds
        setInterval(nextSlide, 5000);

        // Testimonial Slider
        const testimonialTrack = document.querySelector('.testimonial-track');
        const testimonialDots = document.querySelectorAll('.testimonial-dot');
        let currentTestimonial = 0;

        function showTestimonial(index) {
            const width = document.querySelector('.testimonial-card').clientWidth;
            testimonialTrack.style.transform = `translateX(-${index * width}px)`;

            testimonialDots.forEach(dot => dot.classList.remove('active'));
            testimonialDots[index].classList.add('active');

            currentTestimonial = index;
        }

        testimonialDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showTestimonial(index);
            });
        });

        // Auto slide testimonials every 7 seconds
        setInterval(() => {
            const next = (currentTestimonial + 1) % testimonialDots.length;
            showTestimonial(next);
        }, 7000);

        // Form submission
        const form = document.getElementById('form');
        const formMessage = document.getElementById('form-message');

        form.addEventListener('submit', (e) => {
            e.preventDefault();

            // Simulate form submission
            formMessage.textContent = 'Thank you for your message! We will get back to you soon.';
            formMessage.style.display = 'block';
            formMessage.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
            formMessage.style.color = '#10b981';
            formMessage.style.border = '1px solid #10b981';

            // Reset form
            form.reset();

            // Hide message after 5 seconds
            setTimeout(() => {
                formMessage.style.display = 'none';
            }, 5000);
        });

        // Set current year in footer
        document.getElementById('year').textContent = new Date().getFullYear();

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Booking Modal Handler
        (function () {
            const modal = document.getElementById('booking-modal');
            const closeBtn = document.getElementById('close-modal');
            const cancelBtn = document.getElementById('cancel-modal');
            const openBtns = document.querySelectorAll('.open-book');
            const bookingForm = document.getElementById('booking-form');
            const bookingMessage = document.getElementById('booking-message');
            const checkinInput = document.getElementById('checkin');
            const checkoutInput = document.getElementById('checkout');

            // Open modal
            openBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const roomName = btn.getAttribute('data-room');
                    const roomId = btn.getAttribute('data-room-id');
                    const price = btn.getAttribute('data-price');

                    document.getElementById('modal-room-name').textContent = roomName;
                    document.getElementById('modal-room-price').textContent = 'LKR ' + parseFloat(price).toFixed(2) + ' / night';
                    document.getElementById('form-room-name').value = roomName;
                    document.getElementById('form-room-id').value = roomId;
                    document.getElementById('form-price').value = price;

                    // Set min date to today
                    const today = new Date().toISOString().split('T')[0];
                    checkinInput.min = today;
                    checkoutInput.min = today;

                    bookingMessage.style.display = 'none';
                    modal.style.display = 'flex';
                });
            });

            // Close modal
            closeBtn.addEventListener('click', () => modal.style.display = 'none');
            cancelBtn.addEventListener('click', () => modal.style.display = 'none');
            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });

            // Validate checkout date >= checkin date
            checkinInput.addEventListener('change', () => {
                checkoutInput.min = checkinInput.value;
                if (checkoutInput.value && checkoutInput.value < checkinInput.value) {
                    checkoutInput.value = '';
                }
            });

            // Submit booking
            bookingForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                bookingMessage.style.display = 'none';

                const formData = new FormData(bookingForm);

                try {
                    const res = await fetch('book_now.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    });
                    const data = await res.json();

                    if (res.status === 200 && data.success) {
                        bookingMessage.style.display = 'block';
                        bookingMessage.style.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                        bookingMessage.style.color = '#10b981';
                        bookingMessage.style.border = '1px solid #10b981';
                        bookingMessage.textContent = '✓ Reservation confirmed! Booking ID: ' + data.reservation_id;
                        bookingForm.reset();
                        setTimeout(() => modal.style.display = 'none', 2000);
                    } else if (res.status === 401) {
                        window.location.href = 'login.php?redirect=home';
                    } else {
                        bookingMessage.style.display = 'block';
                        bookingMessage.style.backgroundColor = 'rgba(255, 107, 107, 0.1)';
                        bookingMessage.style.color = '#b91c1c';
                        bookingMessage.style.border = '1px solid #fca5a5';
                        bookingMessage.textContent = '✕ ' + (data.message || 'Unable to complete booking.');
                    }
                } catch (err) {
                    bookingMessage.style.display = 'block';
                    bookingMessage.style.backgroundColor = 'rgba(255, 107, 107, 0.1)';
                    bookingMessage.style.color = '#b91c1c';
                    bookingMessage.style.border = '1px solid #fca5a5';
                    bookingMessage.textContent = '✕ Network error. Please try again.';
                }
            });
        })();

        // Food Order Management
        const foodModal = document.getElementById('food-modal');
        const closeFoodModal = document.getElementById('close-food-modal');
        const closeFoodBtn = document.getElementById('close-food-btn');
        let foodOrder = {};
        const menuItems = document.querySelectorAll('.menu-item');
        const addToOrderBtns = document.querySelectorAll('.add-to-order');

        // Open food modal
        function openFoodMenu() {
            foodOrder = {};
            document.getElementById('order-items-list').innerHTML = '';
            document.getElementById('order-total').textContent = '0.00';
            foodModal.style.display = 'flex';
        }

        // Close food modal
        closeFoodModal.addEventListener('click', () => foodModal.style.display = 'none');
        closeFoodBtn.addEventListener('click', () => foodModal.style.display = 'none');
        window.addEventListener('click', (e) => {
            if (e.target === foodModal) foodModal.style.display = 'none';
        });

        // Add item to order
        addToOrderBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const item = btn.closest('.menu-item');
                const itemId = item.getAttribute('data-id');
                const itemName = item.getAttribute('data-name');
                const itemPrice = parseFloat(item.getAttribute('data-price'));

                if (!foodOrder[itemId]) {
                    foodOrder[itemId] = { name: itemName, price: itemPrice, quantity: 0 };
                }
                foodOrder[itemId].quantity++;
                updateOrderDisplay();
            });
        });

        function updateOrderDisplay() {
            const list = document.getElementById('order-items-list');
            list.innerHTML = '';
            let total = 0;

            for (const itemId in foodOrder) {
                const item = foodOrder[itemId];
                const subtotal = item.price * item.quantity;
                total += subtotal;

                const orderItem = document.createElement('div');
                orderItem.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #ddd;';
                orderItem.innerHTML = `
                    <div style="flex: 1;">
                        <p style="margin: 0; color: #333; font-weight: 500;">${item.name}</p>
                        <p style="margin: 5px 0 0 0; color: #666; font-size: 0.85rem;">LKR ${item.price.toFixed(2)} x ${item.quantity}</p>
                    </div>
                    <div style="text-align: right;">
                        <p style="margin: 0; color: #8b7355; font-weight: 600;">LKR ${subtotal.toFixed(2)}</p>
                        <button type="button" class="remove-item" data-id="${itemId}" style="background: #ff6b6b; color: #fff; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; margin-top: 5px;">Remove</button>
                    </div>
                `;
                list.appendChild(orderItem);
            }

            document.getElementById('order-total').textContent = total.toFixed(2);

            // Remove buttons
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', () => {
                    delete foodOrder[btn.getAttribute('data-id')];
                    updateOrderDisplay();
                });
            });
        }

        document.getElementById('confirm-food-order').addEventListener('click', () => {
            // Save food order to session/localStorage for confirmation
            alert('Food items added to your reservation!');
            foodModal.style.display = 'none';
        });
    </script>
</body>

</html>
</script>
</body>

</html>