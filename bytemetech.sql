-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 07:05 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bytemetech`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `ADDID` int(11) NOT NULL,
  `UNIT_NUMBER` varchar(255) DEFAULT NULL,
  `STREET` varchar(255) DEFAULT NULL,
  `CITY` varchar(255) DEFAULT NULL,
  `STATE_ID` int(11) DEFAULT NULL,
  `COUNTRY_ID` int(11) DEFAULT NULL,
  `UID` int(11) NOT NULL,
  `TYPE` enum('Billing','Delivery') DEFAULT 'Delivery',
  `DEFAULT_ADDRESS` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`ADDID`, `UNIT_NUMBER`, `STREET`, `CITY`, `STATE_ID`, `COUNTRY_ID`, `UID`, `TYPE`, `DEFAULT_ADDRESS`) VALUES
(5, '1', 'Jalan Bahagia', 'Kuala Lumpur, Malaysia', 14, 1, 7, 'Delivery', 1),
(9, '2', 'Jalan Bunga Raya', 'Petaling Jaya', 12, 1, 7, 'Delivery', 0),
(10, '1', '22A, Jalan lndah 23, Taman Cheras lndah', 'Kuala Lumpur, Malaysia', 5, 1, 21, 'Delivery', 1),
(11, '123', 'ABC', 'ABCCity', 14, 1, 24, 'Delivery', 1),
(12, '123', 'daww', 'awedaw', 10, 1, 25, 'Delivery', 0),
(14, '456', 'fgbd', 'gsdrt', 17, 2, 25, 'Delivery', 1);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `AID` int(11) NOT NULL,
  `USERNAME` varchar(50) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `ROLE` enum('Super Admin','Logistics','Sales','Customer Support','Marketing','Moderator') NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `LAST_LOGGEDIN` datetime DEFAULT NULL,
  `EMAIL` varchar(255) NOT NULL,
  `FIRST_NAME` varchar(255) DEFAULT NULL,
  `LAST_NAME` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`AID`, `USERNAME`, `PASSWORD`, `ROLE`, `CREATED_AT`, `LAST_LOGGEDIN`, `EMAIL`, `FIRST_NAME`, `LAST_NAME`) VALUES
(2, 'SUPERADMIN', '$2y$10$Er3KWc8Fv0eQDd5V7I4oZuj4ADP1FcNQ5RGeGrBBZ34btM2gTrcDO', 'Super Admin', '2025-04-03 07:31:09', '2025-04-28 00:08:25', 'superadmin@gmail.com', 'Ronnie', 'Leong'),
(6, 'TC', '$2y$10$d335I63PBBf3OivRzM0Fk.cT/Y9MGkuqQ1msE890Bm8INsbYZqTpm', 'Super Admin', '2025-04-27 13:17:02', '2025-04-27 21:18:07', 'natc-wm21@student.tarc.edu.my', 'Ting', 'Choon'),
(7, 'sales', '$2y$10$sbXiB61QcJqwAMRcRccQBOf1/bDfvu7OgymbdJllV4JpNiyDUQRZC', 'Sales', '2025-04-27 15:17:49', NULL, 'sales@gmail.com', 'sales', 'admin'),
(8, 'logistics', '$2y$10$be60jtdlRgsl5LOibfcjS.ZrWEUDaMP4HfPIw4U7e6m2SHprpesjq', 'Logistics', '2025-04-27 15:18:09', '2025-04-27 23:19:27', 'logistics@gmail.com', 'logistics', 'admin'),
(9, 'customersupport', '$2y$10$ESrNJqmKmHzBSOmWzjB7T.ojgS6qJ5BrPF8kFVqS.nDquHdH.FQRW', 'Customer Support', '2025-04-27 15:18:41', NULL, 'customersupport@gmail.com', 'customer', 'support'),
(10, 'marketing', '$2y$10$6dCSji.1tNCkCp.gwbpMf.Js8f1tob1BZg0QV5RwTc..TLfFr0pki', 'Marketing', '2025-04-27 15:19:02', NULL, 'marketing@gmail.com', 'marketing', 'admin'),
(11, 'moderator', '$2y$10$JC1vP9EzyGhEMjoZH.jDJewl23l7vDtUQMg1Jmns8WmCpyyvXj1Rq', 'Moderator', '2025-04-27 15:19:19', NULL, 'moderator@gmail.com', 'moderator', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `BILL_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `TOTAL_AMOUNT` decimal(10,2) NOT NULL,
  `PAYMENT_METHOD` enum('Credit Card','PayPal','Bank Transfer','Cash on Delivery') NOT NULL,
  `PAYMENT_STATUS` enum('Paid','Unpaid','Refunded') DEFAULT 'Unpaid',
  `BILLING_DATE` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `CID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `PID` int(11) NOT NULL,
  `QUANTITY` int(11) DEFAULT 1,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`CID`, `UID`, `PID`, `QUANTITY`, `CREATED_AT`, `UPDATED_AT`) VALUES
(64, 7, 60, 1, '2025-04-27 15:38:57', '2025-04-27 15:38:57');

-- --------------------------------------------------------

--
-- Table structure for table `contact_form`
--

CREATE TABLE `contact_form` (
  `CONTACT_ID` int(11) NOT NULL,
  `CONTACT_FIRSTNAME` varchar(255) NOT NULL,
  `CONTACT_LASTNAME` varchar(255) NOT NULL,
  `CONTACT_EMAIL` varchar(255) NOT NULL,
  `CONTACT_MESSAGE` text NOT NULL,
  `CONTACT_IP_ADDRESS` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `contact_form`
--

INSERT INTO `contact_form` (`CONTACT_ID`, `CONTACT_FIRSTNAME`, `CONTACT_LASTNAME`, `CONTACT_EMAIL`, `CONTACT_MESSAGE`, `CONTACT_IP_ADDRESS`) VALUES
(1, 'John', 'Doe', 'john@gmail.com', 'testing', '175.143.170.33'),
(4, 'Test 1', 'asdfg', 'test@gamil.com', 'Test message', '103.130.13.162'),
(8, 'Test 4', 'asdfg', 'test@gamil.com', 'Test message', '103.130.13.162'),
(14, 'Test', '123', 'natc-wm21@student.tarc.edu.my', 'Hello World !', '118.101.178.105');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `COUNTRY_ID` int(11) NOT NULL,
  `COUNTRY_NAME` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`COUNTRY_ID`, `COUNTRY_NAME`) VALUES
(3, 'Brunei'),
(1, 'Malaysia'),
(2, 'Singapore'),
(4, 'Thailand');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ORDER_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `TOTAL_AMOUNT` decimal(10,2) NOT NULL,
  `STATUS` enum('Pending','Processing','Shipped','Delivered','Cancelled','Refunded') DEFAULT 'Pending',
  `PAYMENT_METHOD` enum('Credit Card','PayPal','Bank Transfer','Cash on Delivery') NOT NULL,
  `PAYMENT_STATUS` enum('Paid','Unpaid','Refunded') DEFAULT 'Unpaid',
  `BILLING_ADDRESS_ID` int(11) NOT NULL,
  `SHIPPING_ADDRESS_ID` int(11) NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ORDER_ID`, `UID`, `TOTAL_AMOUNT`, `STATUS`, `PAYMENT_METHOD`, `PAYMENT_STATUS`, `BILLING_ADDRESS_ID`, `SHIPPING_ADDRESS_ID`, `CREATED_AT`, `UPDATED_AT`) VALUES
(1, 7, '950.00', 'Delivered', 'Credit Card', 'Paid', 5, 5, '2025-04-22 16:26:12', '2025-04-23 06:32:21'),
(2, 7, '260.10', 'Cancelled', 'Credit Card', 'Refunded', 5, 5, '2025-04-23 09:47:04', '2025-04-23 10:28:31'),
(3, 7, '1124.12', 'Processing', 'Credit Card', 'Paid', 5, 5, '2025-04-23 10:29:26', '2025-04-23 10:29:39'),
(4, 7, '5469.47', 'Processing', 'Credit Card', 'Paid', 5, 5, '2025-04-23 10:30:45', '2025-04-24 04:56:09'),
(5, 7, '668.97', 'Pending', 'Credit Card', 'Unpaid', 5, 5, '2025-04-23 10:43:38', '2025-04-23 10:43:38'),
(6, 7, '668.97', 'Pending', 'Credit Card', 'Unpaid', 5, 5, '2025-04-23 10:44:59', '2025-04-23 10:44:59'),
(7, 7, '668.97', 'Processing', 'Credit Card', 'Paid', 5, 5, '2025-04-23 10:50:28', '2025-04-23 11:37:56'),
(8, 7, '839.47', 'Cancelled', 'Credit Card', 'Unpaid', 5, 5, '2025-04-23 11:04:44', '2025-04-23 11:42:24'),
(9, 7, '0.00', 'Processing', '', 'Paid', 5, 5, '2025-04-24 05:24:13', '2025-04-24 05:24:13'),
(10, 21, '208.08', 'Processing', 'Credit Card', 'Paid', 10, 10, '2025-04-24 05:26:54', '2025-04-24 05:27:06'),
(11, 21, '143.03', 'Processing', 'Credit Card', 'Paid', 10, 10, '2025-04-24 05:54:21', '2025-04-24 05:54:33'),
(12, 21, '0.00', 'Processing', '', 'Paid', 10, 10, '2025-04-24 06:24:37', '2025-04-24 06:24:37'),
(13, 21, '1499.99', 'Processing', 'Credit Card', 'Paid', 10, 10, '2025-04-24 06:37:54', '2025-04-26 05:06:07'),
(14, 7, '799.99', 'Pending', 'Credit Card', 'Unpaid', 5, 5, '2025-04-24 06:38:48', '2025-04-24 06:38:48'),
(15, 7, '160.10', 'Cancelled', 'Credit Card', 'Paid', 5, 5, '2025-04-24 06:41:06', '2025-04-27 16:22:54'),
(16, 21, '195.05', 'Pending', 'Credit Card', 'Unpaid', 10, 10, '2025-04-26 05:02:55', '2025-04-26 05:02:55'),
(17, 7, '5750.50', 'Cancelled', 'Credit Card', 'Paid', 5, 5, '2025-04-27 03:21:16', '2025-04-27 16:15:20'),
(18, 24, '0.00', 'Delivered', '', 'Paid', 11, 11, '2025-04-27 13:14:05', '2025-04-27 13:18:51'),
(19, 25, '3833.53', 'Processing', 'Credit Card', 'Paid', 12, 12, '2025-04-27 13:33:10', '2025-04-27 13:34:07'),
(20, 25, '4250.00', 'Processing', 'Credit Card', 'Paid', 12, 12, '2025-04-27 13:49:57', '2025-04-27 13:51:57'),
(21, 25, '9670.68', 'Cancelled', 'Credit Card', 'Refunded', 12, 12, '2025-04-27 13:52:47', '2025-04-27 13:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `ORDER_ITEM_ID` int(11) NOT NULL,
  `ORDER_ID` int(11) NOT NULL,
  `PID` int(11) NOT NULL,
  `QUANTITY` int(11) NOT NULL,
  `UNIT_PRICE` decimal(10,2) NOT NULL,
  `SUBTOTAL` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`ORDER_ITEM_ID`, `ORDER_ID`, `PID`, `QUANTITY`, `UNIT_PRICE`, `SUBTOTAL`) VALUES
(1, 1, 85, 1, '950.00', '950.00'),
(2, 2, 60, 1, '260.10', '260.10'),
(3, 3, 60, 1, '260.10', '260.10'),
(4, 3, 85, 1, '950.00', '950.00'),
(5, 3, 61, 1, '195.05', '195.05'),
(6, 4, 73, 1, '5750.50', '5750.50'),
(7, 5, 85, 1, '950.00', '950.00'),
(8, 6, 85, 1, '950.00', '950.00'),
(9, 7, 85, 1, '950.00', '950.00'),
(10, 8, 86, 1, '1120.50', '1120.50'),
(11, 9, 60, 1, '260.10', '260.10'),
(12, 10, 60, 1, '260.10', '260.10'),
(13, 11, 61, 1, '195.05', '195.05'),
(14, 12, 87, 1, '1599.99', '1599.99'),
(15, 13, 87, 1, '1599.99', '1599.99'),
(16, 14, 110, 1, '899.99', '899.99'),
(17, 15, 60, 1, '260.10', '260.10'),
(18, 16, 61, 1, '195.05', '195.05'),
(19, 17, 73, 1, '5750.50', '5750.50'),
(20, 18, 10, 1, '75.27', '75.27'),
(21, 19, 61, 1, '195.05', '195.05'),
(22, 19, 10, 1, '75.27', '75.27'),
(23, 19, 110, 1, '899.99', '899.99'),
(24, 19, 86, 3, '1120.50', '3361.50'),
(25, 19, 60, 1, '260.10', '260.10'),
(27, 21, 64, 2, '330.20', '660.40'),
(28, 21, 65, 2, '4005.15', '8010.30'),
(29, 21, 117, 2, '549.99', '1099.98');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `PID` int(11) NOT NULL,
  `PRODUCT_NAME` varchar(255) DEFAULT NULL,
  `PRODUCT_PRICE_REGULAR` decimal(10,2) DEFAULT NULL,
  `PRODUCT_PRICE_SALE` decimal(10,2) DEFAULT NULL,
  `PRODUCT_CATEGORY` varchar(255) DEFAULT NULL,
  `PRODUCT_SKU` varchar(255) DEFAULT NULL,
  `PRODUCT_STOCK_STATUS` varchar(255) DEFAULT NULL,
  `PRODUCT_SOLD_INDIVIDUALLY` tinyint(1) DEFAULT NULL,
  `PRODUCT_QUANTITY` int(11) DEFAULT NULL,
  `PRODUCT_BACKORDER` int(11) DEFAULT NULL,
  `PRODUCT_STOCK_ALERT` int(11) DEFAULT NULL,
  `PRODUCT_WEIGHT` decimal(10,2) DEFAULT NULL,
  `PRODUCT_DIMENSION_LENGTH` decimal(10,2) DEFAULT NULL,
  `PRODUCT_DIMENSION_WIDTH` decimal(10,2) DEFAULT NULL,
  `PRODUCT_DIMENSION_HEIGHT` decimal(10,2) DEFAULT NULL,
  `PRODUCT_UPSELLS` int(11) DEFAULT NULL,
  `PRODUCT_CROSS_SELLS` int(11) DEFAULT NULL,
  `PRODUCT_ATTRIBUTES` text DEFAULT NULL,
  `PRODUCT_IMAGE_PATH` text DEFAULT NULL,
  `PRODUCT_LIKES` int(11) DEFAULT 0,
  `PRODUCT_DESCRIPTION` text DEFAULT NULL,
  `PRODUCT_STATUS` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`PID`, `PRODUCT_NAME`, `PRODUCT_PRICE_REGULAR`, `PRODUCT_PRICE_SALE`, `PRODUCT_CATEGORY`, `PRODUCT_SKU`, `PRODUCT_STOCK_STATUS`, `PRODUCT_SOLD_INDIVIDUALLY`, `PRODUCT_QUANTITY`, `PRODUCT_BACKORDER`, `PRODUCT_STOCK_ALERT`, `PRODUCT_WEIGHT`, `PRODUCT_DIMENSION_LENGTH`, `PRODUCT_DIMENSION_WIDTH`, `PRODUCT_DIMENSION_HEIGHT`, `PRODUCT_UPSELLS`, `PRODUCT_CROSS_SELLS`, `PRODUCT_ATTRIBUTES`, `PRODUCT_IMAGE_PATH`, `PRODUCT_LIKES`, `PRODUCT_DESCRIPTION`, `PRODUCT_STATUS`) VALUES
(9, 'Hippo Pro V3', '1200.00', '1200.00', 'Phone', '123455', 'in_stock', 0, 100, 0, 10, '0.50', '1.00', '1.00', '1.00', 1, 1, '', 'images/default-image.jpg', 0, 'none', 'Inactive'),
(10, 'Aokko 3068B', '85.16', '75.27', 'Keyboard', 'KB-AOKKO-3068B', 'in_stock', 1, 79, 2, 10, '1.21', '33.30', '14.10', '3.90', 4, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Brown/Blue\r\nRGB: Yes', 'uploads/680849737aa23.jpg', 64, 'Aokko 3068B is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(11, 'Arma SMK-12R', '72.35', '64.93', 'Keyboard', 'KB-ARMA-SMK-12R', 'in_stock', 1, 26, 1, 10, '0.88', '36.00', '13.90', '3.70', 2, 1, 'Keyboard type: Mechanical Gaming Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hall-effect analog switches\r\nRGB: Yes', 'uploads/6808499026d74.jpg', 25, 'Arma SMK-12R is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(12, 'Asos RGG Strix Scope RX', '57.06', '46.78', 'Keyboard', 'KB-ASOS-RGG-STRIX-SCOPE', 'in_stock', 0, 61, 10, 7, '0.94', '35.50', '11.80', '3.20', 3, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808aeba2dbb7.jpg', 98, 'Asos RGG Strix Scope RX is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(13, 'Aunty Pro 2', '99.05', '69.69', 'Keyboard', 'KB-AUNTY-PRO-2', 'in_stock', 0, 93, 8, 4, '0.88', '34.40', '12.90', '4.50', 1, 1, 'Keyboard type: Mechanical Gaming Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hall-effect analog switches\r\nRGB: Yes', 'uploads/6808aed1ce09c.jpg', 7, 'Aunty Pro 2 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(14, 'Canler Monster CK552', '149.45', '104.65', 'Keyboard', 'KB-CANLER-MONSTER-CK552', 'in_stock', 1, 55, 3, 3, '0.95', '33.50', '13.00', '4.10', 1, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Brown/Blue\r\nRGB: Yes', 'uploads/6808aedd04d7a.jpg', 37, 'Canler Monster CK552 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(15, 'Cornsair K70 Pro Mini', '95.64', '84.95', 'Keyboard', 'KB-CORNSAIR-K70-PRO-MIN', 'in_stock', 0, 81, 8, 6, '1.30', '30.50', '10.80', '4.90', 2, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless\r\nAvailable color: Black\r\nSwitch type: GL Tactile/Clicky/Linear\r\nRGB: Yes', 'uploads/680849e0b5167.jpg', 34, 'Cornsair K70 Pro Mini is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(16, 'Cornsair K70 RGB Pro', '90.77', '77.06', 'Keyboard', 'KB-CORNSAIR-K70-RGB-PRO', 'in_stock', 1, 54, 5, 4, '0.95', '30.90', '12.50', '4.60', 4, 3, 'Keyboard type: Membrane Keyboard\r\nConnection type: Wired\r\nAvailable color: Black\r\nSwitch type: Membrane\r\nRGB: Yes', 'uploads/6808aef3034b1.jpg', 21, 'Cornsair K70 RGB Pro is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(17, 'Crazy BlackWidow V4', '128.25', '103.62', 'Keyboard', 'KB-CRAZY-BLACKWIDOW-V4', 'in_stock', 1, 71, 10, 9, '0.96', '37.90', '11.80', '4.40', 4, 3, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808af0765d28.jpg', 9, 'Crazy BlackWidow V4 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(18, 'Crazy Huntsman Mini', '136.59', '120.42', 'Keyboard', 'KB-CRAZY-HUNTSMAN-MINI', 'in_stock', 1, 97, 0, 9, '1.28', '38.10', '10.20', '3.20', 3, 3, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808af1026c0e.jpg', 32, 'Crazy Huntsman Mini is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(19, 'Donky One 3 Mini', '84.07', '65.65', 'Keyboard', 'KB-DONKY-ONE-3-MINI', 'in_stock', 0, 54, 0, 5, '0.91', '39.60', '11.20', '5.00', 3, 3, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hot-swappable switch slots (various options available)\r\nRGB: Yes', 'uploads/6808af1a39cb5.jpg', 64, 'Donky One 3 Mini is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(20, 'Donut NJ98', '145.42', '120.29', 'Keyboard', 'KB-DONUT-NJ98', 'in_stock', 0, 89, 3, 3, '0.89', '38.10', '11.20', '4.60', 4, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Brown/Blue\r\nRGB: Yes', 'uploads/6808af2937faa.jpg', 85, 'Donut NJ98 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(21, 'Epenmaker Shadow-X', '55.93', '52.45', 'Keyboard', 'KB-EPENMAKER-SHADOW-X', 'in_stock', 1, 3, 2, 7, '0.77', '30.90', '13.60', '4.80', 1, 5, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black\r\nSwitch type: Razer Green/Yellow\r\nRGB: Yes', 'uploads/6808af310c2cf.jpg', 77, 'Epenmaker Shadow-X is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(22, 'Glaous GMMK Pro', '145.33', '125.82', 'Keyboard', 'KB-GLAOUS-GMMK-PRO', 'in_stock', 0, 85, 3, 9, '0.80', '37.60', '14.60', '3.70', 1, 5, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless\r\nAvailable color: Black\r\nSwitch type: GL Tactile/Clicky/Linear\r\nRGB: Yes', 'uploads/6808af3b42a56.jpg', 10, 'Glaous GMMK Pro is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(23, 'HyterD Alley Origins', '56.82', '53.46', 'Keyboard', 'KB-HYTERD-ALLEY-ORIGINS', 'in_stock', 0, 90, 6, 9, '0.91', '34.60', '11.30', '3.50', 5, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless\r\nAvailable color: Black\r\nSwitch type: GL Tactile/Clicky/Linear\r\nRGB: Yes', 'uploads/6808af4718195.jpg', 33, 'HyterD Alley Origins is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(24, 'Kaychan C1', '127.77', '97.63', 'Keyboard', 'KB-KAYCHAN-C1', 'in_stock', 1, 22, 6, 5, '1.26', '33.60', '12.80', '3.30', 2, 5, 'Keyboard type: Membrane Gaming Keyboard\r\nConnection type: Wired\r\nAvailable color: Black\r\nSwitch type: Mech-Dome\r\nRGB: Yes', 'uploads/6808af4e3b221.jpg', 56, 'Kaychan C1 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(25, 'Kaychan K13 Pro', '74.20', '59.91', 'Keyboard', 'KB-KAYCHAN-K13-PRO', 'in_stock', 0, 70, 2, 0, '1.20', '32.20', '10.80', '4.20', 2, 2, 'Keyboard type: Mechanical Gaming Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hall-effect analog switches\r\nRGB: Yes', 'uploads/6808af5f76153.jpg', 9, 'Kaychan K13 Pro is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(26, 'Kaychan K6', '135.48', '97.70', 'Keyboard', 'KB-KAYCHAN-K6', 'in_stock', 0, 21, 0, 7, '1.23', '34.40', '13.00', '3.30', 5, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808af65bddde.jpg', 9, 'Kaychan K6 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(27, 'Lemonkey P1 HE', '94.41', '84.57', 'Keyboard', 'KB-LEMONKEY-P1-HE', 'in_stock', 0, 8, 6, 3, '1.50', '30.60', '13.20', '3.80', 3, 5, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hot-swappable switch slots (various options available)\r\nRGB: Yes', 'uploads/6808af6d64c49.jpg', 97, 'Lemonkey P1 HE is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(28, 'Logic G213 Pro', '65.79', '46.13', 'Keyboard', 'KB-LOGIC-G213-PRO', 'in_stock', 0, 10, 1, 7, '1.17', '36.60', '13.50', '4.60', 2, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Brown/Blue\r\nRGB: Yes', 'uploads/6808af76c981b.jpg', 46, 'Logic G213 Pro is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(29, 'Logic G915 TKL', '66.48', '53.25', 'Keyboard', 'KB-LOGIC-G915-TKL', 'in_stock', 1, 13, 4, 5, '0.76', '39.20', '10.70', '3.70', 3, 3, 'Keyboard type: Membrane Keyboard\r\nConnection type: Wired\r\nAvailable color: Black\r\nSwitch type: Membrane\r\nRGB: Yes', 'uploads/6808af860674a.jpg', 52, 'Logic G915 TKL is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(30, 'Rayol Kluzt RK61', '80.76', '58.32', 'Keyboard', 'KB-RAYOL-KLUZT-RK61', 'in_stock', 0, 68, 3, 7, '1.24', '38.40', '10.60', '3.10', 4, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Brown/Blue\r\nRGB: Yes', 'uploads/6808af8f7bc28.jpg', 58, 'Rayol Kluzt RK61 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(31, 'Robotcat Vulcan 120 AIMO', '77.75', '54.44', 'Keyboard', 'KB-ROBOTCAT-VULCAN-120-', 'in_stock', 0, 68, 2, 8, '1.38', '31.70', '13.60', '4.50', 2, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless (Bluetooth/2.4GHz) and Wired\r\nAvailable color: Black\r\nSwitch type: Hot-swappable switch slots (various options available)\r\nRGB: Yes', 'uploads/6808af9840130.jpg', 1, 'Robotcat Vulcan 120 AIMO is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(32, 'Rodragan K552 Kumara', '75.40', '56.82', 'Keyboard', 'KB-RODRAGAN-K552-KUMARA', 'in_stock', 0, 89, 2, 4, '1.45', '37.30', '12.20', '4.80', 1, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808afa07af9e.jpg', 87, 'Rodragan K552 Kumara is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(33, 'SteelPower Aped Pro', '107.96', '87.40', 'Keyboard', 'KB-STEELPOWER-APED-PRO', 'in_stock', 1, 1, 1, 3, '1.23', '34.50', '11.00', '3.80', 3, 2, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808afa7ca66e.jpg', 7, 'SteelPower Aped Pro is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(34, 'TFF Gaming K1', '126.41', '107.37', 'Keyboard', 'KB-TFF-GAMING-K1', 'in_stock', 1, 49, 10, 5, '0.98', '31.40', '12.30', '4.60', 1, 4, 'Keyboard type: Mechanical Keyboard\r\nConnection type: Wireless/Wired\r\nAvailable color: Black/White\r\nSwitch type: Gateron Red/Blue/Brown\r\nRGB: Yes', 'uploads/6808afadb3318.jpg', 66, 'TFF Gaming K1 is a high-quality keyboard suitable for gaming and productivity.', 'Active'),
(35, 'Arma Starship 3', '51.68', '75.05', 'Mouse', 'MS-85986', 'in_stock', 1, 64, 11, 3, '0.29', '10.97', '9.52', '3.32', 2, 1, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afb7e96c7.jpg', 636, 'Indeed field church. Lot similar represent money crime yourself. Away technology level if hope west.', 'Active'),
(36, 'Asos ROG Chakram', '44.58', '50.40', 'Mouse', 'MS-53473', 'in_stock', 1, 76, 14, 5, '0.07', '8.71', '9.77', '4.28', 1, 5, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afbe79fbb.jpg', 22, 'Federal party dream order order.', 'Active'),
(37, 'BenA Zowie S2', '56.72', '41.56', 'Mouse', 'MS-84703', 'in_stock', 0, 61, 2, 7, '0.12', '10.50', '7.23', '4.26', 2, 5, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afc5ddfd6.jpg', 277, 'New ok along step low million military. Require response discussion. Inside hand significant. Draw book agent already.', 'Active'),
(38, 'Coolmaster MM710', '68.56', '58.73', 'Mouse', 'MS-25335', 'in_stock', 1, 77, 13, 5, '0.05', '8.10', '5.72', '3.61', 3, 4, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afcd4ec87.jpg', 433, 'Use professional they democratic will rule. He site degree without party.', 'Active'),
(39, 'Cornsair Dark Core RGB Pro SE', '33.51', '65.89', 'Mouse', 'MS-18733', 'in_stock', 1, 37, 14, 7, '0.08', '13.06', '8.26', '4.22', 2, 2, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afd664533.jpg', 246, 'Culture very foreign ready election. Building avoid realize.', 'Active'),
(40, 'Cornsair M65 RGB Ultra', '139.68', '80.45', 'Mouse', 'MS-15805', 'in_stock', 0, 60, 20, 3, '0.17', '8.19', '5.54', '3.62', 2, 2, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afdc876f9.jpg', 546, 'End air north. Local point the college fire. Choose economic staff office eye year.', 'Active'),
(41, 'Crazy basilisk V3 Pro', '139.64', '30.87', 'Mouse', 'MS-89713', 'in_stock', 1, 44, 2, 4, '0.29', '13.96', '7.03', '5.99', 5, 0, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afe2e8fe0.jpg', 559, 'Enjoy energy magazine. Last race himself. Hot ever us instead together land might young. Other all organization fund brother option.', 'Active'),
(42, 'Crazy DeathAdder V2', '74.36', '70.08', 'Mouse', 'MS-00353', 'in_stock', 1, 47, 11, 2, '0.25', '9.40', '5.60', '3.73', 3, 5, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808afeeef18c.jpg', 328, 'Financial mind election. Kind onto artist adult. Away drug get fly majority capital. Home citizen fill away challenge fish.', 'Active'),
(43, 'Crazy Naga X', '127.74', '69.64', 'Mouse', 'MS-70445', 'in_stock', 0, 67, 7, 6, '0.11', '12.95', '6.84', '3.06', 3, 1, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808aff655c4a.jpg', 371, 'Tree base fly project. Main region experience question culture. Hot receive certainly member simple occur scene.', 'Active'),
(44, 'Crazy Viper Ultimate', '140.44', '91.97', 'Mouse', 'MS-80030', 'in_stock', 0, 95, 16, 6, '0.19', '14.90', '5.88', '3.11', 2, 3, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808affd686ae.jpg', 995, 'Man relate father imagine deal news medical summer. Step the people garden share small reality.', 'Active'),
(45, 'HypterD Pulsefire Haste', '26.45', '36.14', 'Mouse', 'MS-82968', 'in_stock', 1, 23, 5, 2, '0.24', '8.56', '8.60', '3.34', 4, 2, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b00b92de6.jpg', 951, 'Live reality decade foot large price. Movie eye recently lawyer who stop position. Part individual star.', 'Active'),
(46, 'Kaychan M1 Ultra-Light Optical Mouse', '33.99', '79.30', 'Mouse', 'MS-52303', 'in_stock', 0, 39, 6, 9, '0.25', '13.13', '7.58', '5.12', 4, 2, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b02c46dc6.jpg', 777, 'Tell free manager hospital. Somebody fine during. Per respond produce tell beautiful.', 'Active'),
(47, 'Logic ERGO M575S', '47.47', '80.65', 'Mouse', 'MS-42204', 'in_stock', 0, 65, 5, 5, '0.13', '12.38', '5.52', '3.42', 1, 2, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b0349ae3b.jpg', 750, 'Health less much thousand. Charge reason after campaign buy site. Wonder bed spend energy feel beautiful north.', 'Active'),
(48, 'Logic G Pro X Superlight', '122.79', '57.94', 'Mouse', 'MS-53115', 'in_stock', 0, 97, 19, 1, '0.23', '8.33', '8.26', '5.64', 1, 0, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b03d0de59.jpg', 655, 'Finish matter onto PM nation hand explain become. Speak memory senior by. Lose design term visit.', 'Active'),
(49, 'Logic G502 Lightspeed', '123.82', '24.13', 'Mouse', 'MS-66018', 'in_stock', 0, 20, 1, 9, '0.15', '13.76', '9.47', '4.85', 5, 5, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b045643d2.jpg', 588, 'Simply free order above nature. Gun hotel blue blood here catch affect.', 'Active'),
(50, 'Logic MX Master 3S', '30.48', '67.46', 'Mouse', 'MS-83872', 'in_stock', 1, 18, 1, 5, '0.21', '10.81', '6.82', '5.61', 2, 0, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b061de9e1.jpg', 161, 'National no American against thought. Speech with school camera information son require wife.', 'Active'),
(51, 'MSSIS Clutch GM41 Lightweight', '143.03', '28.38', 'Mouse', 'MS-32476', 'in_stock', 0, 41, 8, 3, '0.28', '12.41', '5.01', '3.87', 3, 1, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b06891d2b.jpg', 420, 'Some buy itself half them occur seat. Item coach for forget political approach arm. Another check affect fly participant.', 'Active'),
(52, 'Radragan M602 Griffin', '63.47', '79.51', 'Mouse', 'MS-98087', 'in_stock', 0, 19, 15, 5, '0.10', '11.54', '7.32', '4.67', 1, 3, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b0725edf2.jpg', 460, 'Significant employee view win walk finish company. Admit son color ability executive account break.', 'Active'),
(53, 'Rocket Kone Pro Air', '103.39', '38.54', 'Mouse', 'MS-59919', 'in_stock', 0, 74, 19, 8, '0.24', '14.92', '6.74', '4.84', 2, 0, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b07b5a6d3.jpg', 818, 'Operation unit after situation road enter story.', 'Active'),
(54, 'SteelPower Aerox 3', '137.55', '30.57', 'Mouse', 'MS-07564', 'in_stock', 0, 75, 16, 7, '0.09', '14.13', '8.42', '5.04', 2, 4, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b0832a299.jpg', 19, 'Interview science mind another. Get join protect notice. Before so media arm avoid.', 'Active'),
(55, 'SteelPower Aerox 9 Wireless', '23.60', '85.24', 'Mouse', 'MS-37826', 'in_stock', 1, 17, 8, 6, '0.26', '9.27', '8.28', '4.79', 2, 3, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b089ec9d5.jpg', 416, 'Political business middle box body and bring. Serious notice wall cut. Teacher everybody financial painting.', 'Active'),
(56, 'TFF Gaming Mouse M5', '110.87', '85.48', 'Mouse', 'MS-81877', 'in_stock', 0, 58, 20, 3, '0.09', '13.19', '5.33', '3.67', 2, 5, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b08fd830d.jpg', 423, 'Theory window sometimes usually. Choose list rich family reason heavy inside create. Church event might cause after.', 'Active'),
(57, 'Zowik EC2-B', '56.07', '48.37', 'Mouse', 'MS-94470', 'in_stock', 0, 54, 20, 1, '0.22', '12.16', '6.84', '4.36', 3, 3, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Black\r\nRGB: Yes\r\nVersion: V3 Pro', 'uploads/6808b09504146.jpg', 941, 'Successful different bank heart. Music art vote two line. Type guy lead must.', 'Active'),
(58, 'Glorious Model O Wireless', '10.56', '20.37', 'Mouse', 'MS-98807', 'in_stock', 0, 54, 20, 1, '0.22', '12.16', '6.84', '4.36', 3, 3, 'color, black|white|yellow\r\nram, 4GB|8GB|16GB', 'uploads/6808b09f61d37.jpg', 941, 'Successful different bank heart. Music art vote two line. Type guy lead must.', 'Active'),
(59, 'FinalCountDown Starlight-12', '10.50', '20.50', 'Mouse', 'MS-95507', 'in_stock', 0, 54, 20, 1, '0.22', '12.16', '6.84', '4.36', 3, 3, 'Mouse type: Gaming Mouse\r\nConnection type: Wireless\r\nAvailable color: Various\r\nRGB: No\r\nVersion: Starlight-12', 'uploads/6808b0a705f00.jpg', 941, 'Successful different bank heart. Music art vote two line. Type guy lead must.', 'Active'),
(60, 'acor ed270r s3', '275.50', '260.10', 'Monitor', 'MON-10583', 'in_stock', 0, 41, 2, 5, '4.88', '61.53', '19.88', '45.21', 3, 5, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 27 inches', 'uploads/6808b0af49bd0.jpg', 231, 'Analysis maintain official Democrat. Effect detail identify author.', 'Active'),
(61, 'Acor Nitro VG240Y', '182.33', '195.05', 'Monitor', 'MON-73920', 'in_stock', 0, 55, 0, 8, '3.75', '54.12', '21.40', '41.95', 1, 2, 'Monitor type: Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 75Hz\r\nSize: 24 inches', 'uploads/6808b0bbebb8a.jpg', 200, 'Attack player likely page beautiful. Total establish however common.', 'Active'),
(62, 'Alien AW2725Q', '910.00', '885.50', 'Monitor', 'MON-48275', 'in_stock', 0, 25, 1, 4, '6.15', '60.77', '24.51', '52.83', 7, 4, 'Monitor type: Gaming Monitor (QD-OLED)\r\nConnection type: HDMI 2.1, DisplayPort 1.4, USB Hub\r\nAvailable color: Black\r\nRefresh rate: 240Hz\r\nSize: 27 inches', 'uploads/6808b0c6887e7.jpg', 788, 'Result message project fight newspaper. Moment difficult stage court discussion.', 'Active'),
(63, 'Alien AW3423DW', '1180.75', '1215.99', 'Monitor', 'MON-63041', 'in_stock', 0, 19, 0, 3, '7.82', '81.94', '30.73', '52.19', 5, 8, 'Monitor type: OLED Ultrawide Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 175Hz\r\nSize: 34 inches', 'uploads/6808b0ce28dc9.jpg', 976, 'Whose town prevent issue. Policy see blood.', 'Active'),
(64, 'AMC CQ32G3SE', '315.60', '330.20', 'Monitor', 'MON-95112', 'in_stock', 0, 44, 3, 6, '6.95', '71.88', '24.72', '52.65', 2, 6, 'Monitor type: Curved Gaming Monitor (VA)\r\nConnection type: HDMI 2.0, DisplayPort 1.2\r\nAvailable color: Dark Gray\r\nRefresh rate: 165Hz\r\nSize: 32 inches', 'uploads/6808b0d702b8d.jpg', 335, 'Firm specific task include difference church. Response possible property.', 'Active'),
(65, 'Asos ProArtist PA32UCX', '3980.25', '4005.15', 'Monitor', 'MON-78459', 'in_stock', 0, 12, 0, 2, '11.88', '72.91', '24.33', '60.57', 9, 10, 'Monitor type: Professional 4K Monitor\r\nConnection type: HDMI, DisplayPort, USB-C\r\nAvailable color: Black\r\nRefresh rate: 60Hz\r\nSize: 32 inches', 'uploads/6808b0df78585.jpg', 702, 'Across general structure peace. Near purpose effort modern.', 'Active'),
(66, 'Bell S3222DGM', '340.10', '355.80', 'Monitor', 'MON-24731', 'in_stock', 0, 36, 1, 5, '7.61', '70.75', '23.92', '55.81', 4, 3, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 32 inches', 'uploads/6808b0e753603.jpg', 446, 'Real character strategy growth system. Performance human ability increase.', 'Active'),
(67, 'Bell S3222HG', '345.99', '330.00', 'Monitor', 'MON-59104', 'in_stock', 0, 40, 2, 6, '7.15', '70.63', '23.50', '55.77', 6, 1, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 32 inches', 'uploads/6808b0ee8101f.jpg', 418, 'Industry current might region rock. Hospital direction various.', 'Active'),
(68, 'BenA EX3501R', '730.88', '755.50', 'Monitor', 'MON-11286', 'in_stock', 0, 27, 0, 4, '10.77', '83.71', '22.68', '53.05', 8, 9, 'Monitor type: Ultra-Wide Curved Monitor\r\nConnection type: HDMI, DisplayPort, USB-C\r\nAvailable color: Silver\r\nRefresh rate: 100Hz\r\nSize: 35 inches', 'uploads/6808b0f552a96.jpg', 533, 'Report involve specific machine system. Example perhaps candidate.', 'Active'),
(69, 'Gigaman G32QC A', '377.15', '360.90', 'Monitor', 'MON-68590', 'in_stock', 0, 33, 2, 5, '7.95', '71.33', '23.61', '55.07', 3, 7, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 32 inches', 'uploads/6808b10209516.jpg', 474, 'Support structure economic director. Care main north.', 'Active'),
(70, 'Gigaman M27Q X', '485.20', '505.00', 'Monitor', 'MON-30742', 'in_stock', 0, 30, 1, 5, '7.22', '61.89', '20.55', '53.73', 5, 4, 'Monitor type: Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 240Hz\r\nSize: 27 inches', 'uploads/6808b10826b27.jpg', 611, 'Evidence agreement probably participant. Language process role.', 'Active'),
(71, 'HAP Omen Transcend 32', '1185.66', '1205.33', 'Monitor', 'MON-55819', 'in_stock', 0, 17, 0, 3, '8.45', '71.65', '25.30', '58.42', 10, 6, 'Monitor type: Gaming Monitor (QD-OLED)\r\nConnection type: HDMI 2.1, DisplayPort 1.4, USB-C\r\nAvailable color: Black\r\nRefresh rate: 240Hz\r\nSize: 32 inches', 'uploads/6808b10f9353e.jpg', 837, 'Order production state. Responsibility situation certainly.', 'Active'),
(72, 'HAP X34', '880.40', '865.10', 'Monitor', 'MON-91357', 'in_stock', 0, 22, 1, 4, '10.13', '81.52', '30.68', '57.91', 7, 8, 'Monitor type: Ultrawide Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 34 inches', 'uploads/6808b11724118.jpg', 645, 'Party security usually. Collection maybe data.', 'Active'),
(73, 'Hezo ColorEdge CG319X', '5700.00', '5750.50', 'Monitor', 'MON-42066', 'in_stock', 0, 9, 0, 2, '12.44', '73.88', '24.91', '56.32', 1, 9, 'Monitor type: Professional 4K Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 60Hz\r\nSize: 31 inches', 'uploads/6808b11e154c8.jpg', 733, 'Success national final material. Change energy week program.', 'Active'),
(74, 'LAG UltraGear 27GR93U', '530.77', '515.22', 'Monitor', 'MON-88143', 'in_stock', 0, 35, 2, 5, '6.51', '61.70', '27.05', '57.44', 6, 2, 'Monitor type: Gaming Monitor (IPS)\r\nConnection type: HDMI 2.1, DisplayPort 1.4, USB Hub\r\nAvailable color: Black\r\nRefresh rate: 144Hz\r\nSize: 27 inches', 'uploads/6808b1272127e.jpg', 589, 'Technology recently anything. Meeting region magazine.', 'Active'),
(75, 'LAG UltraGear 32GP850-B', '435.10', '445.90', 'Monitor', 'MON-29974', 'in_stock', 0, 31, 0, 5, '7.49', '71.66', '29.01', '60.77', 4, 5, 'Monitor type: Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 32 inches', 'uploads/6808b12da2127.jpg', 552, 'Upon require future seem. Treatment surface option.', 'Active'),
(76, 'Lemonno G34w-10', '490.00', '470.30', 'Monitor', 'MON-75301', 'in_stock', 0, 28, 1, 4, '8.33', '80.61', '23.99', '46.55', 8, 3, 'Monitor type: Ultrawide Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 144Hz\r\nSize: 34 inches', 'uploads/6808b1355da9e.jpg', 497, 'Throughout different available. Together generation significant.', 'Active'),
(77, 'MSSIS Magazine 27CQ6PF QHD 180Hz Gaming Monitor', '288.44', '298.11', 'Monitor', 'MON-31698', 'in_stock', 0, 48, 3, 7, '5.99', '61.35', '22.61', '45.57', 2, 7, 'Monitor type: Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 180Hz\r\nSize: 27 inches', 'uploads/6808b13ed76c8.jpg', 377, 'Million section method improve wonder. Society general amount.', 'Active'),
(78, 'MSSIS MPG 321URX QD-OLED', '1075.80', '1060.25', 'Monitor', 'MON-67215', 'in_stock', 0, 18, 0, 3, '9.37', '71.59', '24.44', '48.71', 9, 1, 'Monitor type: Gaming Monitor (QD-OLED)\r\nConnection type: HDMI 2.1, DisplayPort 1.4, USB-C, USB Hub\r\nAvailable color: Black\r\nRefresh rate: 240Hz\r\nSize: 31.5 inches', 'uploads/6808b14a6e5cc.jpg', 903, 'Case difficult oil toward necessary. Music building relationship structure.', 'Active'),
(79, 'MSSIS Optix MAG342CQR', '410.50', '435.75', 'Monitor', 'MON-84023', 'in_stock', 0, 34, 2, 5, '7.18', '81.33', '27.41', '51.59', 4, 8, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 144Hz\r\nSize: 34 inches', 'uploads/6808b156bc793.jpg', 512, 'Including simple century range. Unit indicate against various.', 'Active'),
(80, 'Philipsm 346E2CUAE', '433.60', '425.30', 'Monitor', 'MON-53991', 'in_stock', 0, 29, 1, 5, '7.99', '80.52', '25.37', '49.88', 7, 2, 'Monitor type: Ultra-Wide Curved Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 100Hz\r\nSize: 34 inches', 'uploads/6808b161f2b02.jpg', 394, 'Top entire key bill. Detail prevent member.', 'Active'),
(81, 'PRISOM X270', '235.10', '245.80', 'Monitor', 'MON-16854', 'in_stock', 0, 60, 4, 8, '4.55', '61.92', '19.31', '45.76', 1, 5, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 165Hz\r\nSize: 27 inches', 'uploads/6808b16961b55.jpg', 306, 'During morning maybe. Figure important development.', 'Active'),
(82, 'Samsing Odyssey G7', '680.00', '665.50', 'Monitor', 'MON-82477', 'in_stock', 0, 26, 0, 4, '8.41', '71.29', '30.83', '59.77', 6, 9, 'Monitor type: Curved Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 240Hz\r\nSize: 32 inches', 'uploads/6808b1720185a.jpg', 805, 'Guess history region reflect garden. Major recently condition.', 'Active'),
(83, 'TFF Gaming VG28UQL1A', '788.20', '760.70', 'Monitor', 'MON-40192', 'in_stock', 0, 21, 1, 3, '7.03', '63.95', '23.33', '53.68', 10, 3, 'Monitor type: 4K Gaming Monitor\r\nConnection type: HDMI, DisplayPort\r\nAvailable color: Black\r\nRefresh rate: 144Hz\r\nSize: 28 inches', 'uploads/6808b179aa900.jpg', 632, 'Sure response relationship response difference. Force necessary store.', 'Active'),
(84, 'ViewSanic VA240A-H', '135.40', '128.99', 'Monitor', 'MON-97538', 'in_stock', 0, 66, 5, 10, '3.66', '54.91', '18.75', '41.62', 2, 4, 'Monitor type: Business Monitor\r\nConnection type: HDMI, VGA\r\nAvailable color: Black\r\nRefresh rate: 60Hz\r\nSize: 24 inches', 'uploads/6808b181a7c96.jpg', 112, 'Want possible process training. Security wall general.', 'Active'),
(85, 'Acor Nitro 5', '985.50', '950.00', 'Laptop', 'LAP-38104', 'in_stock', 0, 28, 2, 5, '2.45', '36.34', '25.50', '2.68', 4, 7, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.1\r\nAvailable color: Black, Red\r\nProcessor: AMD Ryzen 7 6800H\r\nResolution: 1920x1080 (Full HD)', 'uploads/6808b18d04206.jpg', 755, 'Method democratic relationship economic. Control national successful.', 'Active'),
(86, 'Acor Swift X 14', '1099.99', '1120.50', 'Laptop', 'LAP-67291', 'in_stock', 0, 35, 0, 6, '1.48', '32.28', '22.81', '1.79', 2, 5, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Gold, Blue\r\nProcessor: AMD Ryzen 7 5800U\r\nResolution: 2240x1400 (2.2K)', 'uploads/6808b19421340.jpg', 680, 'Knowledge short million building. Network edge material.', 'Active'),
(87, 'Asos RAG Zephyrus G14', '1649.00', '1599.99', 'Laptop', 'LAP-50338', 'in_stock', 0, 22, 1, 4, '1.72', '31.20', '22.70', '1.95', 8, 3, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: White, Gray\r\nProcessor: AMD Ryzen 9 6900HS\r\nResolution: 2560x1600 (WQXGA)', 'uploads/6808b19c0d084.jpg', 1450, 'Floor radio discussion necessary. Moment include source magazine.', 'Active'),
(88, 'Asos TFF Gaming F15', '1295.75', '1315.25', 'Laptop', 'LAP-19574', 'in_stock', 0, 31, 2, 5, '2.20', '35.90', '25.60', '2.45', 1, 6, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Black, Gray\r\nProcessor: Intel Core i7-12700H\r\nResolution: 1920x1080 (Full HD)', 'uploads/6808b1a5536a2.jpg', 810, 'Impact ability property official. Station create political.', 'Active'),
(89, 'Asos Vivola Pro 15 OLED', '1150.00', '1175.99', 'Laptop', 'LAP-84620', 'in_stock', 0, 29, 0, 4, '1.65', '35.68', '23.53', '1.99', 7, 2, 'Laptop type: Creator Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Silver, Blue\r\nProcessor: AMD Ryzen 7 5800H\r\nResolution: 2880x1620 (2.8K OLED)', 'uploads/6808b1af3faf1.jpg', 920, 'Strategy available character region. Amount establish condition.', 'Active'),
(90, 'Asos ZenGarden 14', '1399.50', '1420.00', 'Laptop', 'LAP-77103', 'in_stock', 0, 33, 1, 5, '1.39', '31.36', '22.06', '1.69', 5, 9, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Silver, Blue\r\nProcessor: Intel Core i7-1260P\r\nResolution: 2880x1800 (2.8K OLED)', 'uploads/6808b1b7d6c32.jpg', 1150, 'Sound analysis Mrs court system. Determine standard question.', 'Active'),
(91, 'Asos ZenGarden A14 UX3407', '1250.00', '1285.75', 'Laptop', 'LAP-24815', 'in_stock', 0, 38, 0, 6, '1.29', '31.24', '22.01', '1.59', 3, 8, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Aqua Celadon\r\nProcessor: Intel Core Ultra 5 125H\r\nResolution: 14-inch 2.8K OLED (2880 x 1800)', 'uploads/6808b1bfdee8b.jpg', 1080, 'Quickly recently player director. Culture difference.', 'Active'),
(92, 'Bell G15 5520', '1199.99', '1150.50', 'Laptop', 'LAP-93570', 'in_stock', 0, 26, 2, 4, '2.81', '35.73', '27.21', '2.69', 6, 1, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.1\r\nAvailable color: Dark Shadow Grey\r\nProcessor: Intel Core i7-12700H\r\nResolution: 1920x1080 (Full HD)', 'uploads/6808b1ca620b2.jpg', 715, 'Throughout structure seem role. Morning energy language.', 'Active'),
(93, 'Bell XPS 15', '2350.25', '2400.00', 'Laptop', 'LAP-41892', 'in_stock', 0, 18, 0, 3, '1.92', '34.44', '23.01', '1.85', 10, 4, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6, Bluetooth 5.1\r\nAvailable color: Silver, Black\r\nProcessor: Intel Core i7-13700H\r\nResolution: 3840x2400 (4K UHD)', 'uploads/6808b1d221cbf.jpg', 1850, 'Size piece meeting human significant. Goal issue indicate.', 'Active'),
(94, 'Crazy Blade 17', '2999.99', '3050.50', 'Laptop', 'LAP-75021', 'in_stock', 0, 15, 1, 3, '2.75', '39.50', '26.00', '1.99', 9, 5, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Black\r\nProcessor: Intel Core i9-12900H\r\nResolution: 2560x1440 (QHD)', 'uploads/6808b1d836492.jpg', 1950, 'Security statement ability. Community prevent necessary.', 'Active'),
(95, 'Gigaman Aero 16 OLED', '2650.00', '2599.00', 'Laptop', 'LAP-36488', 'in_stock', 0, 17, 0, 3, '2.30', '35.60', '24.85', '2.24', 8, 10, 'Laptop type: Creative Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Silver\r\nProcessor: Intel Core i9-12900HK\r\nResolution: 3840x2400 (4K UHD OLED)', 'uploads/6808b1df7fc83.jpg', 1700, 'Process option design model. Management difficult.', 'Active'),
(96, 'HAP EliteBook Ultra G1i', '1599.00', '1625.50', 'Laptop', 'LAP-99014', 'in_stock', 0, 25, 2, 4, '1.34', '31.56', '22.42', '1.47', 5, 7, 'Laptop type: Business Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Silver\r\nProcessor: Intel Core Ultra 7 155H\r\nResolution: 14-inch WUXGA (1920 x 1200)', 'uploads/6808b1e69ce3b.jpg', 1280, 'Across available administration various. Reflect figure.', 'Active'),
(97, 'HAP OSmen 16', '1750.80', '1700.20', 'Laptop', 'LAP-45237', 'in_stock', 0, 20, 1, 4, '2.35', '36.92', '26.11', '2.35', 9, 6, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Black\r\nProcessor: AMD Ryzen 9 6900HX\r\nResolution: 2560x1440 (QHD)', 'uploads/6808b1f0734e4.jpg', 1500, 'West effect specific require. Hospital final structure.', 'Active'),
(98, 'HAP Spectre x360 16', '1899.99', '1950.00', 'Laptop', 'LAP-12865', 'in_stock', 0, 19, 0, 3, '2.01', '35.80', '24.53', '1.99', 7, 1, 'Laptop type: Convertible Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Black, Blue\r\nProcessor: Intel Core i7-12700H\r\nResolution: 3072x1920 (3K OLED)', 'uploads/6808b1f9f40b0.jpg', 1650, 'Indeed surface growth meeting. Image field support.', 'Active'),
(99, 'HAP Victor Gaming 15-Fb3721AX', '849.50', '820.00', 'Laptop', 'LAP-57930', 'in_stock', 0, 42, 3, 7, '2.29', '35.79', '25.50', '2.35', 3, 5, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Mica Silver\r\nProcessor: AMD Ryzen 5 8645HS\r\nResolution: 15.6-inch Full HD (1920 x 1080) with 144Hz refresh rate', 'uploads/6808b202a9e26.jpg', 650, 'Church music building state. Letter technology.', 'Active'),
(100, 'Lemonno KeepPad X1 Carbon Gen 11', '1980.00', '2010.50', 'Laptop', 'LAP-60411', 'in_stock', 0, 24, 0, 4, '1.12', '31.56', '22.16', '1.49', 10, 2, 'Laptop type: Business Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Black\r\nProcessor: Intel Core i7-1365U\r\nResolution: 1920x1200 (WUXGA)', 'uploads/6808b20af1179.jpg', 1750, 'Realize main perhaps candidate. Answer include discussion.', 'Active'),
(101, 'Lemonno Legion 5 Pro', '1550.75', '1500.00', 'Laptop', 'LAP-22783', 'in_stock', 0, 27, 2, 5, '2.49', '35.60', '26.42', '2.68', 8, 7, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.2\r\nAvailable color: Gray\r\nProcessor: AMD Ryzen 7 6800H\r\nResolution: 2560x1600 (WQXGA)', 'uploads/6808b2138ceac.jpg', 1380, 'Training major likely production. Program section agreement.', 'Active'),
(102, 'Lemonno LOQ (Ryzen 7 8845HS + RTX 4050)', '1050.00', '1085.99', 'Laptop', 'LAP-71946', 'in_stock', 0, 39, 1, 6, '2.40', '35.96', '26.48', '2.52', 4, 9, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Storm Grey\r\nProcessor: AMD Ryzen 7 8845HS\r\nResolution: 15.6-inch Full HD (1920 x 1080) with 144Hz refresh rate', 'uploads/6808b21d7d07f.jpg', 790, 'Party student toward collection. Reason simple machine.', 'Active'),
(103, 'Mincesoft Deepsea Laptop 5 15', '1449.99', '1480.00', 'Laptop', 'LAP-58209', 'in_stock', 0, 30, 0, 5, '1.56', '34.00', '24.40', '1.47', 6, 3, 'Laptop type: Business Laptop\r\nConnection type: Wi-Fi 6, Bluetooth 5.1\r\nAvailable color: Platinum, Black\r\nProcessor: Intel Core i7-1265U\r\nResolution: 2496x1664 (PixelSense)', 'uploads/6808b22746054.jpg', 1100, 'Project standard necessary role. Responsibility stock.', 'Active'),
(104, 'MSSIS Eren GT77 HX', '3899.00', '3950.50', 'Laptop', 'LAP-14775', 'in_stock', 0, 12, 1, 3, '3.30', '39.70', '33.00', '2.30', 1, 8, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Black\r\nProcessor: Intel Core i9-13980HX\r\nResolution: 3840x2160 (4K Mini LED)', 'uploads/6808b230245e6.jpg', 2100, 'Evidence participant maybe beautiful. Amount response.', 'Active'),
(105, 'MSSIS Prestige 13 AI+ Evo', '1349.00', '1375.00', 'Laptop', 'LAP-90263', 'in_stock', 0, 26, 0, 4, '0.99', '29.90', '21.40', '1.69', 7, 4, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Pure White\r\nProcessor: Intel Core Ultra 7 155H\r\nResolution: 13.3-inch WQXGA+ (2880 x 1800)', 'uploads/6808b239186b1.jpg', 1220, 'Purpose various range member. Upon design development.', 'Active'),
(106, 'MSSIS Stealth 17 Studio', '2799.99', '2850.00', 'Laptop', 'LAP-48817', 'in_stock', 0, 16, 1, 3, '2.80', '39.61', '26.40', '2.01', 5, 10, 'Laptop type: Gaming Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Black\r\nProcessor: Intel Core i9-13900H\r\nResolution: 3840x2160 (4K UHD)', 'uploads/6808b2419ca96.jpg', 1880, 'Benefit during garden final. Official region difference.', 'Active'),
(107, 'Orange MacBook Air 15 M2', '1299.00', '1325.50', 'Laptop', 'LAP-73659', 'in_stock', 0, 45, 0, 7, '1.51', '34.04', '23.76', '1.15', 2, 6, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6, Bluetooth 5.3\r\nAvailable color: Silver, Space Gray, Midnight, Starlight\r\nProcessor: Apple M2\r\nResolution: 2880x1864 (Liquid Retina)', 'uploads/6808b24e1636d.jpg', 1550, 'Near certainly growth want. Language wall treatment.', 'Active'),
(108, 'Orange MacNCheeseBook Pro 16 M2 Max', '3499.00', '3550.00', 'Laptop', 'LAP-10942', 'in_stock', 0, 14, 0, 3, '2.16', '35.57', '24.81', '1.68', 1, 9, 'Laptop type: Professional Laptop\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Silver, Space Gray\r\nProcessor: Apple M2 Max\r\nResolution: 3456x2234 (Liquid Retina XDR)', 'uploads/6808b25798384.jpg', 2200, 'Century support industry human. Change political south.', 'Active'),
(109, 'Samsing Note Book 3 Ultra', '2199.99', '2250.75', 'Laptop', 'LAP-85210', 'in_stock', 0, 21, 1, 4, '1.79', '35.54', '25.04', '1.65', 9, 7, 'Laptop type: Ultrabook\r\nConnection type: Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Graphite\r\nProcessor: Intel Core i9-13900H\r\nResolution: 2880x1800 (Dynamic AMOLED 2X)', 'uploads/6808b25f0ea76.jpg', 1780, 'Future significant training program. Model detail.', 'Active'),
(110, 'Asos RGG Phone 8 Ultimate', '999.99', '899.99', 'Phone', 'PHONE-ASUS-RGG8ULT', 'in_stock', 1, 120, 15, 10, '0.24', '17.20', '7.60', '1.00', 3, 2, 'Phone brand: Asus\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Phantom Black\r\nChipset: Snapdragon 8 Gen 4\r\nResolution: 2448 × 1080', 'uploads/6808b2687f28c.jpg', 450, 'Experience ultimate mobile gaming with the Asos RGG Phone 8 Ultimate.', 'Active'),
(111, 'CAA Something Phone (2) by Nothing', '699.99', '649.99', 'Phone', 'PHONE-NOTHING-SP2', 'in_stock', 1, 150, 20, 12, '0.19', '16.00', '7.60', '0.80', 4, 3, 'Phone brand: Nothing\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Transparent Black, Transparent White\r\nChipset: Snapdragon 8+ Gen 3\r\nResolution: 2412 × 1080', 'uploads/6808b27451c6b.jpg', 380, 'The CAA Something Phone (2) by Nothing offers a unique design and clean software.', 'Active'),
(112, 'Gaagle Pixel 9 Pro', '899.99', '829.99', 'Phone', 'PHONE-GOOGLE-PIXEL9PRO', 'in_stock', 1, 180, 25, 15, '0.21', '16.20', '7.50', '0.90', 5, 4, 'Phone brand: Google\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Snow, Obsidian\r\nChipset: Google Tensor G4\r\nResolution: 3120 × 1440', 'uploads/6808b27cef63e.jpg', 510, 'Capture amazing photos with the Gaagle Pixel 9 Pro, powered by Google AI.', 'Active'),
(113, 'Henor Magic 6 Ultimate', '1099.99', '999.99', 'Phone', 'PHONE-HONOR-MAGIC6ULT', 'in_stock', 1, 90, 10, 8, '0.22', '16.40', '7.50', '0.90', 3, 2, 'Phone brand: Honor\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Black, Green\r\nChipset: Snapdragon 8 Gen 4\r\nResolution: 2848 × 1312', 'uploads/6808b286bafbb.jpg', 420, 'The Henor Magic 6 Ultimate offers premium performance and a stunning display.', 'Active'),
(114, 'Hotmi 14 Ultra', '1199.99', '1099.99', 'Phone', 'PHONE-XIAOMI-14ULTRA', 'in_stock', 1, 100, 15, 10, '0.23', '16.10', '7.50', '0.90', 4, 3, 'Phone brand: Xiaomi\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Black, White\r\nChipset: Snapdragon 8 Gen 4\r\nResolution: 3200 × 1440', 'uploads/6808b28f88fc0.jpg', 480, 'Experience cutting-edge technology with the Hotmi 14 Ultra.', 'Active'),
(115, 'Huogei P70 Pro', '949.99', '879.99', 'Phone', 'PHONE-HUAWEI-P70PRO', 'in_stock', 1, 130, 18, 11, '0.20', '16.30', '7.40', '0.80', 3, 2, 'Phone brand: Huawei\r\nConnection type: 5G, Wi-Fi 6\r\nAvailable color: Silver, Green\r\nChipset: Kirin 9010\r\nResolution: 2844 × 1260', 'uploads/6808b297c2891.jpg', 410, 'The Huogei P70 Pro boasts impressive camera capabilities and performance.', 'Active'),
(116, 'iAOO 12 Ultra', '1049.99', '969.99', 'Phone', 'PHONE-IQOO-12ULTRA', 'in_stock', 1, 110, 16, 9, '0.22', '16.50', '7.60', '0.90', 4, 3, 'Phone brand: iQOO\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Red, Black\r\nChipset: Snapdragon 8 Gen 3\r\nResolution: 2800 × 1260', 'uploads/6808b2a0e7704.jpg', 390, 'Get powerful performance with the iAOO 12 Ultra.', 'Active'),
(117, 'Matarola Edge 60 Fusion', '599.99', '549.99', 'Phone', 'PHONE-MOTOROLA-EDGE60FUS', 'in_stock', 1, 170, 22, 14, '0.18', '16.10', '7.30', '0.80', 3, 2, 'Phone brand: Motorola\r\nConnection type: 5G, Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Amazonite (faux-canvas finish), Blue (faux leather), Pink (faux leather)\r\nChipset: MediaTek Dimensity 7300 or 7400\r\nResolution: 6.67-inch OLED display', 'uploads/6808b2ac95477.jpg', 250, 'The Matarola Edge 60 Fusion offers a great balance of features and value.', 'Active'),
(118, 'Matarola Moto G Power', '249.99', '219.99', 'Phone', 'PHONE-MOTOROLA-MOTOGPOWER', 'in_stock', 1, 200, 25, 15, '0.20', '16.50', '7.60', '0.90', 2, 1, 'Phone brand: Motorola\r\nConnection type: 4G LTE, Wi-Fi 5\r\nAvailable color: Steel Blue, Silver\r\nChipset: MediaTek Helio G85\r\nResolution: 1600 × 720', 'uploads/6808b2b952908.jpg', 180, 'Stay powered up with the long-lasting battery of the Matarola Moto G Power.', 'Active'),
(119, 'Matarola Razr Plus 2025', '1399.99', '1299.99', 'Phone', 'PHONE-MOTOROLA-RAZRPLUS25', 'in_stock', 1, 80, 10, 7, '0.19', '17.00', '7.30', '0.70', 4, 3, 'Phone brand: Motorola\r\nConnection type: 5G, Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Pantone Scarab, Pantone Rio Red, Pantone Mountain Trail, Pantone Cabaret\r\nChipset: Snapdragon 8 Elite (expected)\r\nResolution: 6.9-inch LTPO AMOLED display', 'uploads/6808b2c3b7467.jpg', 350, 'Experience the future of foldable phones with the Matarola Razr Plus 2025.', 'Active'),
(120, 'Noka 3310 (2017)', '59.99', '49.99', 'Phone', 'PHONE-NOKIA-3310-2017', 'in_stock', 1, 300, 30, 20, '0.08', '11.50', '4.80', '1.40', 1, 1, 'Phone brand: Nokia\r\nConnection type: 2G, 3G\r\nAvailable color: Red, Yellow, Blue\r\nChipset: Feature Phone OS\r\nResolution: 240 × 320', 'uploads/6808b2ca6d1c8.jpg', 600, 'A modern take on a classic, the Noka 3310 (2017) offers simplicity and durability.', 'Active'),
(121, 'OneKPlus 12 Pro', '899.99', '829.99', 'Phone', 'PHONE-ONEPLUS-12PRO', 'in_stock', 1, 140, 18, 12, '0.21', '16.30', '7.40', '0.90', 4, 3, 'Phone brand: OnePlus\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Black, Green\r\nChipset: Snapdragon 8 Gen 3\r\nResolution: 3216 × 1440', 'uploads/6808b2d1492b0.jpg', 470, 'Experience flagship performance with the OneKPlus 12 Pro.', 'Active'),
(122, 'OneKPlus 12R', '599.99', '549.99', 'Phone', 'PHONE-ONEPLUS-12R', 'in_stock', 1, 160, 20, 13, '0.20', '16.30', '7.50', '0.80', 3, 2, 'Phone brand: OnePlus\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Cool Blue, Iron Gray\r\nChipset: Snapdragon 8 Gen 2\r\nResolution: 2772 × 1240', 'uploads/6808b2d8cc08b.jpg', 350, 'Get great value and performance with the OneKPlus 12R.', 'Active'),
(123, 'Oppa Find X7 Pro', '1099.99', '999.99', 'Phone', 'PHONE-OPPO-FINDX7PRO', 'in_stock', 1, 110, 15, 10, '0.22', '16.40', '7.40', '0.90', 4, 3, 'Phone brand: Oppo\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Black, Purple\r\nChipset: Snapdragon 8 Gen 4\r\nResolution: 3168 × 1440', 'uploads/6808b2e243cba.jpg', 430, 'The Oppa Find X7 Pro offers a premium camera experience and design.', 'Active'),
(124, 'Orange 16 Pro Max', '1199.99', '1099.99', 'Phone', 'PHONE-APPLE-16PROMAX', 'in_stock', 1, 200, 30, 15, '0.24', '16.00', '7.70', '0.80', 5, 4, 'Phone brand: Apple\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Silver, Gold, Graphite\r\nChipset: A18 Bionic\r\nResolution: 2796 × 1290', 'uploads/6808b2ecc9dce.jpg', 700, 'The Orange 16 Pro Max delivers powerful performance and an exceptional camera system.', 'Active'),
(125, 'OrangeMi Note 13 Pro+', '399.99', '369.99', 'Phone', 'PHONE-XIAOMI-REDMINOTE13PRO+', 'in_stock', 1, 180, 25, 15, '0.20', '16.20', '7.40', '0.80', 3, 2, 'Phone brand: Xiaomi\r\nConnection type: 5G, Wi-Fi 6\r\nAvailable color: Green, Blue\r\nChipset: MediaTek Dimensity 9200+\r\nResolution: 2712 × 1220', 'uploads/6808b2fbe39d0.jpg', 300, 'The OrangeMi Note 13 Pro+ offers impressive features for its price.', 'Active'),
(126, 'Paca F6 Pro', '499.99', '459.99', 'Phone', 'PHONE-POCO-F6PRO', 'in_stock', 1, 170, 20, 14, '0.21', '16.10', '7.50', '0.80', 3, 2, 'Phone brand: Poco\r\nConnection type: 5G, Wi-Fi 6\r\nAvailable color: Cyber Yellow, Black\r\nChipset: Snapdragon 8+ Gen 2\r\nResolution: 2712 × 1220', 'uploads/6808b30318025.jpg', 320, 'Get powerful performance for gaming with the Paca F6 Pro.', 'Active'),
(127, 'Roalme GT 6 Pro', '799.99', '739.99', 'Phone', 'PHONE-REALME-GT6PRO', 'in_stock', 1, 130, 18, 11, '0.20', '16.20', '7.50', '0.80', 4, 3, 'Phone brand: Realme\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Aurora Blue, Black\r\nChipset: Snapdragon 8 Gen 3\r\nResolution: 2772 × 1240', 'uploads/6808b309bd55a.jpg', 390, 'The Roalme GT 6 Pro offers fast charging and smooth performance.', 'Active'),
(128, 'Samsing Galaxy XCover 7 Pro', '449.99', '409.99', 'Phone', 'PHONE-SAMSUNG-XCOVER7PRO', 'in_stock', 1, 150, 20, 15, '0.23', '17.00', '8.10', '1.00', 2, 2, 'Phone brand: Samsung\r\nConnection type: 5G, Wi-Fi 6, Bluetooth 5.2\r\nAvailable color: Not specified\r\nChipset: Not specified\r\nResolution: 6.6-inch LCD display', 'uploads/6808b3109182b.jpg', 150, 'A rugged smartphone built for tough environments, the Samsing Galaxy XCover 7 Pro.', 'Active'),
(129, 'Samsing Galaxy Z Fold 6', '1799.99', '1699.99', 'Phone', 'PHONE-SAMSUNG-ZFOLD6', 'in_stock', 1, 70, 10, 7, '0.26', '15.50', '13.00', '0.60', 5, 4, 'Phone brand: Samsung\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Phantom Black, Cream\r\nChipset: Snapdragon 8 Gen 4\r\nResolution: 2176 × 1812 (Main)', 'uploads/6808b319480d8.jpg', 550, 'Experience multitasking like never before with the Samsing Galaxy Z Fold 6.', 'Active');
INSERT INTO `products` (`PID`, `PRODUCT_NAME`, `PRODUCT_PRICE_REGULAR`, `PRODUCT_PRICE_SALE`, `PRODUCT_CATEGORY`, `PRODUCT_SKU`, `PRODUCT_STOCK_STATUS`, `PRODUCT_SOLD_INDIVIDUALLY`, `PRODUCT_QUANTITY`, `PRODUCT_BACKORDER`, `PRODUCT_STOCK_ALERT`, `PRODUCT_WEIGHT`, `PRODUCT_DIMENSION_LENGTH`, `PRODUCT_DIMENSION_WIDTH`, `PRODUCT_DIMENSION_HEIGHT`, `PRODUCT_UPSELLS`, `PRODUCT_CROSS_SELLS`, `PRODUCT_ATTRIBUTES`, `PRODUCT_IMAGE_PATH`, `PRODUCT_LIKES`, `PRODUCT_DESCRIPTION`, `PRODUCT_STATUS`) VALUES
(130, 'Samsing S25 Ultra', '1299.99', '1199.99', 'Phone', 'PHONE-SAMSUNG-S25ULTRA', 'in_stock', 1, 150, 20, 15, '0.23', '16.30', '7.80', '0.90', 5, 4, 'Phone brand: Samsung\r\nConnection type: 5G, Wi-Fi 7\r\nAvailable color: Black, Green, Titanium\r\nChipset: Exynos 2500 / Snapdragon 8 Gen 4\r\nResolution: 3200 × 1440', 'uploads/6808b322de3a9.jpg', 650, 'Capture epic photos and videos with the Samsing S25 Ultra.', 'Active'),
(131, 'Sany Xpert 1 VI', '1099.99', '999.99', 'Phone', 'PHONE-SONY-XPERIA1VI', 'in_stock', 1, 100, 12, 9, '0.19', '16.50', '7.10', '0.80', 3, 2, 'Phone brand: Sony\r\nConnection type: 5G, Wi-Fi 6\r\nAvailable color: Black, Silver\r\nChipset: Snapdragon 8 Gen 3\r\nResolution: 3840 × 1644', 'uploads/6808b32997ab4.jpg', 400, 'Experience professional-grade photography and videography with the Sany Xpert 1 VI.', 'Active'),
(132, 'Sany Xpert 1 VII', '1199.99', '1099.99', 'Phone', 'PHONE-SONY-XPERIA1VII', 'in_stock', 1, 90, 10, 8, '0.19', '16.70', '7.10', '0.80', 4, 3, 'Phone brand: Sony\r\nConnection type: 5G, Wi-Fi 6E, Bluetooth 5.3\r\nAvailable color: Not specified\r\nChipset: Snapdragon 8 Elite (expected)\r\nResolution: 6.5-inch display', 'uploads/6808b332788a4.jpg', 410, 'The Sany Xpert 1 VII brings enhanced features for creators and enthusiasts.', 'Active'),
(133, 'Something Phone (3)', '799.99', '749.99', 'Phone', 'PHONE-NOTHING-SP3', 'in_stock', 1, 140, 18, 12, '0.20', '16.10', '7.60', '0.80', 4, 3, 'Phone brand: Nothing\r\nConnection type: 5G, Wi-Fi 6E\r\nAvailable color: Transparent Black, Transparent White\r\nChipset: Snapdragon 8+ Gen 3\r\nResolution: 2412 × 1080', 'uploads/6808b3394a8c8.jpg', 400, 'The Something Phone (3) continues Nothings unique design philosophy.', 'Active'),
(134, 'Viva X200 Pro', '849.99', '789.99', 'Phone', 'PHONE-VIVO-X200PRO', 'in_stock', 1, 120, 16, 10, '0.21', '16.40', '7.50', '0.90', 3, 2, 'Phone brand: Vivo\r\nConnection type: 5G, Wi-Fi 6\r\nAvailable color: Blue, Black\r\nChipset: MediaTek Dimensity 9400\r\nResolution: 2800 × 1260', 'uploads/6808b3421ad5e.jpg', 370, 'The Viva X200 Pro offers powerful performance and a great camera system.', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `product_likes`
--

CREATE TABLE `product_likes` (
  `LID` int(11) NOT NULL,
  `UID` int(11) DEFAULT NULL,
  `PID` int(11) NOT NULL,
  `TIMESTAMP` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `product_likes`
--

INSERT INTO `product_likes` (`LID`, `UID`, `PID`, `TIMESTAMP`) VALUES
(24, 25, 61, '2025-04-27 13:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `STATE_ID` int(11) NOT NULL,
  `STATE_NAME` varchar(255) NOT NULL,
  `COUNTRY_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`STATE_ID`, `STATE_NAME`, `COUNTRY_ID`) VALUES
(1, 'Johor', 1),
(2, 'Kedah', 1),
(3, 'Kelantan', 1),
(4, 'Melaka', 1),
(5, 'Negeri Sembilan', 1),
(6, 'Pahang', 1),
(7, 'Perak', 1),
(8, 'Perlis', 1),
(9, 'Pulau Pinang', 1),
(10, 'Sabah', 1),
(11, 'Sarawak', 1),
(12, 'Selangor', 1),
(13, 'Terengganu', 1),
(14, 'Kuala Lumpur', 1),
(15, 'Labuan', 1),
(16, 'Putrajaya', 1),
(17, 'Singapore', 2),
(18, 'Brunei-Muara', 3),
(19, 'Belait', 3),
(20, 'Tutong', 3),
(21, 'Temburong', 3),
(22, 'Bangkok', 4),
(23, 'Chiang Mai', 4),
(24, 'Phuket', 4),
(25, 'Chonburi', 4),
(26, 'Nakhon Ratchasima', 4),
(27, 'Khon Kaen', 4),
(28, 'Songkhla', 4),
(29, 'Ayutthaya', 4),
(30, 'Pattani', 4),
(31, 'Udon Thani', 4);

-- --------------------------------------------------------

--
-- Table structure for table `support_chats`
--

CREATE TABLE `support_chats` (
  `CHAT_ID` int(11) NOT NULL,
  `UID` int(11) NOT NULL,
  `AID` int(11) DEFAULT NULL,
  `STATUS` enum('Open','In Progress','Closed') DEFAULT 'Open',
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `support_chats`
--

INSERT INTO `support_chats` (`CHAT_ID`, `UID`, `AID`, `STATUS`, `CREATED_AT`, `UPDATED_AT`) VALUES
(5, 7, 2, 'Closed', '2025-04-27 11:23:47', '2025-04-27 12:46:02'),
(6, 25, 2, 'Closed', '2025-04-27 13:21:40', '2025-04-27 14:06:20'),
(7, 24, 2, 'Closed', '2025-04-27 14:07:25', '2025-04-27 14:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `MESSAGE_ID` int(11) NOT NULL,
  `CHAT_ID` int(11) NOT NULL,
  `SENDER` enum('User','Admin') NOT NULL,
  `SENDER_ID` int(11) NOT NULL,
  `MESSAGE` text NOT NULL,
  `TIMESTAMP` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`MESSAGE_ID`, `CHAT_ID`, `SENDER`, `SENDER_ID`, `MESSAGE`, `TIMESTAMP`) VALUES
(42, 5, 'User', 7, 'hi', '2025-04-27 11:23:47'),
(43, 5, 'User', 7, 'i need help', '2025-04-27 11:26:16'),
(44, 5, 'Admin', 2, 'how can i help', '2025-04-27 11:26:30'),
(45, 5, 'User', 7, 'good question', '2025-04-27 11:26:43'),
(46, 5, 'Admin', 2, 'then how am i suppose to help????', '2025-04-27 11:26:56'),
(47, 5, 'Admin', 2, 'oof', '2025-04-27 12:23:52'),
(48, 5, 'Admin', 2, 'oof', '2025-04-27 12:25:03'),
(49, 5, 'Admin', 2, 'oof', '2025-04-27 12:25:44'),
(50, 5, 'Admin', 2, 'oof', '2025-04-27 12:29:42'),
(51, 5, 'Admin', 2, 'test', '2025-04-27 12:29:48'),
(52, 5, 'Admin', 2, 'test', '2025-04-27 12:30:11'),
(53, 5, 'Admin', 2, 'test', '2025-04-27 12:30:11'),
(54, 5, 'Admin', 2, 'test', '2025-04-27 12:30:11'),
(55, 5, 'Admin', 2, 'test', '2025-04-27 12:30:11'),
(56, 5, 'Admin', 2, 'test', '2025-04-27 12:30:11'),
(57, 5, 'Admin', 2, 'asd', '2025-04-27 12:30:50'),
(58, 5, 'Admin', 2, 'asd', '2025-04-27 12:30:53'),
(59, 5, 'Admin', 2, 'whoa', '2025-04-27 12:34:10'),
(60, 5, 'Admin', 2, 'whoa', '2025-04-27 12:36:44'),
(61, 5, 'Admin', 2, 's', '2025-04-27 12:36:48'),
(62, 5, 'Admin', 2, 'hi', '2025-04-27 12:45:44'),
(63, 5, 'Admin', 2, 'wow', '2025-04-27 12:45:48'),
(64, 5, 'Admin', 2, 'that worked', '2025-04-27 12:45:53'),
(65, 5, 'Admin', 2, 'as', '2025-04-27 12:46:02'),
(66, 6, 'User', 25, 'hello', '2025-04-27 13:21:40'),
(67, 6, 'Admin', 2, 'hi', '2025-04-27 13:21:47'),
(68, 6, 'User', 25, 'i wan refund', '2025-04-27 13:21:54'),
(69, 6, 'Admin', 2, 'why tho', '2025-04-27 13:21:59'),
(70, 6, 'User', 25, 'item is broken', '2025-04-27 13:22:09'),
(71, 6, 'Admin', 2, 'oops, my bad', '2025-04-27 13:22:15'),
(72, 6, 'User', 25, 'give me voucher rm120000', '2025-04-27 13:22:25'),
(73, 6, 'Admin', 2, 'WELCOME20', '2025-04-27 13:22:29'),
(74, 7, 'User', 24, 'Hello, i would like a refund.', '2025-04-27 14:07:25'),
(75, 7, 'Admin', 2, 'hi', '2025-04-27 14:07:35'),
(76, 7, 'Admin', 2, 'How bout no', '2025-04-27 14:07:41'),
(77, 7, 'User', 24, 'Yes, you must refund me.', '2025-04-27 14:07:50'),
(78, 7, 'Admin', 2, 'No, Why should i', '2025-04-27 14:08:00'),
(79, 7, 'User', 24, 'Because the product SUCK ! : (', '2025-04-27 14:08:34'),
(80, 7, 'Admin', 2, 'Nah, not my problem', '2025-04-27 14:08:45');

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `TOKEN_ID` varchar(100) NOT NULL,
  `EXPIRE` datetime NOT NULL,
  `USER_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `token`
--

INSERT INTO `token` (`TOKEN_ID`, `EXPIRE`, `USER_ID`) VALUES
('5158083ab56431dd1d04f103b214f338440fff8d', '2025-04-27 21:46:30', 25);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UID` int(11) NOT NULL,
  `USERNAME` varchar(50) NOT NULL,
  `FIRSTNAME` varchar(255) DEFAULT NULL,
  `LASTNAME` varchar(255) DEFAULT NULL,
  `GENDER` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `BIRTHDATE` date DEFAULT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `CREATED` datetime DEFAULT current_timestamp(),
  `LAST_LOGGEDIN` datetime DEFAULT NULL,
  `VERIFIED` tinyint(1) DEFAULT 0,
  `REMEMBER_ME_TOKEN` varchar(255) DEFAULT NULL,
  `PROFILE_PICTURE` varchar(255) DEFAULT '../images/default-profile.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UID`, `USERNAME`, `FIRSTNAME`, `LASTNAME`, `GENDER`, `EMAIL`, `BIRTHDATE`, `PASSWORD`, `CREATED`, `LAST_LOGGEDIN`, `VERIFIED`, `REMEMBER_ME_TOKEN`, `PROFILE_PICTURE`) VALUES
(7, 'brianc69', 'Bernard', 'Choong', 'Male', 'bchoong1@gmail.com', '2002-04-19', '$2y$10$ntI7wUZE4WoZOWYiS87ZfenU8jk2nZZ453iBt0VwLJ/06bLZwxAhC', '2025-04-03 14:42:40', '2025-04-28 00:15:13', 1, NULL, '../uploads/680e4d2277cea.jpg'),
(8, 'user1', 'User', 'one', 'Male', 'user1@gmail.com', '2025-04-01', '$2y$10$aGuPUpRNwmfRYJoU73JVmOayaEHcC8/DOVKa4/AKxEelrA/NCK5pG', '2025-04-03 22:12:10', '2025-04-03 23:48:19', 1, NULL, '../images/default-profile.png'),
(12, 'user5', NULL, NULL, NULL, 'user5@gmail.com', NULL, '$2y$10$XjyXBLcd5NAJQc7N.Uq82.NH4YbNKEZ.ExRAUoCEEesFDSdMgDWjy', '2025-04-03 22:13:26', NULL, 1, NULL, '../images/default-profile.png'),
(13, 'user6', NULL, NULL, NULL, 'user6@gmail.com', NULL, '$2y$10$zQBQ2et3H8Ta6Lbni3zVk.U/EZsQK0Ox6l.mC7V7GDQls1X5HhZHu', '2025-04-03 22:13:49', NULL, 1, NULL, '../images/default-profile.png'),
(14, 'user7', NULL, NULL, NULL, 'user7@gmail.com', NULL, '$2y$10$SArXg8E9/cYxAOkOYZKaXuZhIoIBA7rFcEWgbZwSgXF3QN4nJ.QKK', '2025-04-03 22:14:21', NULL, 1, NULL, '../images/default-profile.png'),
(15, 'user8', NULL, NULL, NULL, 'user8@gmail.com', NULL, '$2y$10$/qrRqNrGjKbwp4bPf2EIxeskoHYfZPKBupGmIohVN/TbwenUvm4p2', '2025-04-03 22:14:33', NULL, 1, NULL, '../images/default-profile.png'),
(16, 'user9', NULL, NULL, NULL, 'user9@gmail.com', NULL, '$2y$10$DYyKo/ifaiH1.25YBjWZtebj3MFa3WGzUwQ7ZQjSR.cHyc8wbG/16', '2025-04-03 22:14:48', NULL, 1, NULL, '../images/default-profile.png'),
(18, 'User10', NULL, NULL, NULL, 'User10@gmail.com', NULL, '$2y$10$5ZkvdRb.SzBkShPxaqzJju1x.zIPI2D11eqdsow20AX4OUb10Dnx.', '2025-04-08 00:05:38', NULL, 0, NULL, '../images/default-profile.png'),
(19, 'abc', NULL, NULL, NULL, 'abc@gmail.com', NULL, '$2y$10$Geg.7oc5BmArJWBTRYdoqu3z533XNGwDe0YmZEf6HHwB5LtT.xxFK', '2025-04-09 14:24:48', '2025-04-09 14:24:57', 0, NULL, '../images/default-profile.png'),
(20, 'yhtan', NULL, NULL, NULL, 'yhtan-wm23@student.tarc.edu.my', NULL, '$2y$10$0I5L4inthCg0sIvMgvOxbePsVtU.gBUmq2PsmE.ZXtJEkvUdcsJKy', '2025-04-09 15:47:25', '2025-04-09 15:47:36', 0, NULL, '../images/default-profile.png'),
(21, 'Jon', 'John', 'athan', 'Male', 'jon@gmail.com', '2025-04-17', '$2y$10$Sn0MaI5xycjMt5RF9wryc.IPwIP77qoSyENkFkT/WxLSjOk8u88Y.', '2025-04-22 14:17:17', '2025-04-27 17:50:52', 0, 'effdd672a9faacaeab79addb3a759cfde53c09cfe8abef451686dcc7248bf949', '../images/default-profile.png'),
(22, 'James', NULL, NULL, NULL, 'james@gmail.com', NULL, '$2y$10$S22yMIdx21H499xHy2cJguO0EaNqC0IE1yH2R/nXrMqKlDYBhEqNG', '2025-04-23 17:51:25', '2025-04-23 17:51:30', 0, '49dc22526408dfce7a3aec091ea361f7cd68159d1b834d23cf20f710671b92dd', '../images/default-profile.png'),
(24, 'TCTARUMT', 'Ting', 'Choon', 'Male', 'natc-wm21@student.tarc.edu.my', '2009-01-27', '$2y$10$Znx/s4UIxRiOKMejAkLTyeq/sD4WRTRATQABDQz02ig0haUC1mQX2', '2025-04-27 21:11:02', '2025-04-27 22:07:15', 0, NULL, '../images/default-profile.png'),
(25, 'zixian', 'zi', 'xian', 'Male', 'christianlz-sm22@student.tarc.edu.my', '2004-12-26', '$2y$10$JKop6ow8LKy4OdXj9VKnU.vapJCtIK5YVUswmyuXxM7qVeeqqXXVy', '2025-04-27 21:21:25', '2025-04-27 21:48:28', 1, '68b49a857c5912bd4dbc069b7c25dcec29b6ced99c07221c642a0dddc8536ecb', '../uploads/680e30a0c05e8.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `VID` int(11) NOT NULL,
  `CODE` varchar(50) NOT NULL,
  `DISCOUNT_TYPE` enum('percent','fixed') NOT NULL,
  `DISCOUNT_VALUE` decimal(10,2) NOT NULL,
  `EXPIRY_DATE` date DEFAULT NULL,
  `USAGE_LIMIT` int(11) DEFAULT NULL,
  `USED_COUNT` int(11) DEFAULT 0,
  `STATUS` enum('active','inactive') DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`VID`, `CODE`, `DISCOUNT_TYPE`, `DISCOUNT_VALUE`, `EXPIRY_DATE`, `USAGE_LIMIT`, `USED_COUNT`, `STATUS`, `CREATED_AT`) VALUES
(3, 'WELCOME50', 'fixed', '50.00', '2025-05-10', 10, 1, 'active', '2025-04-22 05:37:16'),
(4, 'WELCOME100', 'fixed', '100.00', '2025-05-10', 10, 5, 'active', '2025-04-22 05:37:31'),
(5, 'WELCOME20', 'percent', '20.00', '2025-04-30', 10, 1, 'active', '2025-04-23 06:19:30'),
(6, 'FREE100', 'percent', '100.00', '2025-05-10', 10, 1, 'active', '2025-04-24 06:24:26'),
(7, 'FREE50', 'percent', '50.00', '2025-05-04', 10, 1, 'active', '2025-04-27 13:47:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`ADDID`),
  ADD KEY `STATE_ID` (`STATE_ID`),
  ADD KEY `COUNTRY_ID` (`COUNTRY_ID`),
  ADD KEY `UID` (`UID`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`AID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`BILL_ID`),
  ADD KEY `ORDER_ID` (`ORDER_ID`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `PID` (`PID`);

--
-- Indexes for table `contact_form`
--
ALTER TABLE `contact_form`
  ADD PRIMARY KEY (`CONTACT_ID`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`COUNTRY_ID`),
  ADD UNIQUE KEY `COUNTRY_NAME` (`COUNTRY_NAME`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ORDER_ID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `BILLING_ADDRESS_ID` (`BILLING_ADDRESS_ID`),
  ADD KEY `SHIPPING_ADDRESS_ID` (`SHIPPING_ADDRESS_ID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`ORDER_ITEM_ID`),
  ADD KEY `ORDER_ID` (`ORDER_ID`),
  ADD KEY `PID` (`PID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`PID`);

--
-- Indexes for table `product_likes`
--
ALTER TABLE `product_likes`
  ADD PRIMARY KEY (`LID`),
  ADD UNIQUE KEY `UID` (`UID`,`PID`),
  ADD KEY `PID` (`PID`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`STATE_ID`),
  ADD UNIQUE KEY `STATE_NAME` (`STATE_NAME`),
  ADD KEY `COUNTRY_ID` (`COUNTRY_ID`);

--
-- Indexes for table `support_chats`
--
ALTER TABLE `support_chats`
  ADD PRIMARY KEY (`CHAT_ID`),
  ADD KEY `UID` (`UID`),
  ADD KEY `AID` (`AID`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`MESSAGE_ID`),
  ADD KEY `CHAT_ID` (`CHAT_ID`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`TOKEN_ID`),
  ADD KEY `USER_ID` (`USER_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`VID`),
  ADD UNIQUE KEY `CODE` (`CODE`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `ADDID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `AID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `BILL_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `CID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `contact_form`
--
ALTER TABLE `contact_form`
  MODIFY `CONTACT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `COUNTRY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ORDER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `ORDER_ITEM_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `PID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `product_likes`
--
ALTER TABLE `product_likes`
  MODIFY `LID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `STATE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `support_chats`
--
ALTER TABLE `support_chats`
  MODIFY `CHAT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `MESSAGE_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `VID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`STATE_ID`) REFERENCES `states` (`STATE_ID`),
  ADD CONSTRAINT `address_ibfk_2` FOREIGN KEY (`COUNTRY_ID`) REFERENCES `countries` (`COUNTRY_ID`),
  ADD CONSTRAINT `address_ibfk_3` FOREIGN KEY (`UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE;

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`ORDER_ID`) REFERENCES `orders` (`ORDER_ID`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`PID`) REFERENCES `products` (`PID`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`BILLING_ADDRESS_ID`) REFERENCES `address` (`ADDID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`SHIPPING_ADDRESS_ID`) REFERENCES `address` (`ADDID`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`ORDER_ID`) REFERENCES `orders` (`ORDER_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`PID`) REFERENCES `products` (`PID`) ON DELETE CASCADE;

--
-- Constraints for table `product_likes`
--
ALTER TABLE `product_likes`
  ADD CONSTRAINT `product_likes_ibfk_1` FOREIGN KEY (`PID`) REFERENCES `products` (`PID`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_likes_ibfk_2` FOREIGN KEY (`UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE;

--
-- Constraints for table `states`
--
ALTER TABLE `states`
  ADD CONSTRAINT `states_ibfk_1` FOREIGN KEY (`COUNTRY_ID`) REFERENCES `countries` (`COUNTRY_ID`) ON DELETE CASCADE;

--
-- Constraints for table `support_chats`
--
ALTER TABLE `support_chats`
  ADD CONSTRAINT `support_chats_ibfk_1` FOREIGN KEY (`UID`) REFERENCES `users` (`UID`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_chats_ibfk_2` FOREIGN KEY (`AID`) REFERENCES `admins` (`AID`) ON DELETE SET NULL;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`CHAT_ID`) REFERENCES `support_chats` (`CHAT_ID`) ON DELETE CASCADE;

--
-- Constraints for table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `users` (`UID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
