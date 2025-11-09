-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 11:55 AM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'sahat', 'sahatahmed387@gmail.com', '123456789', '2025-11-01 13:14:17');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `added_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`) VALUES
(2, 'Electronics', 'Phones, laptops and accessories', '0000-00-00 00:00:00'),
(3, 'Fashion', 'Clothing & accessories', '0000-00-00 00:00:00'),
(4, 'Grocery', 'Daily groceries', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `message`, `created_at`, `is_read`) VALUES
(1, 'Sahat Ahmed', 'sahatahmed470@gmail.com', '09007942704', 'Hello', '2025-11-08 05:27:17', 0),
(2, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:28:48', 0),
(3, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:37:08', 0),
(4, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:37:54', 0),
(5, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:44:42', 0),
(6, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:52:08', 0),
(7, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 05:52:38', 0),
(9, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 06:08:36', 0),
(10, 'Sahat Ahmed', 'sahatahmed387@gmail.com', '+91 0000000098362192', 'HIIIIIIIIIIIIIIIIIIIIII', '2025-11-08 06:09:00', 0),
(11, 'Sahat Ahmed', 'sahatahmed470@gmail.com', '09007942704', 'Hiiiiiiiiiiiiiiiiii', '2025-11-08 06:09:22', 1),
(12, 'Sahat Ahmed', 'sahatahmed470@gmail.com', '09007942704', 'Hiiiiiiiiiiiiiiiiii', '2025-11-08 06:09:50', 1),
(14, 'Sahat Ahmed', 'sahatahmed470@gmail.com', '09007942704', 'Sahata      hvc', '2025-11-09 09:28:35', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `order_status` enum('Pending','Shipped','In Transit','Delivered','Cancelled') DEFAULT 'Pending',
  `payment_method` varchar(20) DEFAULT 'COD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `total_amount`, `delivery_address`, `order_status`, `payment_method`) VALUES
(1, 1, '2025-11-02 14:24:06', 4000.00, 'sahat', 'Cancelled', 'COD'),
(2, 1, '2025-11-02 14:25:15', 2000.00, 'sahat ahmed', 'Cancelled', 'COD'),
(3, 1, '2025-11-05 09:26:14', 2000.00, 'hi', '', 'COD'),
(4, 1, '2025-11-05 16:37:30', 99.99, 'Thakurpukur', '', 'COD'),
(5, 1, '2025-11-07 14:07:22', 499.99, 'hello', '', 'COD');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 3, 2, 2000.00, 4000.00),
(2, 2, 3, 1, 2000.00, 2000.00),
(3, 3, 3, 1, 2000.00, 2000.00),
(4, 4, 5, 1, 99.99, 99.99),
(5, 5, 7, 1, 499.99, 499.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `original_price` int(50) NOT NULL,
  `rating` double NOT NULL,
  `on_sale` tinyint(1) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `original_price`, `rating`, `on_sale`, `image_url`, `category_id`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(3, 'cfghx', 'cgxghxgc', 2000.00, 0, 0, 0, '1762322088_11A.jpg', 2, 8, '2025-11-01 13:36:09', '2025-11-05 11:24:48'),
(4, 'bus', '8 sitter bus', 1000.00, 0, 0, 0, '0', 2, 5, '2025-11-02 09:27:09', '2025-11-02 09:27:09'),
(5, 'gandw', 'hacdhavs', 99.99, 0, 0, 0, '1762322144_NAAC-Logo-Unit-1.png', 2, 10, '2025-11-05 11:25:24', '2025-11-05 11:25:44'),
(6, 'game', 'hello', 499.99, 1000, 0, 0, '../uploads/1762345193_11A.jpg', 2, 10, '2025-11-05 17:49:53', '2025-11-05 17:49:53'),
(7, 'game', 'bgcgcgn c', 499.99, 1000, 5, 1, '../uploads/1762346732_NAAC-Logo-Unit-1.png', 2, 1, '2025-11-05 18:15:32', '2025-11-05 18:48:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(15) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `registration_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `email`, `mobile_number`, `password_hash`, `registration_date`, `last_login`) VALUES
(1, 'Sahat', 'Ahmed', 'sahat10', 'sahatahmed470@gmail.com', '9007942704', '$2y$10$Jc3HYjUsNhdMZpFdMZtFEOTjB6dRMb880z.K.T26iO5iNapAqe1UO', '2025-11-02 13:11:32', '2025-11-09 14:21:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
