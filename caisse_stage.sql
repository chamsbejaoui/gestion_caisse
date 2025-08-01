-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 31 juil. 2025 à 12:56
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `caisse_stage`
--

-- --------------------------------------------------------

--
-- Structure de la table `alimentation`
--

CREATE TABLE `alimentation` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `montant` double NOT NULL,
  `source` varchar(255) NOT NULL,
  `date_action` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `alimentation`
--

INSERT INTO `alimentation` (`id`, `user_id`, `montant`, `source`, `date_action`, `created_at`) VALUES
(1, 1, 220, 'vente', '2025-07-14 12:02:00', '2025-07-15 13:02:24'),
(2, 1, 780, 'gain', '2025-07-18 14:26:00', '2025-07-14 15:27:00'),
(3, 1, 111, 'vente', '2025-07-17 14:11:00', '2025-07-16 15:11:28'),
(4, 28, 750, 'gain', '2025-07-28 10:56:00', '2025-07-28 11:56:12');

-- --------------------------------------------------------

--
-- Structure de la table `alimentation_audit`
--

CREATE TABLE `alimentation_audit` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `discriminator` varchar(255) DEFAULT NULL,
  `transaction_hash` varchar(40) DEFAULT NULL,
  `diffs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`diffs`)),
  `blame_id` varchar(255) DEFAULT NULL,
  `blame_user` varchar(255) DEFAULT NULL,
  `blame_user_fqdn` varchar(255) DEFAULT NULL,
  `blame_user_firewall` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `alimentation_audit`
--

INSERT INTO `alimentation_audit` (`id`, `type`, `object_id`, `discriminator`, `transaction_hash`, `diffs`, `blame_id`, `blame_user`, `blame_user_fqdn`, `blame_user_firewall`, `ip`, `created_at`) VALUES
(1, 'insert', '7', NULL, '967f418b97e3b7101646c9a0e24f0e6ab579927d', '{\"@source\":{\"id\":7,\"class\":\"App\\\\Entity\\\\Alimentation\",\"label\":\"App\\\\Entity\\\\Alimentation#7\",\"table\":\"alimentation\"},\"montant\":{\"new\":45},\"source\":{\"new\":\"vente\"},\"dateAction\":{\"new\":\"2025-07-30 11:46:00\"},\"createdAt\":{\"new\":\"2025-07-30 12:46:34\"},\"user\":{\"new\":{\"id\":1,\"class\":\"App\\\\Entity\\\\User\",\"label\":\"App\\\\Entity\\\\User#1\",\"table\":\"user\"}}}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 10:46:34'),
(2, 'update', '7', NULL, '1cf7bd6fbe5dbaaf36f7bb90b329dc06c55cb943', '{\"montant\":{\"old\":45,\"new\":450}}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 10:48:06'),
(3, 'remove', '7', NULL, 'b6144d7e86a480e528db7fe0c9ff884029f6ee88', '{\"id\":7,\"class\":\"App\\\\Entity\\\\Alimentation\",\"label\":\"App\\\\Entity\\\\Alimentation#7\",\"table\":\"alimentation\"}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 12:03:23'),
(4, 'remove', '6', NULL, '6e669a0a87b0ac9ab1f1d7d08c62808159a85597', '{\"id\":6,\"class\":\"App\\\\Entity\\\\Alimentation\",\"label\":\"App\\\\Entity\\\\Alimentation#6\",\"table\":\"alimentation\"}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 12:08:51'),
(5, 'remove', '5', NULL, 'f65fb7b1c1a9984e3f7823b8369492e093b14189', '{\"id\":5,\"class\":\"App\\\\Entity\\\\Alimentation\",\"label\":\"App\\\\Entity\\\\Alimentation#5\",\"table\":\"alimentation\"}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 12:11:50');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `nom`) VALUES
(1, 'carburant'),
(2, 'matériel informatique');

-- --------------------------------------------------------

--
-- Structure de la table `categorie_audit`
--

CREATE TABLE `categorie_audit` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `discriminator` varchar(255) DEFAULT NULL,
  `transaction_hash` varchar(40) DEFAULT NULL,
  `diffs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`diffs`)),
  `blame_id` varchar(255) DEFAULT NULL,
  `blame_user` varchar(255) DEFAULT NULL,
  `blame_user_fqdn` varchar(255) DEFAULT NULL,
  `blame_user_firewall` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `depense`
--

CREATE TABLE `depense` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `montant` double NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_action` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `depense`
--

INSERT INTO `depense` (`id`, `user_id`, `categorie_id`, `montant`, `description`, `date_action`, `created_at`) VALUES
(1, 1, 1, 120.5, 'achat', '2025-06-04 19:24:00', '2025-07-14 11:50:06'),
(3, 1, 1, 1, '15', '2025-07-19 09:45:00', '2025-07-15 10:45:46'),
(4, 1, 1, 8445, '4554', '2025-07-27 09:45:00', '2025-07-15 10:46:01'),
(5, 1, 1, 1, 'b h', '2025-07-17 02:21:00', '2025-07-15 11:21:48'),
(6, 1, 1, 5, 'n', '2025-07-09 10:21:00', '2025-07-15 11:21:59'),
(7, 1, 1, 78, '*', '2025-07-24 10:22:00', '2025-07-15 11:22:15'),
(8, 1, 1, 5, 'achhterretecha', '2025-07-24 10:22:00', '2025-07-15 11:22:39'),
(9, 1, 1, 88, '4', '2025-07-24 10:22:00', '2025-07-15 11:23:02'),
(10, 1, 1, 7, 'achat', '2025-07-23 10:23:00', '2025-07-15 11:23:26'),
(12, 1, 1, 789996, 'achat', '2025-07-11 10:16:00', '2025-07-16 11:16:08'),
(14, 1, 2, 5, 'achat', '2025-07-24 13:52:00', '2025-07-16 14:52:23'),
(15, 1, 2, 120.5, 'b h', '2025-07-25 12:45:00', '2025-07-18 13:45:27');

-- --------------------------------------------------------

--
-- Structure de la table `depense_audit`
--

CREATE TABLE `depense_audit` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `discriminator` varchar(255) DEFAULT NULL,
  `transaction_hash` varchar(40) DEFAULT NULL,
  `diffs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`diffs`)),
  `blame_id` varchar(255) DEFAULT NULL,
  `blame_user` varchar(255) DEFAULT NULL,
  `blame_user_fqdn` varchar(255) DEFAULT NULL,
  `blame_user_firewall` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `depense_audit`
--

INSERT INTO `depense_audit` (`id`, `type`, `object_id`, `discriminator`, `transaction_hash`, `diffs`, `blame_id`, `blame_user`, `blame_user_fqdn`, `blame_user_firewall`, `ip`, `created_at`) VALUES
(1, 'update', '11', NULL, 'd0b385f964da8edb5d5e14c763fbe73afeb26a8d', '{\"montant\":{\"old\":7890,\"new\":78}}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 12:04:33'),
(2, 'remove', '11', NULL, 'f298b8eb5a7ffa1cb4564c3867e64e20c36a134a', '{\"id\":11,\"class\":\"App\\\\Entity\\\\Depense\",\"label\":\"App\\\\Entity\\\\Depense#11\",\"table\":\"depense\"}', '1', 'admin@gmail.com', 'DH\\Auditor\\User\\User', 'main', '127.0.0.1', '2025-07-30 13:07:15');

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250703113535', '2025-07-03 13:35:41', 64),
('DoctrineMigrations\\Version20250704101459', '2025-07-04 12:15:02', 10),
('DoctrineMigrations\\Version20250708081820', '2025-07-08 10:18:39', 112),
('DoctrineMigrations\\Version20250714085145', '2025-07-14 10:51:47', 15),
('DoctrineMigrations\\Version20250714091917', '2025-07-14 11:19:25', 142),
('DoctrineMigrations\\Version20250714105056', '2025-07-14 12:50:59', 87),
('DoctrineMigrations\\Version20250728102151', '2025-07-28 12:21:57', 75);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(12, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:1738:\\\"<!DOCTYPE html>\n<html>\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <title>Confirmation de votre compte</title>\n    <style>\n        .email-container {\n            max-width: 600px;\n            margin: 0 auto;\n            font-family: Arial, sans-serif;\n            padding: 20px;\n        }\n        .header {\n            background-color: #4CAF50;\n            color: white;\n            padding: 20px;\n            text-align: center;\n            border-radius: 5px 5px 0 0;\n        }\n        .content {\n            background-color: #f9f9f9;\n            padding: 20px;\n            border-radius: 0 0 5px 5px;\n        }\n        .button {\n            display: inline-block;\n            padding: 10px 20px;\n            background-color: #4CAF50;\n            color: white;\n            text-decoration: none;\n            border-radius: 5px;\n            margin-top: 20px;\n        }\n    </style>\n</head>\n<body>\n    <div class=\\\"email-container\\\">\n        <div class=\\\"header\\\">\n            <h1>Confirmation de votre compte</h1>\n        </div>\n        <div class=\\\"content\\\">\n            <p>Bonjour,</p>\n            <p>Merci de vous être inscrit ! Pour activer votre compte, veuillez cliquer sur le bouton ci-dessous :</p>\n            <p style=\\\"text-align: center;\\\">\n                <a href=\\\"https://127.0.0.1:8000/user/verify/email?expires=1751631745&amp;id=9&amp;signature=FyPDypEuRhTiO5XjShTL3CVlb3RiTy_ogqgLSvYNWvg&amp;token=PIVvFI7xQ%2FbCUWVetcoYhBCiGNXSvjEXgmguhCzHXxI%3D\\\" class=\\\"button\\\">Confirmer mon compte</a>\n            </p>\n            <p>Ce lien expirera dans 1 heure.</p>\n            <p>Si vous n\\\'avez pas créé de compte, vous pouvez ignorer cet email.</p>\n            <p>Cordialement,<br>L\\\'équipe</p>\n        </div>\n    </div>\n</body>\n</html>\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"chams2002bejaoui@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:36:\\\"mohamedchamseddine.bejaoui@esprit.tn\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:28:\\\"Confirmation de votre compte\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2025-07-04 11:22:25', '2025-07-04 11:22:25', '2025-07-04 11:43:27');

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) NOT NULL,
  `hashed_token` varchar(100) NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `is_verified`) VALUES
(1, 'admin@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$NOV69W9ETsaSjloXpswSRO.o8/HmoC5i0EslexTW2gcCoHDRQsf4m', 1),
(2, 'user@gmail.com', '[\"ROLE_USER\"]', '$2y$13$n0Q0zRpaVnfc5iWHHbtY7.OIzq0XobmiPfY8ye8N2Pknf2qxu7Zmq', 1),
(18, 'mohamedchamseddine.bejaoui@esprit.tn', '[]', '$2y$13$9RMrkGmEYUYJLZXCVbXkCu8hcJRmUu/AEQEjBl/5yfnEU3S1vR17S', 1),
(28, 'chams2002bejaoui@gmail.com', '[\"ROLE_USER\",\"ROLE_ADMIN\"]', '$2y$13$QvCONOceAJIvjVO9Y3bxw.gSi2By5rMI9i4PZmyIvYJQBmV5Utm0a', 1);

-- --------------------------------------------------------

--
-- Structure de la table `user_audit`
--

CREATE TABLE `user_audit` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(10) NOT NULL,
  `object_id` varchar(255) NOT NULL,
  `discriminator` varchar(255) DEFAULT NULL,
  `transaction_hash` varchar(40) DEFAULT NULL,
  `diffs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`diffs`)),
  `blame_id` varchar(255) DEFAULT NULL,
  `blame_user` varchar(255) DEFAULT NULL,
  `blame_user_fqdn` varchar(255) DEFAULT NULL,
  `blame_user_firewall` varchar(100) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alimentation`
--
ALTER TABLE `alimentation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_8E65DFA0A76ED395` (`user_id`);

--
-- Index pour la table `alimentation_audit`
--
ALTER TABLE `alimentation_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_5c8593e3aeefc896693e172e90597858_idx` (`type`),
  ADD KEY `object_id_5c8593e3aeefc896693e172e90597858_idx` (`object_id`),
  ADD KEY `discriminator_5c8593e3aeefc896693e172e90597858_idx` (`discriminator`),
  ADD KEY `transaction_hash_5c8593e3aeefc896693e172e90597858_idx` (`transaction_hash`),
  ADD KEY `blame_id_5c8593e3aeefc896693e172e90597858_idx` (`blame_id`),
  ADD KEY `created_at_5c8593e3aeefc896693e172e90597858_idx` (`created_at`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `categorie_audit`
--
ALTER TABLE `categorie_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`type`),
  ADD KEY `object_id_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`object_id`),
  ADD KEY `discriminator_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`discriminator`),
  ADD KEY `transaction_hash_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`transaction_hash`),
  ADD KEY `blame_id_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`blame_id`),
  ADD KEY `created_at_b689b27e9b00bf3a4d4e85af66cca6ba_idx` (`created_at`);

--
-- Index pour la table `depense`
--
ALTER TABLE `depense`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_34059757A76ED395` (`user_id`),
  ADD KEY `IDX_34059757BCF5E72D` (`categorie_id`);

--
-- Index pour la table `depense_audit`
--
ALTER TABLE `depense_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`type`),
  ADD KEY `object_id_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`object_id`),
  ADD KEY `discriminator_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`discriminator`),
  ADD KEY `transaction_hash_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`transaction_hash`),
  ADD KEY `blame_id_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`blame_id`),
  ADD KEY `created_at_b2cd5042d3a8e770be3b361da5d4eb12_idx` (`created_at`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  ADD KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  ADD KEY `IDX_75EA56E016BA31DB` (`delivered_at`);

--
-- Index pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`);

--
-- Index pour la table `user_audit`
--
ALTER TABLE `user_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_e06395edc291d0719bee26fd39a32e8a_idx` (`type`),
  ADD KEY `object_id_e06395edc291d0719bee26fd39a32e8a_idx` (`object_id`),
  ADD KEY `discriminator_e06395edc291d0719bee26fd39a32e8a_idx` (`discriminator`),
  ADD KEY `transaction_hash_e06395edc291d0719bee26fd39a32e8a_idx` (`transaction_hash`),
  ADD KEY `blame_id_e06395edc291d0719bee26fd39a32e8a_idx` (`blame_id`),
  ADD KEY `created_at_e06395edc291d0719bee26fd39a32e8a_idx` (`created_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alimentation`
--
ALTER TABLE `alimentation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `alimentation_audit`
--
ALTER TABLE `alimentation_audit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `categorie_audit`
--
ALTER TABLE `categorie_audit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `depense`
--
ALTER TABLE `depense`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `depense_audit`
--
ALTER TABLE `depense_audit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `user_audit`
--
ALTER TABLE `user_audit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `alimentation`
--
ALTER TABLE `alimentation`
  ADD CONSTRAINT `FK_8E65DFA0A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `depense`
--
ALTER TABLE `depense`
  ADD CONSTRAINT `FK_34059757A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_34059757BCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`);

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
