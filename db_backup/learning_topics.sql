-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2024 at 09:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blisglob_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `learning_topics`
--

CREATE TABLE `learning_topics` (
  `topic_id` int(11) NOT NULL,
  `topic_course` int(11) NOT NULL,
  `topic_week` int(11) NOT NULL,
  `topic_title` text NOT NULL,
  `topic_certification` int(11) NOT NULL,
  `topic_document` text NOT NULL,
  `topic_status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learning_topics`
--

INSERT INTO `learning_topics` (`topic_id`, `topic_course`, `topic_week`, `topic_title`, `topic_certification`, `topic_document`, `topic_status`) VALUES
(1, 1, 1, 'Overview of Electricity and Circuits', 1, '../Courses/Electricity/WEEK1.pdf', 'Active'),
(2, 1, 1, 'Simple Circuit Construction:', 1, '../Courses/Electricity/WEEK1.pdf', 'Active'),
(3, 1, 2, 'Voltage, Current, and Resistance ', 1, '../Courses/Electricity/WEEK2.pdf', 'Active'),
(4, 1, 2, ' Learn the concepts of voltage, current, and resistance.', 1, '../Courses/Electricity/WEEK2.pdf', 'Active'),
(5, 1, 3, 'Ohm\'s Law and Basic Circuit Analysis', 1, '../Courses/Electricity/WEEK3.pdf', 'Active'),
(6, 1, 3, ' Series and Parallel Circuits:', 1, '../Courses/Electricity/WEEK3.pdf', 'Active'),
(7, 1, 4, ' Introduction to Electronic Components', 1, '../Courses/Electricity/WEEK4.pdf', 'Active'),
(8, 1, 4, 'Component Identification & Testing', 1, '../Courses/Electricity/WEEK4.pdf', 'Active'),
(9, 1, 5, ' Hands-on Projects', 1, '../Courses/Electricity/WEEK5.pdf', 'Active'),
(10, 1, 5, 'Hands-on Projects  2', 1, '../Courses/Electricity/WEEK5.pdf', 'Active'),
(11, 1, 6, 'Battery Connection Experiment ', 1, '../Courses/Electricity/WEEK6.pdf', 'Active'),
(12, 1, 6, ' Series & Parallel Battery Connection ', 1, '../Courses/Electricity/WEEK6.pdf', 'Active'),
(13, 1, 7, 'Hands-on Projects Continuation ', 1, '../Courses/Electricity/WEEK7.pdf', 'Active'),
(14, 1, 7, ' Building Complex Circuits', 1, '../Courses/Electricity/WEEK7.pdf', 'Active'),
(15, 1, 8, 'Final Projects and Presentation', 1, '../Courses/Electricity/WEEK8.pdf', 'Active'),
(16, 1, 8, 'Final Project Development & Presentation ', 1, '../Courses/Electricity/WEEK8.pdf', 'Active'),
(17, 2, 1, '', 1, '', 'Active'),
(18, 2, 1, ' ', 1, '', 'Active'),
(19, 2, 2, ' ', 1, '', 'Active'),
(20, 2, 2, ' ', 1, '', 'Active'),
(21, 2, 3, ' ', 1, '', 'Active'),
(22, 2, 3, ' ', 1, '', 'Active'),
(23, 2, 4, ' ', 1, '', 'Active'),
(24, 2, 4, ' ', 1, '', 'Active'),
(25, 2, 5, ' ', 1, '', 'Active'),
(26, 2, 5, ' ', 1, '', 'Active'),
(27, 2, 6, ' ', 1, '', 'Active'),
(28, 2, 6, ' ', 1, '', 'Active'),
(29, 2, 7, ' ', 1, '', 'Active'),
(30, 2, 7, ' ', 1, '', 'Active'),
(31, 2, 8, ' ', 1, '', 'Active'),
(32, 2, 8, ' ', 1, '', 'Active'),
(33, 3, 1, '', 1, '', 'Active'),
(34, 3, 1, ' ', 1, '', 'Active'),
(35, 3, 2, ' ', 1, '', 'Active'),
(36, 3, 2, ' ', 1, '', 'Active'),
(37, 3, 3, ' ', 1, '', 'Active'),
(38, 3, 3, ' ', 1, '', 'Active'),
(39, 3, 4, ' ', 1, '', 'Active'),
(40, 3, 4, ' ', 1, '', 'Active'),
(41, 3, 5, ' ', 1, '', 'Active'),
(42, 3, 5, ' ', 1, '', 'Active'),
(43, 3, 6, ' ', 1, '', 'Active'),
(44, 3, 6, ' ', 1, '', 'Active'),
(45, 3, 7, ' ', 1, '', 'Active'),
(46, 3, 7, ' ', 1, '', 'Active'),
(47, 3, 8, ' ', 1, '', 'Active'),
(48, 3, 8, ' ', 1, '', 'Active'),
(49, 4, 1, '', 1, '', 'Active'),
(50, 4, 1, ' ', 1, '', 'Active'),
(51, 4, 2, ' ', 1, '', 'Active'),
(52, 4, 2, ' ', 1, '', 'Active'),
(53, 4, 3, ' ', 1, '', 'Active'),
(54, 4, 3, ' ', 1, '', 'Active'),
(55, 4, 4, ' ', 1, '', 'Active'),
(56, 4, 4, ' ', 1, '', 'Active'),
(57, 4, 5, ' ', 1, '', 'Active'),
(58, 4, 5, ' ', 1, '', 'Active'),
(59, 4, 6, ' ', 1, '', 'Active'),
(60, 4, 6, ' ', 1, '', 'Active'),
(61, 4, 7, ' ', 1, '', 'Active'),
(62, 4, 7, ' ', 1, '', 'Active'),
(63, 4, 8, ' ', 1, '', 'Active'),
(64, 4, 8, ' ', 1, '', 'Active');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `learning_topics`
--
ALTER TABLE `learning_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `topic_certification` (`topic_certification`),
  ADD KEY `topic_week` (`topic_week`),
  ADD KEY `topic_course` (`topic_course`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `learning_topics`
--
ALTER TABLE `learning_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
