-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 08:19 PM
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
-- Database: `organizatii`
--

-- --------------------------------------------------------

--
-- Table structure for table `comanda_org`
--

CREATE TABLE `comanda_org` (
  `id_comanda` int(11) NOT NULL,
  `id_companie` int(11) DEFAULT NULL,
  `id_produs` int(11) DEFAULT NULL,
  `cantitate` int(11) DEFAULT NULL,
  `data_comanda` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comanda_org`
--

INSERT INTO `comanda_org` (`id_comanda`, `id_companie`, `id_produs`, `cantitate`, `data_comanda`) VALUES
(1, 1, 1, 2, '2025-01-10'),
(2, 2, 2, 5, '2025-02-15'),
(3, 3, 3, 1, '2025-03-01'),
(4, 1, 2, 3, '2025-03-10');

-- --------------------------------------------------------

--
-- Table structure for table `companie`
--

CREATE TABLE `companie` (
  `id_companie` int(11) NOT NULL,
  `nume_companie` varchar(100) NOT NULL,
  `domeniu` varchar(100) DEFAULT NULL,
  `tara` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companie`
--

INSERT INTO `companie` (`id_companie`, `nume_companie`, `domeniu`, `tara`) VALUES
(1, 'Tech Solutions SRL', 'IT', 'Romania'),
(2, 'Global Trade LLC', 'Comert', 'USA'),
(3, 'Industrial Parts GmbH', 'Manufactura', 'Germania');

-- --------------------------------------------------------

--
-- Table structure for table `produs_org`
--

CREATE TABLE `produs_org` (
  `id_produs` int(11) NOT NULL,
  `denumire` varchar(100) DEFAULT NULL,
  `pret` decimal(10,2) DEFAULT NULL,
  `specificatii` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specificatii`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produs_org`
--

INSERT INTO `produs_org` (`id_produs`, `denumire`, `pret`, `specificatii`) VALUES
(1, 'Server Dell', 5000.00, '{\"garantie\": \"3 ani\", \"categorie\": \"hardware\"}'),
(2, 'Licenta Software ERP', 2000.00, '{\"tip\": \"software\", \"utilizatori\": 50}'),
(3, 'Echipament Industrial', 8000.00, '{\"greutate\": \"200kg\", \"tip\": \"utilaj\"}');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comanda_org`
--
ALTER TABLE `comanda_org`
  ADD PRIMARY KEY (`id_comanda`),
  ADD KEY `id_companie` (`id_companie`),
  ADD KEY `id_produs` (`id_produs`);

--
-- Indexes for table `companie`
--
ALTER TABLE `companie`
  ADD PRIMARY KEY (`id_companie`);

--
-- Indexes for table `produs_org`
--
ALTER TABLE `produs_org`
  ADD PRIMARY KEY (`id_produs`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comanda_org`
--
ALTER TABLE `comanda_org`
  MODIFY `id_comanda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `companie`
--
ALTER TABLE `companie`
  MODIFY `id_companie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `produs_org`
--
ALTER TABLE `produs_org`
  MODIFY `id_produs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comanda_org`
--
ALTER TABLE `comanda_org`
  ADD CONSTRAINT `comanda_org_ibfk_1` FOREIGN KEY (`id_companie`) REFERENCES `companie` (`id_companie`),
  ADD CONSTRAINT `comanda_org_ibfk_2` FOREIGN KEY (`id_produs`) REFERENCES `produs_org` (`id_produs`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
