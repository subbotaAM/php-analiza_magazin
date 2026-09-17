-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 08:18 PM
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
-- Database: `magazin`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id_client` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_telefon` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id_client`, `nume`, `email`, `id_telefon`) VALUES
(1, 'Ion Popescu', 'ion@mail.com', 1),
(2, 'Maria Ionescu', 'maria@mail.com', 3),
(3, 'Alex Marin', 'alex@mail.com', 5),
(4, 'Elena Dinu', 'elena@mail.com', 2),
(5, 'Vlad Rusu', 'vlad@mail.com', 4);

-- --------------------------------------------------------

--
-- Table structure for table `producator`
--

CREATE TABLE `producator` (
  `id_producator` int(11) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `tara` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `producator`
--

INSERT INTO `producator` (`id_producator`, `nume`, `tara`) VALUES
(1, 'Apple', 'USA'),
(2, 'Samsung', 'Coreea de Sud'),
(3, 'Xiaomi', 'China');

-- --------------------------------------------------------

--
-- Table structure for table `telefon`
--

CREATE TABLE `telefon` (
  `id_telefon` int(11) NOT NULL,
  `model` varchar(100) NOT NULL,
  `pret` decimal(10,2) DEFAULT NULL,
  `id_producator` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `telefon`
--

INSERT INTO `telefon` (`id_telefon`, `model`, `pret`, `id_producator`) VALUES
(1, 'iPhone 14', 1200.00, 1),
(2, 'iPhone 13', 900.00, 1),
(3, 'Galaxy S22', 1000.00, 2),
(4, 'Galaxy A52', 500.00, 2),
(5, 'Redmi Note 12', 400.00, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_client`),
  ADD KEY `id_telefon` (`id_telefon`);

--
-- Indexes for table `producator`
--
ALTER TABLE `producator`
  ADD PRIMARY KEY (`id_producator`);

--
-- Indexes for table `telefon`
--
ALTER TABLE `telefon`
  ADD PRIMARY KEY (`id_telefon`),
  ADD KEY `id_producator` (`id_producator`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id_client` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `producator`
--
ALTER TABLE `producator`
  MODIFY `id_producator` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `telefon`
--
ALTER TABLE `telefon`
  MODIFY `id_telefon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`id_telefon`) REFERENCES `telefon` (`id_telefon`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `telefon`
--
ALTER TABLE `telefon`
  ADD CONSTRAINT `telefon_ibfk_1` FOREIGN KEY (`id_producator`) REFERENCES `producator` (`id_producator`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
