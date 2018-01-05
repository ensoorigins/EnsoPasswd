-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 03-Out-2017 às 11:27
-- Versão do servidor: 10.1.26-MariaDB
-- PHP Version: 7.1.9
--
-- Database: `mydb`
--

-- --------------------------------------------------------

INSERT INTO `enso_actions` (`enso_action_name`, `inserted_timestamp`, `deleted_timestamp`) VALUES
('accessSysAdminArea', 1506943573, NULL),
('manageRootFolders', 1506943573, NULL),
('manageUsers', 1506943573, NULL),
('listUsers', 1506943573, NULL),
('viewLogs', 1506943573, NULL);

-- --------------------------------------------------------

INSERT INTO `enso_roles` (`enso_role_name`, `inserted_timestamp`, `deleted_timestamp`) VALUES
('NormalUser', 0, NULL),
('SysAdmin', 0, NULL);

-- --------------------------------------------------------

INSERT INTO `enso_actions_roles` (`inserted_timestamp`, `deleted_timestamp`, `enso_role_name_enso_roles`, `enso_action_name_enso_actions`) VALUES
(0, NULL, 'SysAdmin', 'accessSysAdminArea'),
(0, NULL, 'SysAdmin', 'manageRootFolders'),
(0, NULL, 'SysAdmin', 'manageUsers'),
(0, NULL, 'SysAdmin', 'listUsers'),
(0, NULL, 'SysAdmin', 'viewLogs');

-- --------------------------------------------------------

INSERT INTO `Users` (`username`, `password`, `email`, `sessionKey`, `ldap`) VALUES
('admin', 'b123e9e19d217169b981a61188920f9d28638709a5132201684d792b9264271b7f09157ed4321b1c097f7a4abecfc0977d40a7ee599c845883bd1074ca23c4af', 'pedronascimento@enso-origins.com', NULL, 0),
('pedromarques', 'b123e9e19d217169b981a61188920f9d28638709a5132201684d792b9264271b7f09157ed4321b1c097f7a4abecfc0977d40a7ee599c845883bd1074ca23c4af', 'pedromarques@enso-origins.com', NULL, 0);

-- --------------------------------------------------------

INSERT INTO `enso_user_roles` (`id_user`, `inserted_timestamp`, `deleted_timestamp`, `enso_role_name_enso_roles`) VALUES
('admin', 1506944408, NULL, 'NormalUser'),
('pedromaeques', 1506944408, NULL, 'NormalUser'),
('admin', 1506944408, NULL, 'SysAdmin');

-- ----------------------------------------------------------

CREATE OR REPLACE VIEW UserInfo AS
    SELECT 
        Users.username,
        Users.email,
        Users.ldap,
        Users.password,
        CASE enso_user_roles.enso_role_name_enso_roles
            WHEN 'SysAdmin' THEN 1
            ELSE 0
        END AS 'sysadmin'
    FROM
        Users
            LEFT JOIN
        enso_user_roles ON enso_user_roles.id_user = Users.username
            AND enso_user_roles.enso_role_name_enso_roles = 'SysAdmin'
