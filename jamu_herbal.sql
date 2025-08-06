-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 03, 2025 at 04:44 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jamu_herbal`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `total_price` decimal(10,2) DEFAULT '0.00',
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(10, 6, '12000.00', 'paid', '2025-07-09 13:31:17'),
(11, 8, '22000.00', 'paid', '2025-07-09 13:36:44'),
(12, 10, '46000.00', 'pending', '2025-07-31 14:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int NOT NULL,
  `cart_id` int NOT NULL,
  `product_variant_id` int NOT NULL,
  `qty` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `qty`, `price`, `created_at`) VALUES
(21, 10, 20, 1, '12000.00', '2025-07-09 13:31:17'),
(22, 11, 17, 1, '10000.00', '2025-07-09 13:36:44'),
(23, 11, 20, 1, '12000.00', '2025-07-09 13:36:50'),
(24, 12, 17, 1, '10000.00', '2025-07-31 14:45:37'),
(25, 12, 18, 2, '12000.00', '2025-07-31 14:45:40'),
(26, 12, 20, 1, '12000.00', '2025-07-31 14:45:47');

-- --------------------------------------------------------

--
-- Table structure for table `content_images`
--

CREATE TABLE `content_images` (
  `id` int NOT NULL,
  `type` varchar(15) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `content_images`
--

INSERT INTO `content_images` (`id`, `type`, `image_path`, `created_at`) VALUES
(1, 'about', 'about.jpg', '2025-05-14 06:44:56'),
(2, 'banner', 'banner-1.png', '2025-05-14 06:48:43'),
(3, 'banner', 'banner-2.png', '2025-05-14 06:49:52'),
(4, 'banner', 'banner-3.png', '2025-05-14 06:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `ongkir` int DEFAULT NULL,
  `asuransi` int DEFAULT NULL,
  `layanan` int DEFAULT NULL,
  `total_amount` int NOT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `transaction_time` datetime DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `shipped_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_id`, `ongkir`, `asuransi`, `layanan`, `total_amount`, `payment_type`, `transaction_time`, `transaction_status`, `created_at`, `shipped_at`) VALUES
(6, 9, 'INV-1749749032', 10000, 1000, 2000, 37000, 'bank_transfer', '2025-06-13 00:23:59', 'completed', '2025-06-12 17:24:23', '2025-06-19 22:22:20'),
(7, 9, 'INV-1750059815', 9000, 1000, 2000, 34000, 'bank_transfer', '2025-06-16 14:43:39', 'shipping', '2025-06-16 07:43:55', '2025-06-20 14:04:04'),
(8, 9, 'INV-1750343862', 10000, 1000, 2000, 25000, 'bank_transfer', '2025-06-19 21:37:50', 'shipping', '2025-06-19 14:39:22', '2025-06-20 14:02:30'),
(9, 9, 'INV-1750393242', 550000, 1000, 2000, 685000, 'cstore', '2025-06-20 11:20:47', 'completed', '2025-06-20 04:21:03', '2025-06-20 11:21:51'),
(10, 6, 'INV-1752067902', 40000, 1000, 2000, 55000, 'bank_transfer', '2025-07-09 20:31:53', 'settlement', '2025-07-09 13:32:27', NULL),
(11, 8, 'INV-1752068266', 12000, 1000, 2000, 37000, 'bank_transfer', '2025-07-09 20:37:51', 'settlement', '2025-07-09 13:38:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`, `created_at`) VALUES
(4, 6, 11, 18, 2, 12000, '2025-06-12 17:24:23'),
(5, 7, 11, 17, 1, 10000, '2025-06-16 07:43:55'),
(6, 7, 11, 18, 1, 12000, '2025-06-16 07:43:55'),
(7, 8, 12, 20, 1, 12000, '2025-06-19 14:39:22'),
(8, 9, 12, 20, 11, 12000, '2025-06-20 04:21:03'),
(9, 10, 12, 20, 1, 12000, '2025-07-09 13:32:27'),
(10, 11, 11, 17, 1, 10000, '2025-07-09 13:38:06'),
(11, 11, 12, 20, 1, 12000, '2025-07-09 13:38:06');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(61) NOT NULL,
  `description` text,
  `main_image` varchar(255) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `main_image`, `type`, `created_at`) VALUES
(11, 'Jamu Kunyit Asam', 'Jamu khas Indonesia berbahan dasar kunyit dan asam jawa, membantu menyegarkan tubuh, meredakan nyeri haid, dan melancarkan pencernaan.', '11_108bc0b9.jpg', 'herbal', '2025-05-21 16:14:47'),
(12, 'Susu', 'bla bla bla', '12_5a23a197.png', 'Cair', '2025-06-13 03:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `variant_name` varchar(15) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `variant_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `weight` int DEFAULT '250'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_name`, `price`, `stock`, `variant_image`, `created_at`, `weight`) VALUES
(17, 11, 'Original', '10000.00', 23, '11_2e1bfc69.jpg', '2025-05-21 16:14:47', 250),
(18, 11, 'Plus Madu', '12000.00', 9, '11_8d4d7b48.jpg', '2025-05-21 16:14:47', 250),
(19, 11, 'Tanpa Gula', '11000.00', 0, '11_267d0118.png', '2025-05-21 16:14:47', 250),
(20, 12, 'Formula', '12000.00', 88, '12_9eff6f1f.png', '2025-06-13 03:57:23', 250);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `province_id` int DEFAULT NULL,
  `province_name` varchar(100) DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `city_name` varchar(100) DEFAULT NULL,
  `district_id` varchar(255) DEFAULT NULL,
  `district_name` varchar(255) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `full_address` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `phone`, `created_at`, `province_id`, `province_name`, `city_id`, `city_name`, `district_id`, `district_name`, `postal_code`, `full_address`) VALUES
(1, 'Muhammad Daffa Al Hakim', 'muhammaddaffaalhakim13@gmail.com', '$2y$10$4AXfBIaGosc3YlDmcu98SOeoriPGYzdpiy9kW3W4riKhIP3Qbto62', 'user', NULL, '2025-05-15 14:52:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Admin', 'admin@gmail.com', '$2y$10$OlTbVkARgeWiPGH6weA45u4d0jlcveuMfWSnENeZyirUYHZVpTLc2', 'admin', NULL, '2025-05-16 11:42:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'user', 'user@gmail.com', '$2y$10$gJLNnZ4iyurtppRe/jP21Oj9mq7lOaR.hW/FNrMiv0sjR0XQUsYa2', 'user', '081299306586', '2025-05-25 06:53:23', 6, 'DKI Jakarta', 151, 'Kota Jakarta Barat', NULL, NULL, '11510', 'Jl. Duta Buntu RT.11/RW.07, Duri Kepa, Kebon Jeruk, Jakarta Barat'),
(6, 'Ahmad Fikriansyah', 'fikri@gmail.com', '$2y$10$Jivc6hosYfIwJH1Enn1uRuj.Clqg.sPoQcHWkg98SrLKa0ZMCOplC', 'user', '08123456789', '2025-06-08 01:32:35', 9, 'Jawa Barat', 115, 'Kota Depok', NULL, NULL, '16431', 'Jl. Margonda No.38, Depok, Kec. Pancoran Mas, Kota Depok, Jawa Barat 16431'),
(8, 'defibau', 'defibau@gmail.com', '$2y$10$8BM/uzWSqtx5YmHKz7FosuLxSQT0CIsF05VFLEcWKiGQO4O1.wRZO', 'user', '12112', '2025-07-09 13:36:05', 9, 'Jawa Barat', 23, 'Kota Bandung', NULL, NULL, '1212', 'q'),
(9, 'Defi Ngeselin', 'defingeselin@gmail.com', '$2y$10$n/DP7pAmxuur2nBciguGM.Kjun/faQIAv21cZrVz3tcq3ebsdRL4e', 'user', NULL, '2025-07-25 10:17:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'ark', 'ark@gmail.com', '$2y$10$N9KrZRcl4ZsdaAwgp/cqO.YnPDyvXSDxu7s18lD5MeqswH0oKQ5Ka', 'user', '081233456', '2025-07-31 09:58:13', 10, 'DKI JAKARTA', 135, 'JAKARTA BARAT', '1324', 'KEBON JERUK', '11510', 'Jl. Kb. Raya 2 No.25, RT.1/RW.2, Duri Kepa, Kec. Kb. Jeruk, Kota Jakarta Barat, Daerah Khusus Ibukota Jakarta 11510');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `content_images`
--
ALTER TABLE `content_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`full_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `content_images`
--
ALTER TABLE `content_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
