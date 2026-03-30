-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2024 at 08:50 AM
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
(1, 1, 1, 'Active'),
(2, 2, 1, ''),
(7, 4, 3, 'Active'),
(8, 4, 4, 'Active'),
(9, 4, 5, 'Active'),
(10, 4, 6, 'Active'),
(11, 4, 7, 'Active'),
(20, 1, 9, ''),
(21, 2, 9, ''),
(23, 4, 9, 'Active'),
(28, 3, 9, ''),
(29, 3, 2, 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `Country_name` varchar(65) COLLATE utf8_unicode_ci NOT NULL,
  `Country_status` varchar(23) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `Country_name`, `Country_status`) VALUES
(1, 'Afghan afghani', 'Active'),
(2, 'Akrotiri and Dhekelia (UK)', 'Inactive'),
(3, 'Aland Islands (Finland)', 'Inactive'),
(4, 'Albania', 'Inactive'),
(5, 'Algeria', 'Inactive'),
(6, 'American Samoa (USA)', 'Inactive'),
(7, 'Andorra', 'Inactive'),
(8, 'Angola', 'Inactive'),
(9, 'Anguilla (UK)', 'Inactive'),
(10, 'Antigua and Barbuda', 'Inactive'),
(11, 'Argentina', 'Inactive'),
(12, 'Armenia', 'Inactive'),
(13, 'Aruba (Netherlands)', 'Inactive'),
(14, 'Ascension Island (UK)', 'Inactive'),
(15, 'Australia', 'Inactive'),
(16, 'Austria', 'Inactive'),
(17, 'Azerbaijan', 'Inactive'),
(18, 'Bahamas', 'Inactive'),
(19, 'Bahrain', 'Inactive'),
(20, 'Bangladesh', 'Inactive'),
(21, 'Barbados', 'Inactive'),
(22, 'Belarus', 'Inactive'),
(23, 'Belgium', 'Inactive'),
(24, 'Belize', 'Inactive'),
(25, 'Benin', 'Inactive'),
(26, 'Bermuda (UK)', 'Inactive'),
(27, 'Bhutan', 'Inactive'),
(28, 'Bolivia', 'Inactive'),
(29, 'Bonaire (Netherlands)', 'Inactive'),
(30, 'Bosnia and Herzegovina', 'Inactive'),
(31, 'Botswana', 'Inactive'),
(32, 'Brazil', 'Inactive'),
(33, 'British Indian Ocean Territory (UK)', 'Inactive'),
(34, 'British Virgin Islands (UK)', 'Inactive'),
(35, 'Brunei', 'Inactive'),
(36, 'Bulgaria', 'Inactive'),
(37, 'Burkina Faso', 'Inactive'),
(38, 'Burundi', 'Active'),
(39, 'Cabo Verde', 'Inactive'),
(40, 'Cambodia', 'Inactive'),
(41, 'Cameroon', 'Active'),
(42, 'Canada', 'Inactive'),
(43, 'Caribbean Netherlands (Netherlands)', 'Inactive'),
(44, 'Cayman Islands (UK)', 'Inactive'),
(45, 'Central African Republic', 'Inactive'),
(46, 'Chad', 'Inactive'),
(47, 'Chatham Islands (New Zealand)', 'Inactive'),
(48, 'Chile', 'Inactive'),
(49, 'China', 'Inactive'),
(50, 'Christmas Island (Australia)', 'Inactive'),
(51, 'Cocos (Keeling) Islands (Australia)', 'Inactive'),
(52, 'Colombia', 'Inactive'),
(53, 'Comoros', 'Inactive'),
(54, 'Congo, Democratic Republic of the', 'Inactive'),
(55, 'Congo, Republic of the', 'Inactive'),
(56, 'Cook Islands (New Zealand)', 'Inactive'),
(57, 'Costa Rica', 'Inactive'),
(58, 'Cote d\'Ivoire', 'Inactive'),
(59, 'Croatia', 'Inactive'),
(60, 'Cuba', 'Inactive'),
(61, 'Curacao (Netherlands)', 'Inactive'),
(62, 'Cyprus', 'Inactive'),
(63, 'Czechia', 'Inactive'),
(64, 'Denmark', 'Inactive'),
(65, 'Djibouti', 'Inactive'),
(66, 'Dominica', 'Inactive'),
(67, 'Dominican Republic', 'Inactive'),
(68, 'Ecuador', 'Inactive'),
(69, 'Egypt', 'Inactive'),
(70, 'El Salvador', 'Inactive'),
(71, 'Equatorial Guinea', 'Inactive'),
(72, 'Eritrea', 'Inactive'),
(73, 'Estonia', 'Inactive'),
(74, 'Eswatini (formerly Swaziland)', 'Inactive'),
(75, 'Ethiopia', 'Inactive'),
(76, 'Falkland Islands (UK)', 'Inactive'),
(77, 'Faroe Islands (Denmark)', 'Inactive'),
(78, 'Fiji', 'Inactive'),
(79, 'Finland', 'Inactive'),
(80, 'France', 'Inactive'),
(81, 'French Guiana (France)', 'Inactive'),
(82, 'French Polynesia (France)', 'Inactive'),
(83, 'Gabon', 'Inactive'),
(84, 'Gambia', 'Inactive'),
(85, 'Georgia', 'Inactive'),
(86, 'Germany', 'Inactive'),
(87, 'Ghana', 'Inactive'),
(88, 'Gibraltar (UK)', 'Inactive'),
(89, 'Greece', 'Inactive'),
(90, 'Greenland (Denmark)', 'Inactive'),
(91, 'Grenada', 'Inactive'),
(92, 'Guadeloupe (France)', 'Inactive'),
(93, 'Guam (USA)', 'Inactive'),
(94, 'Guatemala', 'Inactive'),
(95, 'Guernsey (UK)', 'Inactive'),
(96, 'Guinea', 'Inactive'),
(97, 'Guinea-Bissau', 'Inactive'),
(98, 'Guyana', 'Inactive'),
(99, 'Haiti', 'Inactive'),
(100, 'Honduras', 'Inactive'),
(101, 'Hong Kong (China)', 'Inactive'),
(102, 'Hungary', 'Inactive'),
(103, 'Iceland', 'Inactive'),
(104, 'India', 'Inactive'),
(105, 'Indonesia', 'Inactive'),
(106, 'International Monetary Fund (IMF)', 'Inactive'),
(107, 'Iran', 'Inactive'),
(108, 'Iraq', 'Inactive'),
(109, 'Ireland', 'Inactive'),
(110, 'Isle of Man (UK)', 'Inactive'),
(111, 'Israel', 'Inactive'),
(112, 'Italy', 'Inactive'),
(113, 'Jamaica', 'Inactive'),
(114, 'Japan', 'Inactive'),
(115, 'Jersey (UK)', 'Inactive'),
(116, 'Jordan', 'Inactive'),
(117, 'Kazakhstan', 'Inactive'),
(118, 'Kenya', 'Inactive'),
(119, 'Kiribati', 'Inactive'),
(120, 'Kosovo', 'Inactive'),
(121, 'Kuwait', 'Inactive'),
(122, 'Kyrgyzstan', 'Inactive'),
(123, 'Laos', 'Inactive'),
(124, 'Latvia', 'Inactive'),
(125, 'Lebanon', 'Inactive'),
(126, 'Lesotho', 'Inactive'),
(127, 'Liberia', 'Inactive'),
(128, 'Libya', 'Inactive'),
(129, 'Liechtenstein', 'Inactive'),
(130, 'Lithuania', 'Inactive'),
(131, 'Luxembourg', 'Inactive'),
(132, 'Macau (China)', 'Inactive'),
(133, 'Madagascar', 'Inactive'),
(134, 'Malawi', 'Active'),
(135, 'Malaysia', 'Inactive'),
(136, 'Maldives', 'Inactive'),
(137, 'Mali', 'Inactive'),
(138, 'Malta', 'Inactive'),
(139, 'Marshall Islands', 'Inactive'),
(140, 'Martinique (France)', 'Inactive'),
(141, 'Mauritania', 'Inactive'),
(142, 'Mauritius', 'Inactive'),
(143, 'Mayotte (France)', 'Inactive'),
(144, 'Mexico', 'Inactive'),
(145, 'Micronesia', 'Inactive'),
(146, 'Moldova', 'Inactive'),
(147, 'Monaco', 'Inactive'),
(148, 'Mongolia', 'Inactive'),
(149, 'Montenegro', 'Inactive'),
(150, 'Montserrat (UK)', 'Inactive'),
(151, 'Morocco', 'Inactive'),
(152, 'Mozambique', 'Inactive'),
(153, 'Myanmar (formerly Burma)', 'Inactive'),
(154, 'Namibia', 'Inactive'),
(155, 'Nauru', 'Inactive'),
(156, 'Nepal', 'Inactive'),
(157, 'Netherlands', 'Inactive'),
(158, 'New Caledonia (France)', 'Inactive'),
(159, 'New Zealand', 'Inactive'),
(160, 'Nicaragua', 'Inactive'),
(161, 'Niger', 'Inactive'),
(162, 'Nigeria', 'Inactive'),
(163, 'Niue (New Zealand)', 'Inactive'),
(164, 'Norfolk Island (Australia)', 'Inactive'),
(165, 'Northern Mariana Islands (USA)', 'Inactive'),
(166, 'North Korea', 'Inactive'),
(167, 'North Macedonia (formerly Macedonia)', 'Inactive'),
(168, 'Norway', 'Inactive'),
(169, 'Oman', 'Inactive'),
(170, 'Pakistan', 'Inactive'),
(171, 'Palau', 'Inactive'),
(172, 'Palestine', 'Inactive'),
(173, 'Panama', 'Inactive'),
(174, 'Papua New Guinea', 'Inactive'),
(175, 'Paraguay', 'Inactive'),
(176, 'Peru', 'Inactive'),
(177, 'Philippines', 'Inactive'),
(178, 'Pitcairn Islands (UK)', 'Inactive'),
(179, 'Poland', 'Inactive'),
(180, 'Portugal', 'Inactive'),
(181, 'Puerto Rico (USA)', 'Inactive'),
(182, 'Qatar', 'Inactive'),
(183, 'Reunion (France)', 'Inactive'),
(184, 'Romania', 'Inactive'),
(185, 'Russia', 'Inactive'),
(186, 'Rwanda', 'Active'),
(187, 'Saba (Netherlands)', 'Inactive'),
(188, 'Saint Barthelemy (France)', 'Inactive'),
(189, 'Saint Helena (UK)', 'Inactive'),
(190, 'Saint Kitts and Nevis', 'Inactive'),
(191, 'Saint Lucia', 'Inactive'),
(192, 'Saint Martin (France)', 'Inactive'),
(193, 'Saint Pierre and Miquelon (France)', 'Inactive'),
(194, 'Saint Vincent and the Grenadines', 'Inactive'),
(195, 'Samoa', 'Inactive'),
(196, 'San Marino', 'Inactive'),
(197, 'Sao Tome and Principe', 'Inactive'),
(198, 'Saudi Arabia', 'Inactive'),
(199, 'Senegal', 'Inactive'),
(200, 'Serbia', 'Inactive'),
(201, 'Seychelles', 'Inactive'),
(202, 'Sierra Leone', 'Inactive'),
(203, 'Singapore', 'Inactive'),
(204, 'Sint Eustatius (Netherlands)', 'Inactive'),
(205, 'Sint Maarten (Netherlands)', 'Inactive'),
(206, 'Slovakia', 'Inactive'),
(207, 'Slovenia', 'Inactive'),
(208, 'Solomon Islands', 'Inactive'),
(209, 'Somalia', 'Inactive'),
(210, 'South Africa', 'Inactive'),
(211, 'South Georgia Island (UK)', 'Inactive'),
(212, 'South Korea', 'Inactive'),
(213, 'South Sudan', 'Inactive'),
(214, 'Spain', 'Inactive'),
(215, 'Sri Lanka', 'Inactive'),
(216, 'Sudan', 'Inactive'),
(217, 'Suriname', 'Inactive'),
(218, 'Svalbard and Jan Mayen (Norway)', 'Inactive'),
(219, 'Sweden', 'Inactive'),
(220, 'Switzerland', 'Inactive'),
(221, 'Syria', 'Inactive'),
(222, 'Taiwan', 'Inactive'),
(223, 'Tajikistan', 'Inactive'),
(224, 'Tanzania', 'Inactive'),
(225, 'Thailand', 'Inactive'),
(226, 'Timor-Leste', 'Inactive'),
(227, 'Togo', 'Inactive'),
(228, 'Tokelau (New Zealand)', 'Inactive'),
(229, 'Tonga', 'Inactive'),
(230, 'Trinidad and Tobago', 'Inactive'),
(231, 'Tristan da Cunha (UK)', 'Inactive'),
(232, 'Tunisia', 'Inactive'),
(233, 'Turkey', 'Inactive'),
(234, 'Turkmenistan', 'Inactive'),
(235, 'Turks and Caicos Islands (UK)', 'Inactive'),
(236, 'Tuvalu', 'Inactive'),
(237, 'Uganda', 'Inactive'),
(238, 'Ukraine', 'Inactive'),
(239, 'United Arab Emirates', 'Inactive'),
(240, 'United Kingdom', 'Inactive'),
(241, 'United States of America', 'Inactive'),
(242, 'Uruguay', 'Inactive'),
(243, 'US Virgin Islands (USA)', 'Inactive'),
(244, 'Uzbekistan', 'Inactive'),
(245, 'Vanuatu', 'Inactive'),
(246, 'Vatican City (Holy See)', 'Inactive'),
(247, 'Venezuela', 'Inactive'),
(248, 'Vietnam', 'Inactive'),
(249, 'Wake Island (USA)', 'Inactive'),
(250, 'Wallis and Futuna (France)', 'Inactive'),
(251, 'Yemen', 'Inactive'),
(252, 'Zambia', 'Active'),
(253, 'Zimbabwe', 'Inactive');

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
-- Table structure for table `schools`
--

CREATE TABLE `schools` (
  `school_id` int(11) NOT NULL,
  `school_name` text NOT NULL,
  `school_abreviation` text NOT NULL,
  `country_ref` int(11) NOT NULL,
  `school_status` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `schools`
--

INSERT INTO `schools` (`school_id`, `school_name`, `school_abreviation`, `country_ref`, `school_status`) VALUES
(1, 'BLIS Makerspace', 'asa', 41, 'Active'),
(2, 'Pythagore School Complex ', 'asdf', 41, 'Active'),
(3, 'COSBIE', 'COSBIE', 41, 'Active'),
(4, 'Lady Bird', 'Lady Bird', 41, 'Active');

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
  `student_contact` text NOT NULL,
  `student_status` varchar(23) NOT NULL,
  `student_profile` text NOT NULL,
  `student_regno` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student_list`
--

INSERT INTO `student_list` (`student_id`, `student_first_name`, `student_last_name`, `student_dob`, `student_gender`, `student_class`, `student_level`, `student_school`, `student_contact`, `student_status`, `student_profile`, `student_regno`) VALUES
(1, 'First Name', 'Last Name', '1970-01-01', 'SEX', 3, 1, 1, 'contact details', 'Status', 'Profile', 'BLIS/2024/00001'),
(2, 'Mika1', 'Yunusu1', '1984-01-10', 'Male', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile1', 'BLIS/2024/00002'),
(3, 'Mika2', 'Yunusu2', '1984-01-11', 'Female', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile2', 'BLIS/2024/00003'),
(4, 'Mika3', 'Yunusu3', '1984-01-12', 'Male', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile3', 'BLIS/2024/00004'),
(5, 'Mika4', 'Yunusu4', '1984-01-13', 'Female', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile4', 'BLIS/2024/00005'),
(6, 'Mika5', 'Yunusu5', '1984-01-14', 'Male', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile5', 'BLIS/2024/00006'),
(7, 'Mika6', 'Yunusu6', '1984-01-15', 'Female', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile6', 'BLIS/2024/00007'),
(8, 'Mika7', 'Yunusu7', '1984-01-16', 'Male', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile7', 'BLIS/2024/00008'),
(9, 'Mika8', 'Yunusu8', '1984-01-17', 'Female', 3, 1, 1, 'Mika Detail 5', 'Active', 'profile8', 'BLIS/2024/00009'),
(10, 'Mika9', 'Yunusu9', '1984-01-18', 'Male', 3, 1, 1, 'Mika Detail 6', 'Active', 'profile9', 'BLIS/2024/00010');

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
  `user_group_ref` int(11) NOT NULL,
  `Password_tocken` varchar(255) NOT NULL,
  `user_image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `firstname`, `lastname`, `email_address`, `phone_number`, `password`, `status`, `access_level`, `school_ref`, `user_group_ref`, `Password_tocken`, `user_image`) VALUES
(2, '', '', '', '+237695809525', '', 'Active', 1, 1, 0, '', 'xAAAAA'),
(3, 'Uwera', 'Claire', 'uwera@gmail.com', '+237680387226', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 4, 1, 0, '', ''),
(4, '', '', '', '+237657595547', '', 'Active', 4, 0, 0, '', ''),
(5, '', '', '', '+237674751055', '', 'Active', 4, 0, 0, '', ''),
(6, 'Che', 'Emmanuel', 'test@emmanuel.com', '+237759572651', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 2, 1, 0, '', 'adasd'),
(7, '', '', '', '+237699135330', '', 'Active', 4, 0, 0, '', ''),
(8, 'Kibuye', 'Mukwende', 'mukwende@gmail.com', '+250788569512', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 4, 1, 0, '', 'Auth/profiles/Emmanuel.jpg'),
(9, 'Mika', 'Yunusu', 'yunusumika@gmail.com', '+250782717557', 'e10adc3949ba59abbe56e057f20f883e', 'Active', 4, 1, 0, '', 'profiles/mika_profile.jpg'),
(10, '', '', '', '+250782717557', '', 'Active', 3, 1, 0, '', '');

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
(4, 'School Facilitators', 'SF', 'Active', 'icon-user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_user_permission`
--
ALTER TABLE `active_user_permission`
  ADD PRIMARY KEY (`active_permission_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_atempt_error`
--
ALTER TABLE `login_atempt_error`
  ADD PRIMARY KEY (`Attempt_id`);

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
-- Indexes for table `student_list`
--
ALTER TABLE `student_list`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `student_level` (`student_level`),
  ADD KEY `student_class` (`student_class`),
  ADD KEY `student_school` (`student_school`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `user_group_ref` (`user_group_ref`),
  ADD KEY `access_level` (`access_level`),
  ADD KEY `company_ref` (`school_ref`);

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
  MODIFY `active_permission_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=254;

--
-- AUTO_INCREMENT for table `login_atempt_error`
--
ALTER TABLE `login_atempt_error`
  MODIFY `Attempt_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schools`
--
ALTER TABLE `schools`
  MODIFY `school_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `student_list`
--
ALTER TABLE `student_list`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `user_group_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permission`
--
ALTER TABLE `user_permission`
  MODIFY `permissio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
