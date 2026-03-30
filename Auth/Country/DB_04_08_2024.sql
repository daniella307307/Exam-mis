-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 04, 2024 at 02:17 AM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 8.0.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blis_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_user_permission`
--

CREATE TABLE `active_user_permission` (
  `active_permission_id` int(10) NOT NULL,
  `active_permission` int(11) NOT NULL,
  `Active_user_ref` int(11) NOT NULL,
  `permission_status` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `active_user_permission`
--

INSERT INTO `active_user_permission` (`active_permission_id`, `active_permission`, `Active_user_ref`, `permission_status`) VALUES
(1, 1, 1, ''),
(2, 2, 1, ''),
(7, 4, 3, 'Active'),
(8, 4, 4, 'Active'),
(9, 4, 5, 'Active'),
(10, 4, 6, 'Active'),
(11, 4, 7, 'Active'),
(20, 1, 9, 'Active'),
(21, 2, 9, ''),
(42, 3, 9, ''),
(43, 4, 9, ''),
(44, 5, 9, ''),
(45, 6, 9, ''),
(47, 3, 2, ''),
(48, 2, 2, ''),
(49, 4, 2, 'Active'),
(50, 1, 2, ''),
(51, 6, 2, ''),
(52, 5, 2, ''),
(53, 4, 1, ''),
(54, 5, 1, 'Active'),
(55, 6, 1, ''),
(56, 3, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `bank_id` int(11) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_country` int(11) NOT NULL,
  `bank_region` int(11) NOT NULL,
  `bank_status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`bank_id`, `bank_name`, `bank_country`, `bank_region`, `bank_status`) VALUES
(1, 'Access Bank Rwanda', 186, 3, 'Active'),
(2, 'Bank of Africa Rwanda Plc', 186, 3, 'Active'),
(3, 'Bank of Kigali', 186, 3, 'Active'),
(4, 'BPR Bank Rwanda Plc', 186, 3, 'Active'),
(5, 'NCBA Bank Rwanda', 186, 3, 'Active'),
(6, 'Equity Bank Rwanda Limited', 186, 3, 'Active'),
(7, 'Ecobank Rwanda', 186, 3, 'Active'),
(8, 'GTBank Rwanda Plc', 186, 3, 'Active'),
(9, 'I&M Bank (Rwanda)', 186, 3, 'Active'),
(10, 'AB Bank Rwanda', 186, 3, 'Active'),
(11, 'Unguka Bank', 186, 3, 'Active'),
(12, 'Development Bank of Rwanda (BRD) ', 186, 3, 'Active'),
(13, 'Housing Bank of Rwanda', 186, 3, 'Active'),
(14, 'Zigama CSS', 186, 3, 'Active'),
(15, 'Co-operative Bank Rwanda', 186, 3, 'Active'),
(16, 'Umwalimu SACCO', 186, 3, 'Active'),
(17, 'Umurenge SACCO', 186, 3, 'Active'),
(18, 'Access Bank Cameroon', 41, 4, 'Active'),
(19, 'Afriland First Bank', 41, 4, 'Active'),
(20, 'Atlantic Bank Cameroon', 41, 4, 'Active'),
(21, 'Banque International du Cameroun pour l\'Epargne et le Crédit (BICEC)', 41, 4, 'Active'),
(22, 'Banque Camerounaise des Petites et Moyennes Entreprises (BC-PME SA)', 41, 4, 'Active'),
(23, 'BGFI Bank Cameroon', 41, 4, 'Active'),
(24, 'SCB Cameroun', 41, 4, 'Active'),
(25, 'Crédit Communautaire d\'Afrique Bank (CCA Bank)', 41, 4, 'Active'),
(26, 'Citibank', 41, 4, 'Active'),
(27, 'Commercial Bank of Cameroon', 41, 4, 'Active'),
(28, 'Oceanic Bank Cameroon', 41, 4, 'Active'),
(29, 'National Financial Credit Bank (NFCB)', 41, 4, 'Active'),
(30, 'Société Commerciale de Banque du Cameroun', 41, 4, 'Active'),
(31, 'Societe Generale des Banques au Cameroun (SGBC)', 41, 4, 'Active'),
(32, 'Standard Chartered Bank', 41, 4, 'Active'),
(33, 'Union Bank of Cameroon (UBC)', 41, 4, 'Active'),
(34, 'United Bank for Africa (UBA)', 41, 4, 'Active'),
(35, 'Banque Nationale de Guinée-Équatoriale', 41, 4, 'Active'),
(36, 'Attijari Securities Central Africa (ASCA)', 41, 4, 'Active'),
(37, 'La Regionale Bank', 41, 4, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `bank_sleeps`
--

CREATE TABLE `bank_sleeps` (
  `sleep_id` int(11) NOT NULL,
  `sleep_bank` int(11) NOT NULL,
  `sleep_school` int(11) NOT NULL,
  `sleep_country` int(11) NOT NULL,
  `sleep_region` int(11) NOT NULL,
  `sleep_no` varchar(32) NOT NULL,
  `sleep_amount_usd` decimal(16,2) NOT NULL,
  `sleep_amount_local` decimal(16,2) NOT NULL,
  `sleep_date` date DEFAULT NULL,
  `sleep_document` text NOT NULL,
  `sleep_status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bank_sleeps`
--

INSERT INTO `bank_sleeps` (`sleep_id`, `sleep_bank`, `sleep_school`, `sleep_country`, `sleep_region`, `sleep_no`, `sleep_amount_usd`, `sleep_amount_local`, `sleep_date`, `sleep_document`, `sleep_status`) VALUES
(1, 26, 1, 0, 0, 'A2034', '3681.58', '2213587.00', '2024-08-26', '', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE `certifications` (
  `certification_id` int(11) NOT NULL,
  `certification_name` varchar(100) NOT NULL,
  `certification_duration` int(11) NOT NULL,
  `certification_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `certifications`
--

INSERT INTO `certifications` (`certification_id`, `certification_name`, `certification_duration`, `certification_status`) VALUES
(1, 'Platinum coders certification', 8, 'Active'),
(2, 'Golden coders certification', 12, 'Inactive'),
(3, 'Genius coders certification', 12, 'Inactive'),
(4, 'Drone Piloting certification', 12, 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `certification_courses`
--

CREATE TABLE `certification_courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(32) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_certificate` int(11) NOT NULL,
  `course_status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `certification_courses`
--

INSERT INTO `certification_courses` (`course_id`, `course_code`, `course_name`, `course_certificate`, `course_status`) VALUES
(1, 'BELEC001', 'Introduction to Electricity and Electronics', 1, 'Active'),
(2, 'BROB001', 'Introduction to Microcontrollers', 1, 'Active'),
(3, 'BMED001', 'Mechanics and Motion', 1, 'Active'),
(4, 'BROB002', 'Introduction to Robotics', 1, 'Active'),
(5, 'BROB003', 'Robot Sensing and Perception', 2, 'Active'),
(6, 'BROB004', 'Robot Control and Actuation', 2, 'Active'),
(7, 'BAERO001', 'Introduction to Aerial Robotics', 2, 'Active'),
(8, 'BROB005', 'Introduction to Swarm Robotics', 2, 'Active'),
(9, 'BROB006', 'Introduction to Artificial Intelligence', 3, 'Active'),
(10, 'BROB007', 'Advanced Robotics Topics', 3, 'Active'),
(11, 'BWEB001', 'Full stack web development', 3, 'Active'),
(12, 'BPRO001', 'Capstone Project', 3, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `Country_name` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `Country_currency` varchar(68) COLLATE utf8_unicode_ci NOT NULL,
  `Country_currency_code` varchar(68) COLLATE utf8_unicode_ci NOT NULL,
  `currency_usd` decimal(10,6) NOT NULL,
  `Country_region` int(11) NOT NULL,
  `Country_status` varchar(23) COLLATE utf8_unicode_ci NOT NULL,
  `Country_flag` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `Country_name`, `Country_currency`, `Country_currency_code`, `currency_usd`, `Country_region`, `Country_status`, `Country_flag`) VALUES
(1, 'Afghan afghani  ', 'nbnmbnmvbnv', 'nbvbnvb', '0.000000', 0, 'Inactive', '  nm mvbnvb'),
(2, 'Akrotiri and Dhekelia (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(3, 'Aland Islands (Finland)', '', '', '0.000000', 0, 'Inactive', ''),
(4, 'Albania', '', '', '0.000000', 0, 'Inactive', ''),
(5, 'Algeria', '', '', '0.000000', 0, 'Inactive', ''),
(6, 'American Samoa (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(7, 'Andorra', '', '', '0.000000', 0, 'Inactive', ''),
(8, 'Angola', '', '', '0.000000', 0, 'Inactive', ''),
(9, 'Anguilla (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(10, 'Antigua and Barbuda', '', '', '0.000000', 0, 'Inactive', ''),
(11, 'Argentina', '', '', '0.000000', 0, 'Inactive', ''),
(12, 'Armenia', '', '', '0.000000', 0, 'Inactive', ''),
(13, 'Aruba (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(14, 'Ascension Island (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(15, 'Australia', '', '', '0.000000', 0, 'Inactive', ''),
(16, 'Austria', '', '', '0.000000', 0, 'Inactive', ''),
(17, 'Azerbaijan', '', '', '0.000000', 0, 'Inactive', ''),
(18, 'Bahamas', '', '', '0.000000', 0, 'Inactive', ''),
(19, 'Bahrain', '', '', '0.000000', 0, 'Inactive', ''),
(20, 'Bangladesh', '', '', '0.000000', 0, 'Inactive', ''),
(21, 'Barbados', '', '', '0.000000', 0, 'Inactive', ''),
(22, 'Belarus', '', '', '0.000000', 0, 'Inactive', ''),
(23, 'Belgium', '', '', '0.000000', 0, 'Inactive', ''),
(24, 'Belize', '', '', '0.000000', 0, 'Inactive', ''),
(25, 'Benin', '', '', '0.000000', 0, 'Inactive', ''),
(26, 'Bermuda (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(27, 'Bhutan', '', '', '0.000000', 0, 'Inactive', ''),
(28, 'Bolivia', '', '', '0.000000', 0, 'Inactive', ''),
(29, 'Bonaire (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(30, 'Bosnia and Herzegovina', '', '', '0.000000', 0, 'Inactive', ''),
(31, 'Botswana', '', '', '0.000000', 0, 'Inactive', ''),
(32, 'Brazil', '', '', '0.000000', 0, 'Inactive', ''),
(33, 'British Indian Ocean Territory (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(34, 'British Virgin Islands (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(35, 'Brunei', '', '', '0.000000', 0, 'Inactive', ''),
(36, 'Bulgaria', '', '', '0.000000', 0, 'Inactive', ''),
(37, 'Burkina Faso', '', '', '0.000000', 0, 'Inactive', ''),
(38, 'Burundi', 'Burundian Franc', 'FBU', '0.000000', 0, 'Active', 'Country/Burundi.png'),
(39, 'Cabo Verde', '', '', '0.000000', 0, 'Inactive', ''),
(40, 'Cambodia', '', '', '0.000000', 0, 'Inactive', ''),
(41, 'Cameroon', 'Central African CFA franc', 'CFA', '601.026000', 3, 'Active', 'Country/Cameroon Flag.png'),
(42, 'Canada', '', '', '0.000000', 0, 'Inactive', ''),
(43, 'Caribbean Netherlands (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(44, 'Cayman Islands (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(45, 'Central African Republic', '', '', '0.000000', 0, 'Inactive', ''),
(46, 'Chad', '', '', '0.000000', 0, 'Inactive', ''),
(47, 'Chatham Islands (New Zealand)', '', '', '0.000000', 0, 'Inactive', ''),
(48, 'Chile', '', '', '0.000000', 0, 'Inactive', ''),
(49, 'China', '', '', '0.000000', 0, 'Inactive', ''),
(50, 'Christmas Island (Australia)', '', '', '0.000000', 0, 'Inactive', ''),
(51, 'Cocos (Keeling) Islands (Australia)', '', '', '0.000000', 0, 'Inactive', ''),
(52, 'Colombia', '', '', '0.000000', 0, 'Inactive', ''),
(53, 'Comoros', '', '', '0.000000', 0, 'Inactive', ''),
(54, 'Congo, Democratic Republic of the', '', '', '0.000000', 0, 'Inactive', ''),
(55, 'Congo, Republic of the', '', '', '0.000000', 0, 'Inactive', ''),
(56, 'Cook Islands (New Zealand)', '', '', '0.000000', 0, 'Inactive', ''),
(57, 'Costa Rica', '', '', '0.000000', 0, 'Inactive', ''),
(58, 'Cote d\'Ivoire', '', '', '0.000000', 0, 'Inactive', ''),
(59, 'Croatia', '', '', '0.000000', 0, 'Inactive', ''),
(60, 'Cuba', '', '', '0.000000', 0, 'Inactive', ''),
(61, 'Curacao (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(62, 'Cyprus', '', '', '0.000000', 0, 'Inactive', ''),
(63, 'Czechia', '', '', '0.000000', 0, 'Inactive', ''),
(64, 'Denmark', '', '', '0.000000', 0, 'Inactive', ''),
(65, 'Djibouti', '', '', '0.000000', 0, 'Inactive', ''),
(66, 'Dominica', '', '', '0.000000', 0, 'Inactive', ''),
(67, 'Dominican Republic', '', '', '0.000000', 0, 'Inactive', ''),
(68, 'Ecuador', '', '', '0.000000', 0, 'Inactive', ''),
(69, 'Egypt', '', '', '0.000000', 0, 'Inactive', ''),
(70, 'El Salvador', '', '', '0.000000', 0, 'Inactive', ''),
(71, 'Equatorial Guinea', '', '', '0.000000', 0, 'Inactive', ''),
(72, 'Eritrea', '', '', '0.000000', 0, 'Inactive', ''),
(73, 'Estonia', '', '', '0.000000', 0, 'Inactive', ''),
(74, 'Eswatini (formerly Swaziland)', '', '', '0.000000', 0, 'Inactive', ''),
(75, 'Ethiopia', '', '', '0.000000', 0, 'Inactive', ''),
(76, 'Falkland Islands (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(77, 'Faroe Islands (Denmark)', '', '', '0.000000', 0, 'Inactive', ''),
(78, 'Fiji', '', '', '0.000000', 0, 'Inactive', ''),
(79, 'Finland', '', '', '0.000000', 0, 'Inactive', ''),
(80, 'France', '', '', '0.000000', 0, 'Inactive', ''),
(81, 'French Guiana (France)', '', '', '0.000000', 0, 'Inactive', ''),
(82, 'French Polynesia (France)', '', '', '0.000000', 0, 'Inactive', ''),
(83, 'Gabon', '', '', '0.000000', 0, 'Inactive', ''),
(84, 'Gambia', '', '', '0.000000', 0, 'Inactive', ''),
(85, 'Georgia', '', '', '0.000000', 0, 'Inactive', ''),
(86, 'Germany', '', '', '0.000000', 0, 'Inactive', ''),
(87, 'Ghana', '', '', '0.000000', 0, 'Inactive', ''),
(88, 'Gibraltar (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(89, 'Greece', '', '', '0.000000', 0, 'Inactive', ''),
(90, 'Greenland (Denmark)', '', '', '0.000000', 0, 'Inactive', ''),
(91, 'Grenada', '', '', '0.000000', 0, 'Inactive', ''),
(92, 'Guadeloupe (France)', '', '', '0.000000', 0, 'Inactive', ''),
(93, 'Guam (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(94, 'Guatemala', '', '', '0.000000', 0, 'Inactive', ''),
(95, 'Guernsey (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(96, 'Guinea', '', '', '0.000000', 0, 'Inactive', ''),
(97, 'Guinea-Bissau', '', '', '0.000000', 0, 'Inactive', ''),
(98, 'Guyana', '', '', '0.000000', 0, 'Inactive', ''),
(99, 'Haiti', '', '', '0.000000', 0, 'Inactive', ''),
(100, 'Honduras', '', '', '0.000000', 0, 'Inactive', ''),
(101, 'Hong Kong (China)', '', '', '0.000000', 0, 'Inactive', ''),
(102, 'Hungary', '', '', '0.000000', 0, 'Inactive', ''),
(103, 'Iceland', '', '', '0.000000', 0, 'Inactive', ''),
(104, 'India', '', '', '0.000000', 0, 'Inactive', ''),
(105, 'Indonesia', '', '', '0.000000', 0, 'Inactive', ''),
(106, 'International Monetary Fund (IMF)', '', '', '0.000000', 0, 'Inactive', ''),
(107, 'Iran', '', '', '0.000000', 0, 'Inactive', ''),
(108, 'Iraq', '', '', '0.000000', 0, 'Inactive', ''),
(109, 'Ireland', '', '', '0.000000', 0, 'Inactive', ''),
(110, 'Isle of Man (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(111, 'Israel', '', '', '0.000000', 0, 'Inactive', ''),
(112, 'Italy', '', '', '0.000000', 0, 'Inactive', ''),
(113, 'Jamaica', '', '', '0.000000', 0, 'Inactive', ''),
(114, 'Japan', '', '', '0.000000', 0, 'Inactive', ''),
(115, 'Jersey (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(116, 'Jordan', '', '', '0.000000', 0, 'Inactive', ''),
(117, 'Kazakhstan', '', '', '0.000000', 0, 'Inactive', ''),
(118, 'Kenya', '', '', '0.000000', 0, 'Inactive', ''),
(119, 'Kiribati', '', '', '0.000000', 0, 'Inactive', ''),
(120, 'Kosovo', '', '', '0.000000', 0, 'Inactive', ''),
(121, 'Kuwait', '', '', '0.000000', 0, 'Inactive', ''),
(122, 'Kyrgyzstan', '', '', '0.000000', 0, 'Inactive', ''),
(123, 'Laos', '', '', '0.000000', 0, 'Inactive', ''),
(124, 'Latvia', '', '', '0.000000', 0, 'Inactive', ''),
(125, 'Lebanon', '', '', '0.000000', 0, 'Inactive', ''),
(126, 'Lesotho', '', '', '0.000000', 0, 'Inactive', ''),
(127, 'Liberia', '', '', '0.000000', 0, 'Inactive', ''),
(128, 'Libya', '', '', '0.000000', 0, 'Inactive', ''),
(129, 'Liechtenstein', '', '', '0.000000', 0, 'Inactive', ''),
(130, 'Lithuania', '', '', '0.000000', 0, 'Inactive', ''),
(131, 'Luxembourg', '', '', '0.000000', 0, 'Inactive', ''),
(132, 'Macau (China)', '', '', '0.000000', 0, 'Inactive', ''),
(133, 'Madagascar', '', '', '0.000000', 0, 'Inactive', ''),
(134, 'Malawi', '', '', '0.000000', 0, 'Inactive', ''),
(135, 'Malaysia', '', '', '0.000000', 0, 'Inactive', ''),
(136, 'Maldives', '', '', '0.000000', 0, 'Inactive', ''),
(137, 'Mali', '', '', '0.000000', 0, 'Inactive', ''),
(138, 'Malta', '', '', '0.000000', 0, 'Inactive', ''),
(139, 'Marshall Islands', '', '', '0.000000', 0, 'Inactive', ''),
(140, 'Martinique (France)', '', '', '0.000000', 0, 'Inactive', ''),
(141, 'Mauritania', '', '', '0.000000', 0, 'Inactive', ''),
(142, 'Mauritius', '', '', '0.000000', 0, 'Inactive', ''),
(143, 'Mayotte (France)', '', '', '0.000000', 0, 'Inactive', ''),
(144, 'Mexico', '', '', '0.000000', 0, 'Inactive', ''),
(145, 'Micronesia', '', '', '0.000000', 0, 'Inactive', ''),
(146, 'Moldova', '', '', '0.000000', 0, 'Inactive', ''),
(147, 'Monaco', '', '', '0.000000', 0, 'Inactive', ''),
(148, 'Mongolia', '', '', '0.000000', 0, 'Inactive', ''),
(149, 'Montenegro', '', '', '0.000000', 0, 'Inactive', ''),
(150, 'Montserrat (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(151, 'Morocco', '', '', '0.000000', 0, 'Inactive', ''),
(152, 'Mozambique', '', '', '0.000000', 0, 'Inactive', ''),
(153, 'Myanmar (formerly Burma)', '', '', '0.000000', 0, 'Inactive', ''),
(154, 'Namibia', '', '', '0.000000', 0, 'Inactive', ''),
(155, 'Nauru', '', '', '0.000000', 0, 'Inactive', ''),
(156, 'Nepal', '', '', '0.000000', 0, 'Inactive', ''),
(157, 'Netherlands', '', '', '0.000000', 0, 'Inactive', ''),
(158, 'New Caledonia (France)', '', '', '0.000000', 0, 'Inactive', ''),
(159, 'New Zealand', '', '', '0.000000', 0, 'Inactive', ''),
(160, 'Nicaragua', '', '', '0.000000', 0, 'Inactive', ''),
(161, 'Niger', '', '', '0.000000', 0, 'Inactive', ''),
(162, 'Nigeria', '', '', '0.000000', 0, 'Inactive', ''),
(163, 'Niue (New Zealand)', '', '', '0.000000', 0, 'Inactive', ''),
(164, 'Norfolk Island (Australia)', '', '', '0.000000', 0, 'Inactive', ''),
(165, 'Northern Mariana Islands (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(166, 'North Korea', '', '', '0.000000', 0, 'Inactive', ''),
(167, 'North Macedonia (formerly Macedonia)', '', '', '0.000000', 0, 'Inactive', ''),
(168, 'Norway', '', '', '0.000000', 0, 'Inactive', ''),
(169, 'Oman', '', '', '0.000000', 0, 'Inactive', ''),
(170, 'Pakistan', '', '', '0.000000', 0, 'Inactive', ''),
(171, 'Palau', '', '', '0.000000', 0, 'Inactive', ''),
(172, 'Palestine', '', '', '0.000000', 0, 'Inactive', ''),
(173, 'Panama', '', '', '0.000000', 0, 'Inactive', ''),
(174, 'Papua New Guinea', '', '', '0.000000', 0, 'Inactive', ''),
(175, 'Paraguay', '', '', '0.000000', 0, 'Inactive', ''),
(176, 'Peru', '', '', '0.000000', 0, 'Inactive', ''),
(177, 'Philippines', '', '', '0.000000', 0, 'Inactive', ''),
(178, 'Pitcairn Islands (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(179, 'Poland', '', '', '0.000000', 0, 'Inactive', ''),
(180, 'Portugal', '', '', '0.000000', 0, 'Inactive', ''),
(181, 'Puerto Rico (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(182, 'Qatar', '', '', '0.000000', 0, 'Inactive', ''),
(183, 'Reunion (France)', '', '', '0.000000', 0, 'Inactive', ''),
(184, 'Romania', '', '', '0.000000', 0, 'Inactive', ''),
(185, 'Russia', '', '', '0.000000', 0, 'Inactive', ''),
(186, 'Rwanda', 'Rwandan Francs', 'FRW', '1302.110000', 3, 'Active', 'Country/Rwanda Flag.png'),
(187, 'Saba (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(188, 'Saint Barthelemy (France)', '', '', '0.000000', 0, 'Inactive', ''),
(189, 'Saint Helena (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(190, 'Saint Kitts and Nevis', '', '', '0.000000', 0, 'Inactive', ''),
(191, 'Saint Lucia', '', '', '0.000000', 0, 'Inactive', ''),
(192, 'Saint Martin (France)', '', '', '0.000000', 0, 'Inactive', ''),
(193, 'Saint Pierre and Miquelon (France)', '', '', '0.000000', 0, 'Inactive', ''),
(194, 'Saint Vincent and the Grenadines', '', '', '0.000000', 0, 'Inactive', ''),
(195, 'Samoa', '', '', '0.000000', 0, 'Inactive', ''),
(196, 'San Marino', '', '', '0.000000', 0, 'Inactive', ''),
(197, 'Sao Tome and Principe', '', '', '0.000000', 0, 'Inactive', ''),
(198, 'Saudi Arabia', '', '', '0.000000', 0, 'Inactive', ''),
(199, 'Senegal', '', '', '0.000000', 0, 'Inactive', ''),
(200, 'Serbia', '', '', '0.000000', 0, 'Inactive', ''),
(201, 'Seychelles', '', '', '0.000000', 0, 'Inactive', ''),
(202, 'Sierra Leone', '', '', '0.000000', 0, 'Inactive', ''),
(203, 'Singapore', '', '', '0.000000', 0, 'Inactive', ''),
(204, 'Sint Eustatius (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(205, 'Sint Maarten (Netherlands)', '', '', '0.000000', 0, 'Inactive', ''),
(206, 'Slovakia', '', '', '0.000000', 0, 'Inactive', ''),
(207, 'Slovenia', '', '', '0.000000', 0, 'Inactive', ''),
(208, 'Solomon Islands', '', '', '0.000000', 0, 'Inactive', ''),
(209, 'Somalia', '', '', '0.000000', 0, 'Inactive', ''),
(210, 'South Africa', '', '', '0.000000', 0, 'Inactive', ''),
(211, 'South Georgia Island (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(212, 'South Korea', '', '', '0.000000', 0, 'Inactive', ''),
(213, 'South Sudan', '', '', '0.000000', 0, 'Inactive', ''),
(214, 'Spain', '', '', '0.000000', 0, 'Inactive', ''),
(215, 'Sri Lanka', '', '', '0.000000', 0, 'Inactive', ''),
(216, 'Sudan', '', '', '0.000000', 0, 'Inactive', ''),
(217, 'Suriname', '', '', '0.000000', 0, 'Inactive', ''),
(218, 'Svalbard and Jan Mayen (Norway)', '', '', '0.000000', 0, 'Inactive', ''),
(219, 'Sweden', '', '', '0.000000', 0, 'Inactive', ''),
(220, 'Switzerland', '', '', '0.000000', 0, 'Inactive', ''),
(221, 'Syria', '', '', '0.000000', 0, 'Inactive', ''),
(222, 'Taiwan', '', '', '0.000000', 0, 'Inactive', ''),
(223, 'Tajikistan', '', '', '0.000000', 0, 'Inactive', ''),
(224, 'Tanzania', '', '', '0.000000', 0, 'Inactive', ''),
(225, 'Thailand', '', '', '0.000000', 0, 'Inactive', ''),
(226, 'Timor-Leste', '', '', '0.000000', 0, 'Inactive', ''),
(227, 'Togo', '', '', '0.000000', 0, 'Inactive', ''),
(228, 'Tokelau (New Zealand)', '', '', '0.000000', 0, 'Inactive', ''),
(229, 'Tonga', '', '', '0.000000', 0, 'Inactive', ''),
(230, 'Trinidad and Tobago', '', '', '0.000000', 0, 'Inactive', ''),
(231, 'Tristan da Cunha (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(232, 'Tunisia', '', '', '0.000000', 0, 'Inactive', ''),
(233, 'Turkey', '', '', '0.000000', 0, 'Inactive', ''),
(234, 'Turkmenistan', '', '', '0.000000', 0, 'Inactive', ''),
(235, 'Turks and Caicos Islands (UK)', '', '', '0.000000', 0, 'Inactive', ''),
(236, 'Tuvalu', '', '', '0.000000', 0, 'Inactive', ''),
(237, 'Uganda', 'Ugandan Shilling', 'UGX', '0.000000', 3, 'Active', 'Country/Uganda.png'),
(238, 'Ukraine', '', '', '0.000000', 0, 'Inactive', ''),
(239, 'United Arab Emirates', '', '', '0.000000', 0, 'Inactive', ''),
(240, 'United Kingdom', '', '', '0.000000', 0, 'Inactive', ''),
(241, 'United States of America', '', '', '0.000000', 0, 'Inactive', ''),
(242, 'Uruguay', '', '', '0.000000', 0, 'Inactive', ''),
(243, 'US Virgin Islands (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(244, 'Uzbekistan', '', '', '0.000000', 0, 'Inactive', ''),
(245, 'Vanuatu', '', '', '0.000000', 0, 'Inactive', ''),
(246, 'Vatican City (Holy See)', '', '', '0.000000', 0, 'Inactive', ''),
(247, 'Venezuela', '', '', '0.000000', 0, 'Inactive', ''),
(248, 'Vietnam', '', '', '0.000000', 0, 'Inactive', ''),
(249, 'Wake Island (USA)', '', '', '0.000000', 0, 'Inactive', ''),
(250, 'Wallis and Futuna (France)', '', '', '0.000000', 0, 'Inactive', ''),
(251, 'Yemen', '', '', '0.000000', 0, 'Inactive', ''),
(252, 'Zambia', 'Zambian Kwacha', 'ZK', '0.000000', 3, 'Active', 'Country/zambia.png'),
(253, 'Zimbabwe', '', '', '0.000000', 0, 'Inactive', '');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `payment_id` int(11) NOT NULL,
  `pay_invoice` varchar(11) NOT NULL,
  `pay_bank_sleep` int(11) NOT NULL,
  `payment_student` int(11) NOT NULL,
  `payment_promotion` int(11) NOT NULL,
  `pay_amount_usd` decimal(16,2) NOT NULL,
  `pay_amount_local` decimal(16,2) NOT NULL,
  `payment_country` int(11) NOT NULL,
  `payment_region` int(11) NOT NULL,
  `payment_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `invoice_payments`
--

INSERT INTO `invoice_payments` (`payment_id`, `pay_invoice`, `pay_bank_sleep`, `payment_student`, `payment_promotion`, `pay_amount_usd`, `pay_amount_local`, `payment_country`, `payment_region`, `payment_status`) VALUES
(9, '3', 1, 2, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(10, '4', 1, 3, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(11, '2', 1, 1, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(12, '5', 1, 4, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(13, '6', 1, 5, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(14, '7', 1, 6, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(15, '8', 1, 7, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(16, '9', 1, 8, 1, '100.00', '130211.00', 41, 3, 'Paid'),
(17, '10', 1, 9, 1, '100.00', '130211.00', 41, 3, 'Paid');

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
  `topic_status` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `learning_topics`
--

INSERT INTO `learning_topics` (`topic_id`, `topic_course`, `topic_week`, `topic_title`, `topic_certification`, `topic_status`) VALUES
(1, 1, 1, 'Voltage, Current, and Resistance', 1, 'Active'),
(2, 1, 1, 'Overview of Electricity and Circuits', 1, 'Active'),
(3, 1, 2, 'Basics of microcontrollers and their role in robotics', 1, 'Active'),
(4, 1, 2, 'Sensors and actuators interfacing', 1, 'Active'),
(5, 1, 3, 'Battery Connection Experiment', 1, 'Active'),
(6, 1, 3, 'Battery Connection Experiment', 1, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `learning_weeks`
--

CREATE TABLE `learning_weeks` (
  `week_id` int(11) NOT NULL,
  `week_description` varchar(100) NOT NULL,
  `week_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `learning_weeks`
--

INSERT INTO `learning_weeks` (`week_id`, `week_description`, `week_status`) VALUES
(1, 'Week 1', 'Active'),
(2, 'Week 2', 'Active'),
(3, 'Week 3', 'Active'),
(4, 'Week 4', 'Active'),
(5, 'Week 5', 'Active'),
(6, 'Week 6', 'Active'),
(7, 'Week 7', 'Active'),
(8, 'Week 8', 'Active'),
(9, 'Week 9', 'Active'),
(10, 'Week 10', 'Active'),
(11, 'Week 11', 'Active'),
(12, 'Week 12', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `login_atempt_error`
--

CREATE TABLE `login_atempt_error` (
  `Attempt_id` int(11) NOT NULL,
  `Attempt_user_ref` int(11) NOT NULL,
  `Attempt_user_email` varchar(65) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Attempt_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regions_table`
--

CREATE TABLE `regions_table` (
  `region_id` int(11) NOT NULL,
  `region_name` text NOT NULL,
  `region_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `regions_table`
--

INSERT INTO `regions_table` (`region_id`, `region_name`, `region_status`) VALUES
(1, 'Northern Africa', 'Inactive'),
(2, 'Sub-Saharan Africa', 'Inactive'),
(3, 'Eastern Africa', 'Active'),
(4, 'Middle Africa', 'Active'),
(5, 'Southern Africa', 'Inactive'),
(6, 'Western Africa', 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL,
  `school_name` text NOT NULL,
  `school_abreviation` text NOT NULL,
  `country_ref` int(11) NOT NULL,
  `school_region` int(11) NOT NULL,
  `school_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_name`, `school_abreviation`, `country_ref`, `school_region`, `school_status`) VALUES
(1, 'BLIS Makerspace', 'asa', 41, 4, 'Active'),
(2, 'Pythagore School Complex ', 'asdf', 41, 4, 'Active'),
(3, 'COSBIE', 'COSBIE', 41, 4, 'Active'),
(4, 'Lady Bird', 'Lady Bird', 41, 4, 'Active'),
(5, 'BLIS RWANDA', 'BLIS', 186, 3, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `school_classes`
--

CREATE TABLE `school_classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(67) NOT NULL,
  `class_level` int(11) NOT NULL,
  `class_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `school_classes`
--

INSERT INTO `school_classes` (`class_id`, `class_name`, `class_level`, `class_status`) VALUES
(1, 'Primary I', 1, 'Active'),
(2, 'Primary II', 1, 'Active'),
(3, 'Primary III', 1, 'Active'),
(4, 'Primary IV', 1, 'Active'),
(5, 'Primary V', 1, 'Active'),
(6, 'Primary VI', 1, 'Active'),
(7, 'Year I', 2, 'Active'),
(8, 'Year II', 2, 'Active'),
(9, 'Year III', 2, 'Active'),
(10, 'Year VI', 2, 'Active'),
(11, 'Year V', 2, 'Active'),
(12, 'Year VI', 2, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `school_class_rooms`
--

CREATE TABLE `school_class_rooms` (
  `room_id` int(11) NOT NULL,
  `room_class` int(11) NOT NULL,
  `room_level` int(11) NOT NULL,
  `room_school` int(11) NOT NULL,
  `room_students` int(11) NOT NULL,
  `room_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `school_class_rooms`
--

INSERT INTO `school_class_rooms` (`room_id`, `room_class`, `room_level`, `room_school`, `room_students`, `room_status`) VALUES
(1, 1, 1, 1, 0, 'Active'),
(2, 2, 1, 1, 0, 'Active'),
(3, 3, 1, 1, 0, 'Active'),
(4, 4, 1, 1, 0, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `school_levels`
--

CREATE TABLE `school_levels` (
  `level_id` int(11) NOT NULL,
  `level_name` text NOT NULL,
  `level_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `school_levels`
--

INSERT INTO `school_levels` (`level_id`, `level_name`, `level_status`) VALUES
(1, 'Primary', 'Active'),
(2, 'Secondary', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `school_programs`
--

CREATE TABLE `school_programs` (
  `program_id` int(11) NOT NULL,
  `program_name` text NOT NULL,
  `program_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings_table`
--

CREATE TABLE `settings_table` (
  `setting_id` int(11) NOT NULL,
  `setting_login_attempts` int(11) NOT NULL,
  `setting_maxrole_no` int(11) NOT NULL,
  `setting_email` varchar(123) NOT NULL,
  `setting_email_passwd` text NOT NULL,
  `setting_timeout` decimal(11,2) NOT NULL,
  `setting_min_year` int(11) NOT NULL,
  `setting_pay_min` decimal(16,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings_table`
--

INSERT INTO `settings_table` (`setting_id`, `setting_login_attempts`, `setting_maxrole_no`, `setting_email`, `setting_email_passwd`, `setting_timeout`, `setting_min_year`, `setting_pay_min`) VALUES
(1, 5, 6, '123456', 'yunusumika@gmail.com', '300.00', 6, '0.500000');

-- --------------------------------------------------------

--
-- Table structure for table `students_invoice`
--

CREATE TABLE `students_invoice` (
  `invoice_id` int(11) NOT NULL,
  `invoice_student` int(11) NOT NULL,
  `invoice_certificate` int(11) NOT NULL,
  `invoice_promotion` int(11) NOT NULL,
  `ivoice_region` int(11) NOT NULL,
  `invoice_country` int(11) NOT NULL,
  `invoice_school` int(11) NOT NULL,
  `invoice_usd` decimal(16,6) NOT NULL,
  `invoice_local` decimal(16,6) NOT NULL,
  `invoice_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students_invoice`
--

INSERT INTO `students_invoice` (`invoice_id`, `invoice_student`, `invoice_certificate`, `invoice_promotion`, `ivoice_region`, `invoice_country`, `invoice_school`, `invoice_usd`, `invoice_local`, `invoice_status`) VALUES
(2, 1, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(3, 2, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(4, 3, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(5, 4, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(6, 5, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(7, 6, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(8, 7, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(9, 8, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(10, 9, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(11, 10, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(12, 11, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(13, 12, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid'),
(14, 13, 1, 1, 3, 186, 5, '100.000000', '130211.000000', 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `students_promotion`
--

CREATE TABLE `students_promotion` (
  `promotion_id` int(11) NOT NULL,
  `promotion_name` varchar(100) NOT NULL,
  `promotion_certification` int(11) NOT NULL,
  `promotion_pay_usd` decimal(10,2) NOT NULL,
  `promotion_pay_local` decimal(10,2) NOT NULL,
  `promotion_from` date DEFAULT NULL,
  `promotion_to` date DEFAULT NULL,
  `promotion_year` year(4) NOT NULL,
  `promotion_region` int(11) NOT NULL,
  `promotion_country` int(11) NOT NULL,
  `promotion_school` int(11) NOT NULL,
  `promotion_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students_promotion`
--

INSERT INTO `students_promotion` (`promotion_id`, `promotion_name`, `promotion_certification`, `promotion_pay_usd`, `promotion_pay_local`, `promotion_from`, `promotion_to`, `promotion_year`, `promotion_region`, `promotion_country`, `promotion_school`, `promotion_status`) VALUES
(1, '', 1, '100.00', '130211.00', '2024-08-03', '2024-08-03', 2024, 3, 186, 5, 'Active'),
(6, '', 1, '75.00', '45076.95', '2024-08-04', '2025-08-04', 2024, 4, 41, 1, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `student_list`
--

CREATE TABLE `student_list` (
  `student_id` int(11) NOT NULL,
  `student_first_name` varchar(100) NOT NULL,
  `student_last_name` varchar(100) NOT NULL,
  `student_dob` date NOT NULL,
  `student_gender` varchar(68) NOT NULL,
  `student_class` int(11) NOT NULL,
  `student_level` int(11) NOT NULL,
  `student_school` int(11) NOT NULL,
  `student_country` int(11) NOT NULL,
  `student_region` int(11) NOT NULL,
  `student_contact` text NOT NULL,
  `student_status` varchar(23) NOT NULL,
  `student_profile` text NOT NULL,
  `student_promotion` int(11) NOT NULL,
  `student_regno` varchar(32) NOT NULL,
  `student_password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student_list`
--

INSERT INTO `student_list` (`student_id`, `student_first_name`, `student_last_name`, `student_dob`, `student_gender`, `student_class`, `student_level`, `student_school`, `student_country`, `student_region`, `student_contact`, `student_status`, `student_profile`, `student_promotion`, `student_regno`, `student_password`) VALUES
(1, 'Mika1', 'Mika1', '2000-01-10', 'Male', 2, 1, 1, 41, 0, 'Mika Detail 5', 'Active', 'Student_profiles/BLIS202400001.jpg', 1, 'BLIS/2024/00001', 'e10adc3949ba59abbe56e057f20f883e'),
(2, 'Mika2', 'Yunusu2', '1984-01-11', 'Female', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'Student_profiles/BLIS202400002.jpg', 1, 'BLIS/2024/00002', 'e10adc3949ba59abbe56e057f20f883e'),
(3, 'Mika3', 'Yunusu3', '1984-01-12', 'Male', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile3', 1, 'BLIS/2024/00003', 'e10adc3949ba59abbe56e057f20f883e'),
(4, 'Mika4', 'Yunusu4', '1984-01-13', 'Female', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile4', 1, 'BLIS/2024/00004', 'e10adc3949ba59abbe56e057f20f883e'),
(5, 'Mika5', 'Yunusu5', '1984-01-14', 'Male', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile5', 1, 'BLIS/2024/00005', 'e10adc3949ba59abbe56e057f20f883e'),
(6, 'Mika6', 'Yunusu6', '1984-01-15', 'Female', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile6', 1, 'BLIS/2024/00006', 'e10adc3949ba59abbe56e057f20f883e'),
(7, 'Mika7', 'Yunusu7', '1984-01-16', 'Male', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile7', 1, 'BLIS/2024/00007', 'e10adc3949ba59abbe56e057f20f883e'),
(8, 'Mika8', 'Yunusu8', '1984-01-17', 'Female', 2, 1, 1, 0, 0, 'Mika Detail 5', 'Active', 'profile8', 1, 'BLIS/2024/00008', 'e10adc3949ba59abbe56e057f20f883e'),
(9, 'Mika9', 'Yunusu9', '1984-01-18', 'Male', 2, 1, 1, 0, 0, 'Mika Detail 6', 'Active', 'profiles/LOGO BLIS (2).pdf', 1, 'BLIS/2024/00009', 'e10adc3949ba59abbe56e057f20f883e'),
(10, 'Mika10', 'Yunusu10', '1984-01-10', 'Male', 2, 1, 1, 0, 0, 'Mika Detail10', 'Active', 'profile10', 1, 'BLIS/2024/00010', 'e10adc3949ba59abbe56e057f20f883e'),
(11, 'Uwera', 'Clarice', '2024-07-23', 'Female', 2, 1, 1, 0, 0, '07888888', 'Active', '', 1, 'BG/2024/00001', 'e10adc3949ba59abbe56e057f20f883e'),
(12, 'Muka kalisa', 'Charlotte', '2024-07-12', 'Female', 1, 1, 1, 0, 0, '07888888', 'Active', '', 1, 'BG/2024/00002', 'e10adc3949ba59abbe56e057f20f883e'),
(13, 'Uwera1213', 'Charlotte', '2024-07-25', 'Male', 2, 1, 1, 0, 0, '07888888', 'Active', '', 1, 'BG/2024/00003', 'e10adc3949ba59abbe56e057f20f883e');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `phone_number` varchar(100) NOT NULL,
  `password` text NOT NULL,
  `status` varchar(25) NOT NULL,
  `access_level` int(10) NOT NULL,
  `school_ref` int(11) NOT NULL,
  `user_country` int(11) NOT NULL,
  `user_region` int(11) NOT NULL,
  `user_group_ref` int(11) NOT NULL,
  `Password_tocken` varchar(255) NOT NULL,
  `user_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email_address`, `phone_number`, `password`, `status`, `access_level`, `school_ref`, `user_country`, `user_region`, `user_group_ref`, `Password_tocken`, `user_image`) VALUES
(1, 'Mika', 'Yunusu', 'yunusumika@gmail.com', '+250782717557', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 5, 1, 186, 3, 0, '', 'profiles/Mmika.jpg'),
(2, 'Che', 'Emmanuel', 'chenesteroi@yahoo.com', '+237759572651', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 4, 1, 41, 3, 0, '', 'profiles/Emmanuel.jpg'),
(3, 'Kibuye', 'Mukwende', 'mukwende@gmail.com', '+250788569512', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 4, 1, 0, 0, 0, '', 'Auth/profiles/Emmanuel.jpg'),
(4, '', '', '', '+237674751055', '', '', 0, 0, 0, 0, 0, '', ''),
(5, '', '', '', '+237699135330', '', '', 0, 0, 0, 0, 0, '', ''),
(6, '', '', '', '+237695809525', '', '', 0, 0, 0, 0, 0, '', 'xAAAAAnb vb'),
(7, '', '', '', '+237680387226', '', '', 0, 0, 0, 0, 0, '', ''),
(8, '', '', '', '+237657595547', '', '', 0, 0, 0, 0, 0, '', ''),
(9, '', '', '', '+23774279109', '', '', 0, 0, 0, 0, 0, '', ''),
(10, '', '', '', '+23775957265', '', '', 0, 0, 0, 0, 0, '', ''),
(11, '', '', '', '+237677980934', '', '', 0, 0, 0, 0, 0, '', ''),
(12, '', '', '', '+237693631740', '', '', 0, 0, 0, 0, 0, '', ''),
(13, '', '', '', '+237621058432', '', '', 0, 0, 0, 0, 0, '', ''),
(14, '', '', '', '+23772141999', '', '', 0, 0, 0, 0, 0, '', ''),
(15, '', '', '', '+237696495795', '', '', 0, 0, 0, 0, 0, '', ''),
(16, '', '', '', '+237678007095', '', '', 0, 0, 0, 0, 0, '', ''),
(17, '', '', '', '+23777486741', '', '', 0, 0, 0, 0, 0, '', ''),
(18, '', '', '', '+2347034721040', '', '', 0, 0, 0, 0, 0, '', ''),
(19, '', '', '', '+237657771413', '', '', 0, 0, 0, 0, 0, '', ''),
(20, '', '', '', '+23791885411', '', '', 0, 0, 0, 0, 0, '', ''),
(21, '', '', '', '+237678969414', '', '', 0, 0, 0, 0, 0, '', ''),
(22, '', '', '', '+237673593875', '', '', 0, 0, 0, 0, 0, '', ''),
(23, '', '', '', '+237696957397', '', '', 0, 0, 0, 0, 0, '', ''),
(24, '', '', '', '+237679953770', '', '', 0, 0, 0, 0, 0, '', ''),
(25, '', '', '', '+237691515266', '', '', 0, 0, 0, 0, 0, '', ''),
(26, '', '', '', '+237672367925', '', '', 0, 0, 0, 0, 0, '', ''),
(27, '', '', '', '+237680175803', '', '', 0, 0, 0, 0, 0, '', ''),
(28, '', '', '', '+237658308193', '', '', 0, 0, 0, 0, 0, '', ''),
(29, '', '', '', '+237690358296', '', '', 0, 0, 0, 0, 0, '', ''),
(30, '', '', '', '+23799697342', '', '', 0, 0, 0, 0, 0, '', ''),
(31, '', '', '', '+23772119777', '', '', 0, 0, 0, 0, 0, '', ''),
(32, '', '', '', '+23775534633', '', '', 0, 0, 0, 0, 0, '', ''),
(33, '', '', '', '+237652975074', '', '', 0, 0, 0, 0, 0, '', ''),
(34, '', '', '', '+237653761681', '', '', 0, 0, 0, 0, 0, '', ''),
(35, '', '', '', '+23777352402', '', '', 0, 0, 0, 0, 0, '', ''),
(36, '', '', '', '+237674849465', '', '', 0, 0, 0, 0, 0, '', ''),
(37, '', '', '', '+237675405507', '', '', 0, 0, 0, 0, 0, '', ''),
(38, '', '', '', '+237653943037', '', '', 0, 0, 0, 0, 0, '', ''),
(39, '', '', '', '+23774500965', '', '', 0, 0, 0, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `user_group_id` int(11) NOT NULL,
  `user_group_name` varchar(65) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_group_operator` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_permission`
--

CREATE TABLE `user_permission` (
  `permissio_id` int(11) NOT NULL,
  `permission` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissio_location` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `per_status` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_tag` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_permission`
--

INSERT INTO `user_permission` (`permissio_id`, `permission`, `permissio_location`, `per_status`, `icon_tag`) VALUES
(1, 'Developer', 'Developer', 'Active', 'icon-wrench'),
(2, 'Global Technical Manager', 'GTM', 'Active', 'icon-user'),
(3, 'Country Technical Manager', 'CTM', 'Active', 'icon-user'),
(4, 'School Facilitators', 'SF', 'Active', 'icon-user'),
(5, 'Business Coordination Manager', 'BCM', 'Active', 'icon-user'),
(6, 'Region Manager', 'RM', 'Active', 'icon-user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_user_permission`
--
ALTER TABLE `active_user_permission`
  ADD PRIMARY KEY (`active_permission_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`bank_id`),
  ADD KEY `bank_region` (`bank_region`),
  ADD KEY `bank_country` (`bank_country`);

--
-- Indexes for table `bank_sleeps`
--
ALTER TABLE `bank_sleeps`
  ADD PRIMARY KEY (`sleep_id`),
  ADD KEY `sleep_bank` (`sleep_bank`),
  ADD KEY `sleep_school` (`sleep_school`),
  ADD KEY `sleep_country` (`sleep_country`),
  ADD KEY `sleep_region` (`sleep_region`);

--
-- Indexes for table `certifications`
--
ALTER TABLE `certifications`
  ADD PRIMARY KEY (`certification_id`);

--
-- Indexes for table `certification_courses`
--
ALTER TABLE `certification_courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `payment_student` (`payment_student`),
  ADD KEY `payment_promotion` (`payment_promotion`),
  ADD KEY `payment_country` (`payment_country`),
  ADD KEY `payment_region` (`payment_region`),
  ADD KEY `pay_invoice` (`pay_invoice`),
  ADD KEY `pay_bank_sleep` (`pay_bank_sleep`);

--
-- Indexes for table `learning_topics`
--
ALTER TABLE `learning_topics`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `topic_certification` (`topic_certification`),
  ADD KEY `topic_week` (`topic_week`),
  ADD KEY `topic_course` (`topic_course`);

--
-- Indexes for table `learning_weeks`
--
ALTER TABLE `learning_weeks`
  ADD PRIMARY KEY (`week_id`);

--
-- Indexes for table `login_atempt_error`
--
ALTER TABLE `login_atempt_error`
  ADD PRIMARY KEY (`Attempt_id`);

--
-- Indexes for table `regions_table`
--
ALTER TABLE `regions_table`
  ADD PRIMARY KEY (`region_id`);

--
-- Indexes for table `schools`
--
ALTER TABLE `schools`
  ADD PRIMARY KEY (`school_id`),
  ADD KEY `scountry_ref` (`country_ref`);

--
-- Indexes for table `school_classes`
--
ALTER TABLE `school_classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `school_class_rooms`
--
ALTER TABLE `school_class_rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `school_levels`
--
ALTER TABLE `school_levels`
  ADD PRIMARY KEY (`level_id`);

--
-- Indexes for table `school_programs`
--
ALTER TABLE `school_programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `settings_table`
--
ALTER TABLE `settings_table`
  ADD PRIMARY KEY (`setting_id`);

--
-- Indexes for table `students_invoice`
--
ALTER TABLE `students_invoice`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `invoice_student` (`invoice_student`),
  ADD KEY `invoice_certificate` (`invoice_certificate`),
  ADD KEY `invoice_promotion` (`invoice_promotion`),
  ADD KEY `ivoice_region` (`ivoice_region`),
  ADD KEY `invoice_country` (`invoice_country`),
  ADD KEY `invoice_school` (`invoice_school`);

--
-- Indexes for table `students_promotion`
--
ALTER TABLE `students_promotion`
  ADD PRIMARY KEY (`promotion_id`),
  ADD KEY `promotion_region` (`promotion_region`),
  ADD KEY `promotion_country` (`promotion_country`),
  ADD KEY `promotion_school` (`promotion_school`);

--
-- Indexes for table `student_list`
--
ALTER TABLE `student_list`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `student_level` (`student_level`),
  ADD KEY `student_class` (`student_class`),
  ADD KEY `student_school` (`student_school`),
  ADD KEY `student_country` (`student_country`),
  ADD KEY `student_region` (`student_region`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_group_ref` (`user_group_ref`),
  ADD KEY `access_level` (`access_level`),
  ADD KEY `company_ref` (`school_ref`),
  ADD KEY `user_region` (`user_region`),
  ADD KEY `user_country` (`user_country`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`user_group_id`);

--
-- Indexes for table `user_permission`
--
ALTER TABLE `user_permission`
  ADD PRIMARY KEY (`permissio_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_user_permission`
--
ALTER TABLE `active_user_permission`
  MODIFY `active_permission_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `bank_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `bank_sleeps`
--
ALTER TABLE `bank_sleeps`
  MODIFY `sleep_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `certifications`
--
ALTER TABLE `certifications`
  MODIFY `certification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `certification_courses`
--
ALTER TABLE `certification_courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `learning_topics`
--
ALTER TABLE `learning_topics`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `learning_weeks`
--
ALTER TABLE `learning_weeks`
  MODIFY `week_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `login_atempt_error`
--
ALTER TABLE `login_atempt_error`
  MODIFY `Attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions_table`
--
ALTER TABLE `regions_table`
  MODIFY `region_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `school_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `school_classes`
--
ALTER TABLE `school_classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `school_class_rooms`
--
ALTER TABLE `school_class_rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `school_levels`
--
ALTER TABLE `school_levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `school_programs`
--
ALTER TABLE `school_programs`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings_table`
--
ALTER TABLE `settings_table`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students_invoice`
--
ALTER TABLE `students_invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `students_promotion`
--
ALTER TABLE `students_promotion`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `student_list`
--
ALTER TABLE `student_list`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `permissio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
