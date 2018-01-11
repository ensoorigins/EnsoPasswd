-- phpMyAdmin SQL Dump
-- version 4.7.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 22, 2017 at 03:59 PM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `Credentials`
--

CREATE TABLE `Credentials` (
  `idCredentials` int(11) NOT NULL,
  `title` tinytext NOT NULL,
  `username` tinytext,
  `password` tinytext NOT NULL,
  `description` tinytext,
  `url` tinytext,
  `belongsToFolder` int(11) DEFAULT NULL,
  `createdById` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enso_actions`
--

CREATE TABLE `enso_actions` (
  `enso_action_name` varchar(50) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL,
  `deleted_timestamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `enso_actions`
--

INSERT INTO `enso_actions` (`enso_action_name`, `inserted_timestamp`, `deleted_timestamp`) VALUES
('accessSysAdminArea', 1506943573, NULL),
('listUsers', 1507389480, NULL),
('manageCredentials', 1508408901, NULL),
('manageRootFolders', 1506943573, NULL),
('manageUsers', 1506943573, NULL),
('seeCredentials', 1508408901, NULL),
('seeFolderContents', 1507740350, NULL),
('shareCredentials', 1509708013, NULL),
('viewLogs', 1506943573, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enso_actions_roles`
--

CREATE TABLE `enso_actions_roles` (
  `inserted_timestamp` int(11) NOT NULL,
  `deleted_timestamp` int(11) DEFAULT NULL,
  `enso_role_name_enso_roles` varchar(50) NOT NULL,
  `enso_action_name_enso_actions` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `enso_actions_roles`
--

INSERT INTO `enso_actions_roles` (`inserted_timestamp`, `deleted_timestamp`, `enso_role_name_enso_roles`, `enso_action_name_enso_actions`) VALUES
(0, NULL, 'SysAdmin', 'accessSysAdminArea'),
(1507389496, NULL, 'NormalUser', 'listUsers'),
(1508408931, NULL, 'NormalUser', 'manageCredentials'),
(0, NULL, 'SysAdmin', 'manageRootFolders'),
(0, NULL, 'SysAdmin', 'manageUsers'),
(1508408931, NULL, 'NormalUser', 'seeCredentials'),
(1507740388, NULL, 'NormalUser', 'seeFolderContents'),
(1509708040, NULL, 'NormalUser', 'shareCredentials'),
(1509708040, NULL, 'SysAdmin', 'shareCredentials'),
(0, NULL, 'SysAdmin', 'viewLogs');

-- --------------------------------------------------------

--
-- Table structure for table `enso_logs`
--

CREATE TABLE `enso_logs` (
  `id` int(11) NOT NULL,
  `severity_level` int(11) NOT NULL,
  `facility` varchar(255) NOT NULL,
  `action` text NOT NULL,
  `ownerid` varchar(50) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL,
  `deleted_timestamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `enso_roles`
--

CREATE TABLE `enso_roles` (
  `enso_role_name` varchar(50) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL,
  `deleted_timestamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `enso_roles`
--

INSERT INTO `enso_roles` (`enso_role_name`, `inserted_timestamp`, `deleted_timestamp`) VALUES
('NormalUser', 0, NULL),
('SysAdmin', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enso_user_roles`
--

CREATE TABLE `enso_user_roles` (
  `id_user` varchar(250) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL,
  `deleted_timestamp` int(11) DEFAULT NULL,
  `enso_role_name_enso_roles` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ExternalMessages`
--

CREATE TABLE `ExternalMessages` (
  `idExternalMessage` int(11) NOT NULL,
  `message` varchar(250) DEFAULT NULL,
  `timeToDie` int(11) NOT NULL,
  `externalKey` varchar(32) NOT NULL,
  `referencedCredential` int(11) NOT NULL,
  `senderId` varchar(250) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Folders`
--

CREATE TABLE `Folders` (
  `idFolders` int(11) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  `name` tinytext NOT NULL,
  `createdById` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Messages`
--

CREATE TABLE `Messages` (
  `idMessages` int(11) NOT NULL,
  `message` tinytext,
  `timeToDie` int(11) DEFAULT NULL,
  `referencedCredential` int(11) NOT NULL,
  `senderId` varchar(250) NOT NULL,
  `receiverId` varchar(250) NOT NULL,
  `inserted_timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Permissions`
--

CREATE TABLE `Permissions` (
  `folder` int(11) NOT NULL,
  `hasAdmin` tinyint(4) NOT NULL,
  `userId` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `UserInfo`
-- (See below for the actual view)
--
CREATE TABLE `UserInfo` (
`username` varchar(250)
,`email` tinytext
,`ldap` tinyint(4)
,`password` tinytext
,`sysadmin` int(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `username` varchar(250) NOT NULL,
  `password` tinytext NOT NULL,
  `email` tinytext NOT NULL,
  `sessionKey` tinytext,
  `trustLimit` int(11) DEFAULT NULL,
  `ldap` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `UserInfo`
--
DROP TABLE IF EXISTS `UserInfo`;

CREATE VIEW `UserInfo`  AS  select `Users`.`username` AS `username`,`Users`.`email` AS `email`,`Users`.`ldap` AS `ldap`,`Users`.`password` AS `password`,(case `enso_user_roles`.`enso_role_name_enso_roles` when 'SysAdmin' then 1 else 0 end) AS `sysadmin` from (`Users` left join `enso_user_roles` on(((`enso_user_roles`.`id_user` = `Users`.`username`) and (`enso_user_roles`.`enso_role_name_enso_roles` = 'SysAdmin')))) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Credentials`
--
ALTER TABLE `Credentials`
  ADD PRIMARY KEY (`idCredentials`),
  ADD KEY `fk_Credentials_Folders1_idx` (`belongsToFolder`);

--
-- Indexes for table `enso_actions`
--
ALTER TABLE `enso_actions`
  ADD PRIMARY KEY (`enso_action_name`);

--
-- Indexes for table `enso_actions_roles`
--
ALTER TABLE `enso_actions_roles`
  ADD PRIMARY KEY (`enso_action_name_enso_actions`,`enso_role_name_enso_roles`),
  ADD KEY `fk_enso_actions_roles_enso_roles1_idx` (`enso_role_name_enso_roles`),
  ADD KEY `fk_enso_actions_roles_enso_actions1_idx` (`enso_action_name_enso_actions`);

--
-- Indexes for table `enso_logs`
--
ALTER TABLE `enso_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enso_roles`
--
ALTER TABLE `enso_roles`
  ADD PRIMARY KEY (`enso_role_name`);

--
-- Indexes for table `enso_user_roles`
--
ALTER TABLE `enso_user_roles`
  ADD PRIMARY KEY (`enso_role_name_enso_roles`,`id_user`),
  ADD KEY `fk_enso_user_roles_enso_roles1_idx` (`enso_role_name_enso_roles`);

--
-- Indexes for table `ExternalMessages`
--
ALTER TABLE `ExternalMessages`
  ADD PRIMARY KEY (`idExternalMessage`),
  ADD KEY `fk_ExternalMessages_Credentials1_idx` (`referencedCredential`),
  ADD KEY `fk_ExternalMessages_Users1_idx` (`senderId`);

--
-- Indexes for table `Folders`
--
ALTER TABLE `Folders`
  ADD PRIMARY KEY (`idFolders`);

--
-- Indexes for table `Messages`
--
ALTER TABLE `Messages`
  ADD PRIMARY KEY (`idMessages`),
  ADD KEY `fk_Messages_Credentials1_idx` (`referencedCredential`),
  ADD KEY `fk_Messages_Users1_idx` (`senderId`),
  ADD KEY `fk_Messages_Users2_idx` (`receiverId`);

--
-- Indexes for table `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`folder`,`userId`),
  ADD KEY `fk_Users_has_Folders_Folders1_idx` (`folder`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Credentials`
--
ALTER TABLE `Credentials`
  MODIFY `idCredentials` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `enso_logs`
--
ALTER TABLE `enso_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1560;

--
-- AUTO_INCREMENT for table `ExternalMessages`
--
ALTER TABLE `ExternalMessages`
  MODIFY `idExternalMessage` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Folders`
--
ALTER TABLE `Folders`
  MODIFY `idFolders` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `Messages`
--
ALTER TABLE `Messages`
  MODIFY `idMessages` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Credentials`
--
ALTER TABLE `Credentials`
  ADD CONSTRAINT `fk_Credentials_Folders1` FOREIGN KEY (`belongsToFolder`) REFERENCES `Folders` (`idFolders`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `enso_actions_roles`
--
ALTER TABLE `enso_actions_roles`
  ADD CONSTRAINT `fk_enso_actions_roles_enso_actions1` FOREIGN KEY (`enso_action_name_enso_actions`) REFERENCES `enso_actions` (`enso_action_name`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_enso_actions_roles_enso_roles1` FOREIGN KEY (`enso_role_name_enso_roles`) REFERENCES `enso_roles` (`enso_role_name`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `enso_user_roles`
--
ALTER TABLE `enso_user_roles`
  ADD CONSTRAINT `fk_enso_user_roles_enso_roles1` FOREIGN KEY (`enso_role_name_enso_roles`) REFERENCES `enso_roles` (`enso_role_name`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `ExternalMessages`
--
ALTER TABLE `ExternalMessages`
  ADD CONSTRAINT `fk_ExternalMessages_Credentials1` FOREIGN KEY (`referencedCredential`) REFERENCES `Credentials` (`idCredentials`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ExternalMessages_Users1` FOREIGN KEY (`senderId`) REFERENCES `Users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `Messages`
--
ALTER TABLE `Messages`
  ADD CONSTRAINT `fk_Messages_Credentials1` FOREIGN KEY (`referencedCredential`) REFERENCES `Credentials` (`idCredentials`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Messages_Users1` FOREIGN KEY (`senderId`) REFERENCES `Users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Messages_Users2` FOREIGN KEY (`receiverId`) REFERENCES `Users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `Permissions`
--
ALTER TABLE `Permissions`
  ADD CONSTRAINT `fk_Users_has_Folders_Folders1` FOREIGN KEY (`folder`) REFERENCES `Folders` (`idFolders`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
