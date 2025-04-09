-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2025 at 11:47 AM
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
-- Database: `letran_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE quizzes (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  question text NOT NULL,
  option_a varchar(255) NOT NULL,
  option_b varchar(255) NOT NULL,
  option_c varchar(255) NOT NULL,
  option_d varchar(255) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  correct_option varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE results (
  id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  quiz_id int(11) DEFAULT NULL,
  user_answer char(1) DEFAULT NULL,
  is_correct tinyint(1) DEFAULT NULL,
  timestamp timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE scores (
  id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  score int(11) NOT NULL,
  timestamp timestamp NOT NULL DEFAULT current_timestamp(),
  is_read tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_videos`
--

CREATE TABLE training_videos (
  id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  description text NOT NULL,
  file_path varchar(255) NOT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  category varchar(100) DEFAULT NULL,
  thumbnail varchar(255) DEFAULT NULL,
  duration varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_videos`
--

-- INSERT INTO training_videos (id, title, description, file_path, created_at, category, thumbnail, duration) VALUES
-- Add your data here if you have any, e.g.:
-- (1, 'Sample Video', 'This is a sample training video', '/videos/sample.mp4', '2025-04-04 11:47:00', 'Training', '/thumbnails/sample.jpg', '00:05:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  id int(11) NOT NULL,
  fullname varchar(255) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  role enum('User','Admin') NOT NULL,
  profile_picture varchar(255) DEFAULT '../assets/images/profile-placeholder.png',
  last_login datetime DEFAULT NULL,
  status enum('online','offline') DEFAULT 'offline',
  is_online tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

-- INSERT INTO users (id, fullname, email, password, role, profile_picture, last_login) VALUES
-- Add your data here if you have any, e.g.:
-- (1, 'John Doe', 'john@example.com', 'hashed_password_here', 'Admin', '../assets/images/profile-placeholder.png', '2025-04-04 11:47:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quizzes`
--
ALTER TABLE quizzes
  ADD PRIMARY KEY (id);

--
-- Indexes for table `results`
--
ALTER TABLE results
  ADD PRIMARY KEY (id);

--
-- Indexes for table `scores`
--
ALTER TABLE scores
  ADD PRIMARY KEY (id);

--
-- Indexes for table `training_videos`
--
ALTER TABLE training_videos
  ADD PRIMARY KEY (id);

--
-- Indexes for table `users`
--
ALTER TABLE users
  ADD PRIMARY KEY (id);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE quizzes
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE results
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE scores
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_videos`
--
ALTER TABLE training_videos
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE users
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;