-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 08:02 AM
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
-- Database: `kvbc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `child_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`child_id`, `parent_id`, `first_name`, `last_name`, `date_of_birth`, `gender`, `address`, `city`, `province`, `postal_code`, `special_notes`, `created_at`, `updated_at`) VALUES
(2, 2, 'Ali', 'Khan', '2020-03-15', 'Male', 'Street 5, Block A', 'Karachi', 'Sindh', '74000', 'None', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(3, 2, 'Fatima', 'Zafar', '2019-07-22', 'Female', 'House 10, Gulberg', 'Lahore', 'Punjab', '54000', 'Peanut allergy', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(4, 2, 'Ahmed', 'Iqbal', '2021-01-05', 'Male', 'House 7, G-10', 'Islamabad', 'ICT', '44000', 'None', '2025-05-29 05:17:44', '2025-05-29 05:19:49'),
(5, 2, 'Noor', 'Awan', '2022-02-11', 'Female', 'Flat 11, Bahria', 'Rawalpindi', 'Punjab', '46000', 'Asthma', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(6, 2, 'Zain', 'Ali', '2018-11-30', 'Male', 'House 2, Saddar', 'Peshawar', 'KP', '25000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(7, 2, 'Hira', 'Saleem', '2020-09-01', 'Female', 'Street 8, DHA', 'Quetta', 'Balochistan', '87300', 'Lactose Intolerance', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(8, 2, 'Usman', 'Hafeez', '2019-04-20', 'Male', 'Building 2, City Center', 'Faisalabad', 'Punjab', '38000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(9, 2, 'Maryam', 'Javed', '2021-10-09', 'Female', 'Near Canal Road', 'Multan', 'Punjab', '60000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(10, 2, 'Hassan', 'Sohail', '2020-06-30', 'Male', 'House 18, Askari', 'Hyderabad', 'Sindh', '71000', 'Eczema', '2025-05-29 05:17:44', '2025-05-29 05:17:44'),
(11, 2, 'Sara', 'Rafiq', '2022-03-18', 'Female', 'Flat 3, Clifton', 'Karachi', 'Sindh', '75000', '', '2025-05-29 05:17:44', '2025-05-29 05:17:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `user_type` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `age`, `bio`, `user_type`, `profile_picture`, `status`) VALUES
(1, 'worker12', '$2y$10$gps/vTvTY1t1FGWgealP8.zmrT0NUXQCBKVorMsz7X0Mrjo5sO.km', 'worker@gmail.com', '3176526827', 23, '', 'Worker', 'images/1753593673_411-2.png', 'approved'),
(2, 'parent1', '$2y$10$OnwDThbrX/FOiMGixb5QHOFPLRf89npIHBpwRvxF1tJHxRY8PNAMq', 'parent@gmail.com', '03120558064', 23, '', 'Parent', 'images/1753593733_1.png', 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `vaccinations`
--

CREATE TABLE `vaccinations` (
  `vaccination_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `age_limit_months` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccinations`
--

INSERT INTO `vaccinations` (`vaccination_id`, `name`, `description`, `age_limit_months`) VALUES
(1, 'BCG', 'Bacillus Calmette–Guérin vaccine for tuberculosis prevention.', 0),
(2, 'Hepatitis B (1st Dose)', 'First dose of Hepatitis B vaccine given at birth.', 0),
(3, 'OPV-0', 'Oral Polio Vaccine given at birth.', 0),
(4, 'Pentavalent-1', 'Combines DTP, Hep B, and Hib vaccines.', 6),
(5, 'OPV-1', 'First dose of Oral Polio Vaccine.', 6),
(6, 'PCV-1', 'First dose of Pneumococcal Conjugate Vaccine.', 6),
(7, 'Rotavirus-1', 'First dose of Rotavirus vaccine for diarrhea prevention.', 6),
(8, 'Pentavalent-2', 'Second dose of Pentavalent vaccine.', 10),
(9, 'OPV-2', 'Second dose of Oral Polio Vaccine.', 10),
(10, 'PCV-2', 'Second dose of Pneumococcal Conjugate Vaccine.', 10),
(11, 'Rotavirus-2', 'Second dose of Rotavirus vaccine.', 10),
(12, 'Pentavalent-3', 'Third dose of Pentavalent vaccine.', 14),
(13, 'OPV-3', 'Third dose of Oral Polio Vaccine.', 14),
(14, 'PCV-3', 'Third dose of Pneumococcal Conjugate Vaccine.', 14),
(15, 'Rotavirus-3', 'Third dose of Rotavirus vaccine.', 14),
(16, 'IPV', 'Inactivated Polio Vaccine.', 14),
(17, 'Measles-1', 'First dose of Measles vaccine.', 9),
(18, 'Vitamin A (1st Dose)', 'First dose of Vitamin A supplementation.', 9),
(19, 'Typhoid Conjugate Vaccine', 'Prevents Typhoid Fever.', 11);

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_records`
--

CREATE TABLE `vaccination_records` (
  `record_id` int(11) NOT NULL,
  `kid_id` int(11) NOT NULL,
  `vaccination_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `date_given` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccination_records`
--

INSERT INTO `vaccination_records` (`record_id`, `kid_id`, `vaccination_id`, `worker_id`, `date_given`, `created_at`) VALUES
(1, 3, 10, 1, '2025-07-27', '2025-07-27 05:16:11'),
(2, 9, 4, 1, '2025-07-27', '2025-07-27 06:02:32');

-- --------------------------------------------------------

--
-- Table structure for table `visit_bookings`
--

CREATE TABLE `visit_bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kid_id` int(11) NOT NULL,
  `vaccination_id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visit_bookings`
--

INSERT INTO `visit_bookings` (`booking_id`, `user_id`, `kid_id`, `vaccination_id`, `worker_id`, `visit_date`, `visit_time`, `status`, `feedback`, `created_at`) VALUES
(1, 2, 3, 10, 1, '2025-07-30', '09:48:00', 'Completed', 'None', '2025-07-27 09:53:03'),
(2, 2, 9, 4, 1, '2025-07-29', '00:51:00', 'Completed', '', '2025-07-27 10:51:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`child_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vaccinations`
--
ALTER TABLE `vaccinations`
  ADD PRIMARY KEY (`vaccination_id`);

--
-- Indexes for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `kid_id` (`kid_id`),
  ADD KEY `vaccination_id` (`vaccination_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- Indexes for table `visit_bookings`
--
ALTER TABLE `visit_bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kid_id` (`kid_id`),
  ADD KEY `vaccination_id` (`vaccination_id`),
  ADD KEY `worker_id` (`worker_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `child_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vaccinations`
--
ALTER TABLE `vaccinations`
  MODIFY `vaccination_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `visit_bookings`
--
ALTER TABLE `visit_bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `children`
--
ALTER TABLE `children`
  ADD CONSTRAINT `children_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccination_records`
--
ALTER TABLE `vaccination_records`
  ADD CONSTRAINT `vaccination_records_ibfk_1` FOREIGN KEY (`kid_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccination_records_ibfk_2` FOREIGN KEY (`vaccination_id`) REFERENCES `vaccinations` (`vaccination_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccination_records_ibfk_3` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `visit_bookings`
--
ALTER TABLE `visit_bookings`
  ADD CONSTRAINT `visit_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visit_bookings_ibfk_2` FOREIGN KEY (`kid_id`) REFERENCES `children` (`child_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visit_bookings_ibfk_3` FOREIGN KEY (`vaccination_id`) REFERENCES `vaccinations` (`vaccination_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visit_bookings_ibfk_4` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
