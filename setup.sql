-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 17, 2025 at 07:01 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `amunra`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `name`, `description`, `price`, `category`, `image_url`, `available`, `created_at`) VALUES
(12, 'Grilled Salmon', 'Premium Atlantic salmon with seasonal vegetables', 28.99, 'main', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(13, 'Filet Mignon', 'USDA Prime beef with truffle butter', 42.99, 'main', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(14, 'Lobster Tail', 'Fresh Maine lobster tail with lemon butter', 35.99, 'main', 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(15, 'Chocolate Lava Cake', 'Warm chocolate cake with vanilla ice cream', 9.99, 'desserts', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(16, 'Tiramisu', 'Classic Italian dessert with espresso', 8.99, 'desserts', 'https://images.unsplash.com/photo-1571115177098-24ec42ed204d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(17, 'Egyptian Ful Medames', 'Traditional fava bean dip', 7.99, 'appetizers', 'https://images.unsplash.com/photo-1585238341710-4913d3a3a48f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(18, 'Koshari', 'Egyptian pasta with lentils and chickpeas', 14.99, 'main', 'https://images.unsplash.com/photo-1585238341710-4913d3a3a48f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(19, 'Fresh Orange Juice', 'Freshly squeezed', 5.99, 'beverages', 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32'),
(20, 'Premium Egyptian Wine', 'Red wine from local vineyards', 24.99, 'beverages', 'https://images.unsplash.com/photo-1510812431401-41d2cab2707d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=60', 1, '2025-11-24 03:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `reservation_id`, `user_id`, `total_amount`, `status`, `created_at`) VALUES
(15, 10, 1, 7.99, 'completed', '2025-11-24 05:49:39'),
(17, 14, 1, 50.98, 'cancelled', '2025-12-01 13:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `quantity`, `price`, `subtotal`, `created_at`) VALUES
(14, 15, 17, 1, 7.99, 7.99, '2025-11-24 05:49:39'),
(16, 17, 14, 1, 35.99, 35.99, '2025-12-01 13:56:49'),
(17, 17, 18, 1, 14.99, 14.99, '2025-12-01 13:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `checkin` date NOT NULL,
  `checkout` date NOT NULL,
  `checkin_time` time DEFAULT '14:00:00',
  `checkout_time` time DEFAULT '11:00:00',
  `guests` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `room_name`, `price`, `checkin`, `checkout`, `checkin_time`, `checkout_time`, `guests`, `created_at`) VALUES
(10, 1, 'Aphrodite Suites', 299.00, '2025-11-26', '2025-11-29', '16:00:00', '11:00:00', 2, '2025-11-24 05:25:16'),
(13, 1, 'Aphrodite Suites', 299.00, '2025-11-25', '2025-11-28', '16:00:00', '11:00:00', 2, '2025-11-24 16:46:54'),
(14, 1, 'Aphrodite Suites', 299.00, '2025-12-11', '2026-01-03', '15:00:00', '12:00:00', 2, '2025-12-01 13:55:11'),
(15, 1, 'Hermes Chambers', 199.00, '2025-12-18', '2026-01-10', '16:00:00', '12:00:00', 2, '2025-12-14 15:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `restaurants`
--

CREATE TABLE `restaurants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price_range` varchar(10) DEFAULT NULL,
  `open` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurants`
--

INSERT INTO `restaurants` (`id`, `name`, `type`, `image_url`, `description`, `price_range`, `open`, `created_at`, `updated_at`, `features`) VALUES
(1, 'Pharaoh\'s Feast', 'Fine Dining', 'https://cdn.pixabay.com/photo/2017/08/07/08/56/restaurant-2607129_1280.jpg', 'Luxury Egyptian fine dining experience.', '$$$', 1, '2025-12-14 14:48:25', '2025-12-14 14:52:28', '[\"Fine Dining\", \"Live Music\", \"Elegant Interior\"]'),
(2, 'Nile Breeze Café', 'Cafe', 'https://cdn.pixabay.com/photo/2016/11/18/14/05/cafe-1836415_1280.jpg', 'Relaxed café with drinks and light meals.', '$$', 1, '2025-12-14 14:48:25', '2025-12-14 14:52:28', '[\"Coffee\", \"Outdoor Seating\", \"Wi-Fi\"]'),
(3, 'Golden Pyramid Grill', 'Buffet', 'https://cdn.pixabay.com/photo/2016/11/29/12/54/buffet-1866499_1280.jpg', 'International buffet experience.', '$$$', 0, '2025-12-14 14:48:25', '2025-12-14 14:52:28', '[\"Buffet\", \"Family Friendly\", \"Live Cooking\"]');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_features`
--

CREATE TABLE `restaurant_features` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `feature` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `restaurant_features`
--

INSERT INTO `restaurant_features` (`id`, `restaurant_id`, `feature`) VALUES
(1, 1, 'Fine Dining'),
(2, 1, 'Live Music'),
(3, 1, 'Elegant Interior'),
(4, 2, 'Coffee'),
(5, 2, 'Outdoor Seating'),
(6, 2, 'Wi-Fi'),
(7, 3, 'Buffet'),
(8, 3, 'Family Friendly'),
(9, 3, 'Live Cooking');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`features`)),
  `total_rooms` int(11) NOT NULL DEFAULT 10,
  `available_rooms` int(11) NOT NULL DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `category`, `price`, `description`, `image_url`, `features`, `total_rooms`, `available_rooms`, `created_at`) VALUES
(1, 'Hermes Chambers', 'standard', 199.00, 'Enjoy stunning views of the Nile River from your private balcony in our elegantly appointed standard room.', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '[\"King Bed\", \"River View\", \"Free WiFi\"]', 8, 7, '2025-11-24 03:26:07'),
(2, 'Aphrodite Suites', 'deluxe', 299.00, 'Experience royal comfort in our spacious deluxe room featuring Egyptian-inspired decor and premium amenities.', 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '[\"King Bed\", \"Sitting Area\", \"Luxury Bath\"]', 6, 3, '2025-11-24 03:26:07'),
(3, 'Zues\' Throne', 'suite', 499.00, 'Live like Egyptian royalty in our expansive suite with separate living area, dining space, and panoramic Nile views.', 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80', '[\"Separate Living Room\", \"Dining Area\"]', 4, 4, '2025-11-24 03:26:07'),
(4, 'Ra-Apollo Penthouse', 'suite', 499.00, 'Live like Egyptian royalty in our expansive suite with separate living area, dining space, and panoramic Nile views.', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS5Wf8WxRdfCGXG6TLperpYJkaMtf0VnX03TQ&s', '[\"Separate Living Room\", \"Dining Area\"]', 3, 3, '2025-11-24 03:26:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `fullname` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `nic` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `fullname`, `contact_number`, `nic`) VALUES
(1, 'demo', 'demo@example.com', '$2y$10$/fjHS5C90YxOukXucJpA3u7ENV9j/KEW3zpLTAj9T8PZJOPWB5RgW', '2025-11-24 02:01:54', NULL, NULL, NULL),
(2, 'dula', 'dulaksharajapaksha4@gmail.com', '$2y$10$yHRYhbrKyAQXVvgXsFmcGucnzzHoUNj988aEQAkY8XzfNSh4vVlfq', '2025-11-24 02:13:36', NULL, NULL, NULL),
(4, 'dulaksha', 'dulaksharajapaksha@gmail.com', '$2y$10$wFtdGuRjF/oUFeeJC1MCwOo99OfJDCM2KkG/Pq40819mtq/hW6RWq', '2025-12-17 17:29:57', 'Dulaksha Rajapaksha', '0774193618', '200286799546');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `restaurants`
--
ALTER TABLE `restaurants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `restaurant_features`
--
ALTER TABLE `restaurant_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `restaurants`
--
ALTER TABLE `restaurants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `restaurant_features`
--
ALTER TABLE `restaurant_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`menu_item_id`) REFERENCES `menu` (`id`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_features`
--
ALTER TABLE `restaurant_features`
  ADD CONSTRAINT `restaurant_features_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
