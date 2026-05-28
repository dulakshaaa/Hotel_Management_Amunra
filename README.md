# Online Hotel Booking System

## Overview

The Online Hotel Booking System is a web-based application designed to simplify hotel reservation management for both customers and administrators. The system allows guests to browse available rooms, make reservations online, manage bookings, and process payments efficiently.

This project is developed using PHP, MySQL, HTML, CSS, and JavaScript.

---

# Features

## Customer Features

* User Registration & Login
* Browse Available Rooms
* Room Details & Pricing
* Online Room Booking
* Booking Confirmation
* Booking History
* Secure Payment Processing
* Responsive User Interface

---

## Admin Features

* Admin Dashboard
* Manage Rooms
* Manage Room Categories
* Manage Bookings
* Customer Management
* Payment Management
* Reports & Analytics
* Booking Status Updates

---

# Technologies Used

## Frontend

* HTML5
* CSS3
* JavaScript

## Backend

* PHP

## Database

* MySQL

---

# Installation Guide

## 1. Clone the Repository

```bash
git clone https://github.com/your-username/hotel-booking-system.git
```

---

## 2. Move Project to Server Directory

### XAMPP

Move the project folder to:

```bash
htdocs/
```

### WAMP

Move the project folder to:

```bash
www/
```

---

## 3. Create Database

1. Open phpMyAdmin
2. Create a database named:

```sql
hotel_booking_system
```

3. Import the `database.sql` file

---

## 4. Configure Database Connection

Open:

```bash
includes/config.php
```

Update database credentials:

```php
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "hotel_booking_system";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}
?>
```

---

# Running the Project

Start Apache and MySQL from XAMPP/WAMP and open:

```bash
http://localhost/hotel-booking-system
```

---

# Database Tables

* users
* rooms
* room_types
* bookings
* payments
* admins

---

# Future Enhancements

* Online Payment Gateway Integration
* Email Notifications
* SMS Notifications
* Room Availability Calendar
* Multi-language Support
* Mobile Application
* AI-based Room Recommendations

---

# Screenshots

Add screenshots of:

* Homepage
* Room Listing
* Booking Form
* Admin Dashboard

---

# Security Features

* Password Hashing
* Session Authentication
* SQL Injection Prevention
* Input Validation

---

# Author

Dulaksha Rajapaksha
Software Engineering Student & Full-Stack Developer

---

# License

This project is developed for educational and learning purposes.
