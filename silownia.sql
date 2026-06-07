-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 05, 2026 at 12:46 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `silownia`
--

-- --------------------------------------------------------

--
-- Table structure for table `trenerzy`
--

CREATE TABLE `trenerzy` (
  `id` int(11) NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `specjalizacja` varchar(100) NOT NULL,
  `cena` decimal(6,2) NOT NULL,
  `numer_telefonu` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trenerzy`
--

INSERT INTO `trenerzy` (`id`, `imie`, `nazwisko`, `specjalizacja`, `cena`, `numer_telefonu`) VALUES
(7, 'Jakub123', 'Węż1', 'Korzykowka', 145.00, '100100100'),
(8, 'Alina123', 'Karolina', 'Fitness', 150.00, '2002002200'),
(9, 'Hello', 'World', 'Fitness', 200.00, '300400500'),
(10, 'Peter1', 'Czech', 'Piilka', 400.00, '100200300');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role` int(11) DEFAULT 0,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(50) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `pesel` varchar(11) DEFAULT NULL,
  `numer_telefonu` varchar(15) DEFAULT NULL,
  `miasto` varchar(50) DEFAULT NULL,
  `ulica` varchar(50) DEFAULT NULL,
  `numer_domu` varchar(10) DEFAULT NULL,
  `numer_lokalu` varchar(10) DEFAULT NULL,
  `kod_pocztowy` varchar(6) DEFAULT NULL,
  `opinia` text DEFAULT NULL,
  `karnet_typ` varchar(50) DEFAULT 'Brak',
  `karnet_zakup` date DEFAULT NULL,
  `karnet_koniec` date DEFAULT NULL,
  `waga` decimal(5,2) DEFAULT NULL,
  `wzrost` int(11) DEFAULT NULL,
  `bmi` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `imie`, `nazwisko`, `mail`, `haslo`, `pesel`, `numer_telefonu`, `miasto`, `ulica`, `numer_domu`, `numer_lokalu`, `kod_pocztowy`, `opinia`, `karnet_typ`, `karnet_zakup`, `karnet_koniec`, `waga`, `wzrost`, `bmi`) VALUES
(2, 1, 'Andrii1', 'Khomenko', 'akhom@gmail.com', '$2y$10$lV56eP0UKuN1jjNEwMWLtOZuvM0Kp4i.qkh6sspInN767tlMU1dim', '09252112783', NULL, NULL, 'Dobrego', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 80.00, 183, 23.90),
(7, 0, 'Peter', 'Czech', 'pc@gmail.com', '$2y$10$6hWmFHjn5EtuW6X93VOB0uImL.xCvYZmU.D4ewGJn3Zjkaid6Ietu', '77021726158', NULL, 'Krakow', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 0, 'Jakub1', 'Peter', 'jp@gmail.com', '$2y$10$TAfubFEH6CP703SmSL6vGuVylnTctDrq1jiQGi3dqIfL2t774lsSu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brak', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trenerzy`
--
ALTER TABLE `trenerzy`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trenerzy`
--
ALTER TABLE `trenerzy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
