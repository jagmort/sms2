-- phpMyAdmin SQL Dump
-- version 4.2.12deb2+deb8u3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 08, 2019 at 10:24 AM
-- Server version: 5.5.60-0+deb8u1
-- PHP Version: 5.6.38-0+deb8u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sms`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE IF NOT EXISTS `contact` (
`id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `position` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `dept` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `mobile` varchar(11) CHARACTER SET utf8mb4 NOT NULL,
  `work` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `home` varchar(30) CHARACTER SET utf8mb4 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `comment` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2942 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `contact_list`
--

CREATE TABLE IF NOT EXISTS `contact_list` (
`id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `email_only` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=23813 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `contact_tab`
--

CREATE TABLE IF NOT EXISTS `contact_tab` (
`id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `tab_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `block` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3220 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group`
--

CREATE TABLE IF NOT EXISTS `group` (
`id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sign` varchar(600) NOT NULL,
  `supervisor` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group_list`
--

CREATE TABLE IF NOT EXISTS `group_list` (
`id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=902 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group_tab`
--

CREATE TABLE IF NOT EXISTS `group_tab` (
`id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `tab_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group_template`
--

CREATE TABLE IF NOT EXISTS `group_template` (
`id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `tid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `list`
--

CREATE TABLE IF NOT EXISTS `list` (
`id` int(11) NOT NULL,
  `tab_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  `alert` varchar(255) NOT NULL,
  `optgroup` varchar(512) NOT NULL,
  `parent_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1017 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `recipient`
--

CREATE TABLE IF NOT EXISTS `recipient` (
`id` int(11) NOT NULL,
  `sms_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `email_only` tinyint(1) NOT NULL,
  `done` datetime NOT NULL,
  `sent` datetime NOT NULL,
  `phone` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `error` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=616428 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sms`
--

CREATE TABLE IF NOT EXISTS `sms` (
`id` int(11) NOT NULL,
  `uid` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `text` varchar(600) NOT NULL,
  `put` datetime NOT NULL,
  `filename` varchar(255) NOT NULL,
  `Priority` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=116031 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tab`
--

CREATE TABLE IF NOT EXISTS `tab` (
`id` int(11) NOT NULL,
  `name` varchar(32) NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `template`
--

CREATE TABLE IF NOT EXISTS `template` (
`id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`id` int(11) NOT NULL,
  `group_id` int(11) DEFAULT '0',
  `admin` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
 ADD PRIMARY KEY (`id`), ADD KEY `name` (`name`(191),`dept`(191)), ADD KEY `email` (`email`(191)), ADD KEY `mobile` (`mobile`), ADD KEY `keyword` (`keyword`);

--
-- Indexes for table `contact_list`
--
ALTER TABLE `contact_list`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uniq` (`contact_id`,`list_id`), ADD KEY `contact_id` (`contact_id`,`list_id`), ADD KEY `list_id` (`list_id`), ADD KEY `email_only` (`email_only`);

--
-- Indexes for table `contact_tab`
--
ALTER TABLE `contact_tab`
 ADD PRIMARY KEY (`id`), ADD KEY `contact_id` (`contact_id`,`tab_id`,`order`), ADD KEY `tab_id` (`tab_id`), ADD KEY `block` (`block`);

--
-- Indexes for table `group`
--
ALTER TABLE `group`
 ADD PRIMARY KEY (`id`), ADD KEY `email` (`email`);

--
-- Indexes for table `group_list`
--
ALTER TABLE `group_list`
 ADD PRIMARY KEY (`id`), ADD KEY `group_id` (`group_id`,`list_id`), ADD KEY `list_id` (`list_id`);

--
-- Indexes for table `group_tab`
--
ALTER TABLE `group_tab`
 ADD PRIMARY KEY (`id`), ADD KEY `group_id` (`group_id`,`tab_id`), ADD KEY `tab_id` (`tab_id`);

--
-- Indexes for table `group_template`
--
ALTER TABLE `group_template`
 ADD PRIMARY KEY (`id`), ADD KEY `gid` (`group_id`,`tid`), ADD KEY `tid` (`tid`);

--
-- Indexes for table `list`
--
ALTER TABLE `list`
 ADD PRIMARY KEY (`id`), ADD KEY `tab_id` (`tab_id`), ADD KEY `order` (`order`), ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `recipient`
--
ALTER TABLE `recipient`
 ADD PRIMARY KEY (`id`), ADD KEY `sms_id` (`sms_id`,`done`,`sent`), ADD KEY `phone` (`phone`), ADD KEY `status` (`status`), ADD KEY `contact_id` (`contact_id`), ADD KEY `email_only` (`email_only`);

--
-- Indexes for table `sms`
--
ALTER TABLE `sms`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uid` (`uid`), ADD KEY `user_id` (`user_id`), ADD KEY `put` (`put`), ADD KEY `group_id` (`gid`), ADD KEY `Priority` (`Priority`);

--
-- Indexes for table `tab`
--
ALTER TABLE `tab`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `template`
--
ALTER TABLE `template`
 ADD PRIMARY KEY (`id`), ADD KEY `gid` (`group_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD UNIQUE KEY `email` (`email`), ADD UNIQUE KEY `password_reset_token` (`password_reset_token`), ADD KEY `admin` (`admin`), ADD KEY `group_id` (`group_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2942;
--
-- AUTO_INCREMENT for table `contact_list`
--
ALTER TABLE `contact_list`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=23813;
--
-- AUTO_INCREMENT for table `contact_tab`
--
ALTER TABLE `contact_tab`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3220;
--
-- AUTO_INCREMENT for table `group`
--
ALTER TABLE `group`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `group_list`
--
ALTER TABLE `group_list`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=902;
--
-- AUTO_INCREMENT for table `group_tab`
--
ALTER TABLE `group_tab`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=47;
--
-- AUTO_INCREMENT for table `group_template`
--
ALTER TABLE `group_template`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `list`
--
ALTER TABLE `list`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1017;
--
-- AUTO_INCREMENT for table `recipient`
--
ALTER TABLE `recipient`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=616428;
--
-- AUTO_INCREMENT for table `sms`
--
ALTER TABLE `sms`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=116031;
--
-- AUTO_INCREMENT for table `tab`
--
ALTER TABLE `tab`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=60;
--
-- AUTO_INCREMENT for table `template`
--
ALTER TABLE `template`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=91;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_list`
--
ALTER TABLE `contact_list`
ADD CONSTRAINT `contact_list_ibfk_2` FOREIGN KEY (`list_id`) REFERENCES `list` (`id`),
ADD CONSTRAINT `contact_list_ibfk_3` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_tab`
--
ALTER TABLE `contact_tab`
ADD CONSTRAINT `contact_tab_ibfk_1` FOREIGN KEY (`tab_id`) REFERENCES `tab` (`id`),
ADD CONSTRAINT `contact_tab_ibfk_2` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_list`
--
ALTER TABLE `group_list`
ADD CONSTRAINT `group_list_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `list` (`id`),
ADD CONSTRAINT `group_list_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`);

--
-- Constraints for table `group_tab`
--
ALTER TABLE `group_tab`
ADD CONSTRAINT `group_tab_ibfk_2` FOREIGN KEY (`tab_id`) REFERENCES `tab` (`id`),
ADD CONSTRAINT `group_tab_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`);

--
-- Constraints for table `group_template`
--
ALTER TABLE `group_template`
ADD CONSTRAINT `group_template_ibfk_2` FOREIGN KEY (`tid`) REFERENCES `template` (`id`),
ADD CONSTRAINT `group_template_ibfk_3` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`);

--
-- Constraints for table `list`
--
ALTER TABLE `list`
ADD CONSTRAINT `list_ibfk_1` FOREIGN KEY (`tab_id`) REFERENCES `tab` (`id`);

--
-- Constraints for table `recipient`
--
ALTER TABLE `recipient`
ADD CONSTRAINT `recipient_ibfk_4` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`),
ADD CONSTRAINT `recipient_ibfk_3` FOREIGN KEY (`sms_id`) REFERENCES `sms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sms`
--
ALTER TABLE `sms`
ADD CONSTRAINT `sms_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
ADD CONSTRAINT `sms_ibfk_2` FOREIGN KEY (`gid`) REFERENCES `group` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `group` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
