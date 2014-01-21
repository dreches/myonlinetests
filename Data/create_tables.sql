-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 16, 2013 at 07:22 PM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `seankraf_h4_seankraft_fall2013_biz`
--
CREATE DATABASE IF NOT EXISTS `seankraf_h4_seankraft_fall2013_biz` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `seankraf_h4_seankraft_fall2013_biz`;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;



-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE IF NOT EXISTS `answers` (
  `answer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(10) unsigned DEFAULT NULL,
  `answer_text` varchar(200) DEFAULT NULL,
  `answer_order` int(10) unsigned DEFAULT NULL,
  `correct` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;



-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) unsigned DEFAULT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`job_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;


-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE IF NOT EXISTS `questions` (
  `question_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_order` int(10) unsigned NOT NULL,
  `test_id` int(10) unsigned NOT NULL,
  `created_by_user_id` int(10) unsigned NOT NULL,
  `question_text` varchar(2000) DEFAULT NULL,
  `question_type_id` int(10) unsigned DEFAULT NULL,
  `question_image` blob,
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL,
  `all_or_none` tinyint(1) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `questions`
--


-- --------------------------------------------------------

--
-- Table structure for table `question_types`
--

CREATE TABLE IF NOT EXISTS `question_types` (
  `question_type_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question_type_descr` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`question_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `question_types`
--

INSERT INTO `question_types` (`question_type_id`, `question_type_descr`) VALUES
(1, 'Choose all correct answers'),
(2, 'Choose single correct answer'),
(3, 'True/False'),
(4, 'Essay Question');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE IF NOT EXISTS `tests` (
  `test_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(10) NOT NULL,
  `copied_from_test_id` int(10) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_descr` varchar(200) DEFAULT NULL,
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `test_year` int(10) unsigned DEFAULT NULL,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created_on_dt` datetime DEFAULT NULL,
  `last_updated_dt` datetime DEFAULT NULL,
  `minutes_to_complete` int(10) unsigned DEFAULT NULL,
  `passing_grade` int(10) unsigned DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `deleted_date` datetime DEFAULT NULL,
  `test_category` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`test_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `testtaker_staging`
--

CREATE TABLE IF NOT EXISTS `testtaker_staging` (
  `testtaker_staging_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_by_user_id` int(10) unsigned DEFAULT NULL,
  `created` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`testtaker_staging_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `testtaker_staging_rows`
--

CREATE TABLE IF NOT EXISTS `testtaker_staging_rows` (
  `testtaker_staging_row_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `testtaker_staging_id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `person_id` varchar(100) DEFAULT NULL,
  `issue_text` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`testtaker_staging_row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `test_assign_status`
--

CREATE TABLE IF NOT EXISTS `test_assign_status` (
  `test_assign_status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `test_assign_status_descr` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`test_assign_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `test_assign_status`
--

INSERT INTO `test_assign_status` (`test_assign_status_id`, `test_assign_status_descr`) VALUES
(1, 'Assigned'),
(2, 'Started'),
(3, 'Finished'),
(4, 'Error');

-- --------------------------------------------------------

--
-- Table structure for table `test_assign_user`
--

CREATE TABLE IF NOT EXISTS `test_assign_user` (
  `test_assign_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `test_assign_status_id` int(10) unsigned DEFAULT NULL,
  `assigned_by_user_id` int(10) unsigned DEFAULT NULL,
  `assigned_on_dt` int(11) DEFAULT NULL,
  `due_on_dt` int(11) DEFAULT NULL,
  PRIMARY KEY (`test_assign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;



-- --------------------------------------------------------

--
-- Table structure for table `test_instance`
--

CREATE TABLE IF NOT EXISTS `test_instance` (
  `test_instance_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `test_assign_id` int(10) unsigned DEFAULT NULL,
  `start_dt` datetime DEFAULT NULL,
  `finish_dt` datetime DEFAULT NULL,
  `grade` int(10) unsigned DEFAULT NULL,
  `graded` tinyint(1) DEFAULT NULL,
  `seconds_elapsed` int(10) unsigned DEFAULT NULL,
  `review_override_grade` int(10) unsigned DEFAULT NULL,
  `review_override_user_id` int(10) unsigned DEFAULT NULL,
  `review_override_comment` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`test_instance_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=39 ;



-- --------------------------------------------------------

--
-- Table structure for table `test_instance_answer`
--

CREATE TABLE IF NOT EXISTS `test_instance_answer` (
  `test_instance_id` int(10) unsigned NOT NULL,
  `question_id` int(10) unsigned NOT NULL,
  `answer_id` int(10) unsigned DEFAULT NULL,
  `answer_text` varchar(2000) DEFAULT NULL,
  `is_selected` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`test_instance_id`,`question_id`,`answer_ID` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` int(11) DEFAULT NULL,
  `time_zone` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `job_id` int(10) unsigned DEFAULT NULL,
  `account_id` int(10) unsigned DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT NULL,
  `person_id` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `users`
--


