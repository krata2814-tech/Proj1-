-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql102.infinityfree.com
-- Generation Time: 24 أغسطس 2026 الساعة 15:00
-- إصدار الخادم: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42732437_electronics_store_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(1, 'أجهزة حواسيب'),
(2, 'هواتف ذكية'),
(3, 'قطع وصيانة');

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 1,
  `category_id` int(11) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `stock`, `category_id`, `added_by`, `created_at`, `image_url`) VALUES
(1, ' lenovo p52', NULL, '400.00', 1, NULL, 2, '2026-08-22 14:59:54', NULL),
(2, 'شاشة لابتوب Lenovo FHD 15.6', 'شاشة دقة عالية مضادة للانعكاس بديلة للابتوبات لينيوفو', '65.00', 1, 3, 2, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500'),
(3, 'قرص تخزين SSD Kingston 512GB', 'قرص تخزين سريع نوع NVMe M.2 لتحسين أداء النظام', '45.00', 1, 3, 2, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1597872250970-450c16152de2?w=500'),
(4, 'شاحن لابتوب HP الذكي 65W', 'شاحن أصلّي ذكي متوافق مع معظم أجهزة HP', '25.00', 1, 3, 2, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=500'),
(5, 'لابتوب ASUS TUF Gaming i7', 'معالج Core i7 مع كرت شاشة RTX 4060 ورام 16GB', '950.00', 1, 1, 3, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500'),
(6, 'هاتف Samsung Galaxy S24 Ultra', 'شاشة 120Hz وقلم S-Pen مع ذاكرة 256GB وفرام ألومنيوم', '1150.00', 1, 2, 3, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=500'),
(7, 'سماعة لاسلكية Sony WH-1000XM5', 'أحدث تقنية عزل ضوضاء مع بطارية تدوم حتى 30 ساعة', '280.00', 1, 2, 3, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'),
(8, 'شاشة قيمنق Curved Xiaomi 27', 'شاشة منحنية بتردد 165Hz وزاوية رؤية واسعة مناسبة للألعاب', '220.00', 1, 1, 3, '2026-08-22 19:16:54', 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer') NOT NULL DEFAULT 'customer',
  `is_protected` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_protected`, `created_at`) VALUES
(1, 'المدير العام (الأدمن)', 'admin@store.com', '$2y$10$4q9Z9M.G1/2z1E7kPqYyE.s5/5G7e1Y9J.x8s8x8s8x8s8x8s8x8s', 'admin', 1, '2026-08-22 14:28:08'),
(2, 'سعيد - فني الصيانة', 'saeed@store.com', '$2y$10$4q9Z9M.G1/2z1E7kPqYyE.s5/5G7e1Y9J.x8s8x8s8x8s8x8s8x8s', 'staff', 0, '2026-08-22 14:28:08'),
(3, 'خالد - مسؤول المبيعات', 'khaled@store.com', '$2y$10$4q9Z9M.G1/2z1E7kPqYyE.s5/5G7e1Y9J.x8s8x8s8x8s8x8s8x8s', 'staff', 0, '2026-08-22 14:28:08'),
(4, 'محمد - العميل', 'customer@gmail.com', '$2y$10$4q9Z9M.G1/2z1E7kPqYyE.s5/5G7e1Y9J.x8s8x8s8x8s8x8s8x8s', 'customer', 0, '2026-08-22 14:28:08'),
(6, 'مازن عبده حمود العزي', 'emp_ebr@gmail.com', '$2y$10$bLfCkQ3Ymesz3c3xHl4Q9O8Qf3MNKB4m6/VA8nPOqvpH.u4ijhw7q', 'customer', 0, '2026-08-23 19:54:05'),
(7, 'علاء الدين ناصر', 'krata2814@gmail.com', '$2y$10$5WPN1PzzC3DZ039vMHIoxuiq3Y39dD5isFLFTB7Grgw6OJ/xB4VkG', 'customer', 0, '2026-08-23 22:32:52'),
(8, 'على شمووووحي', 'mazenalazey1@gmail.com', '$2y$10$8RSsWpYhh7cHCPvb30cEGOrdBm8zTk/D5E50sB9/TSryk0NEZqpfS', 'customer', 0, '2026-08-24 12:35:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- قيود الجداول المحفوظة
--

--
-- القيود للجدول `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- القيود للجدول `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
