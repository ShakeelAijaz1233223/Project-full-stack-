-- --------------------------------------------------------
-- Database: dashboard_db_full
-- --------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `dashboard_db`;
USE `dashboard_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','blocked') DEFAULT 'active',
  `avatar` varchar(255) NOT NULL DEFAULT 'default.png',
  `profile_img` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample users (hashed passwords: 'admin123', 'user123')
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`, `avatar`, `profile_img`) VALUES
('SHAKEEL AHMED', 'admin@example.com', 'admin11', 'admin', 'active', 'avatar_1.jpg', 'default.png'),
('John Doe', 'john@example.com', 'user123', 'user', 'active', 'default.png', 'default.png'),
('Jane Smith', 'jane@example.com', 'user234', 'user', 'active', 'default.png', 'default.png');

-- --------------------------------------------------------
-- Table structure for table `users_login` (login history)
-- --------------------------------------------------------
CREATE TABLE `users_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(50) DEFAULT NULL,
  `browser_info` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `users_login_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `albums`
-- --------------------------------------------------------
CREATE TABLE `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) DEFAULT '',
  `cover` varchar(255) DEFAULT '',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cover_image` varchar(255) NOT NULL DEFAULT 'default_album.png',
  `full_cover_image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample albums
INSERT INTO `albums` (`title`, `artist`, `cover`, `description`, `cover_image`) VALUES
('First Album', 'Shakeel AH', '', 'First album description', 'default_album.png'),
('Second Album', 'John Doe', '', 'Second album description', 'default_album.png');

-- --------------------------------------------------------
-- Table structure for table `music`
-- --------------------------------------------------------
CREATE TABLE `music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `album_id` int(11) DEFAULT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cover_image` varchar(255) NOT NULL DEFAULT 'default_music.png',
  `full_cover_image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `music_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample music
INSERT INTO `music` (`title`, `artist`, `file`, `album_id`, `genre`, `duration`, `cover_image`) VALUES
('Song One', 'Shakeel AH', 'song1.mp3', 1, 'Pop', '03:45', 'default_music.png'),
('Song Two', 'Shakeel AH', 'song2.mp3', 1, 'Rock', '04:20', 'default_music.png'),
('Song Three', 'John Doe', 'song3.mp3', 2, 'Jazz', '05:10', 'default_music.png');

-- --------------------------------------------------------
-- Table structure for table `videos`
-- --------------------------------------------------------
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `album_id` int(11) DEFAULT NULL,
  `genre` varchar(50) DEFAULT NULL,
  `duration` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `thumbnail` varchar(255) NOT NULL DEFAULT 'default_video.png',
  `full_thumbnail_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `videos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample videos
INSERT INTO `videos` (`title`, `file`, `album_id`, `genre`, `duration`, `thumbnail`) VALUES
('Video One', 'video1.mp4', 1, 'Pop', '04:00', 'default_video.png'),
('Video Two', 'video2.mp4', 2, 'Jazz', '05:30', 'default_video.png');

-- --------------------------------------------------------
-- Table structure for table `playlists`
-- --------------------------------------------------------
CREATE TABLE `playlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `visibility` enum('public','private') DEFAULT 'public',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample playlist
INSERT INTO `playlists` (`name`, `created_by`, `description`, `visibility`) VALUES
('My Playlist', 1, 'Admin playlist', 'public');

-- --------------------------------------------------------
-- Table structure for table `playlist_songs`
-- --------------------------------------------------------
CREATE TABLE `playlist_songs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `music_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `playlist_id` (`playlist_id`),
  KEY `music_id` (`music_id`),
  CONSTRAINT `playlist_songs_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `playlist_songs_ibfk_2` FOREIGN KEY (`music_id`) REFERENCES `music` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Playlist songs
INSERT INTO `playlist_songs` (`playlist_id`, `music_id`) VALUES
(1,1),(1,2);

-- --------------------------------------------------------
-- Table structure for table `playlist_videos`
-- --------------------------------------------------------
CREATE TABLE `playlist_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) NOT NULL,
  `video_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `playlist_id` (`playlist_id`),
  KEY `video_id` (`video_id`),
  CONSTRAINT `playlist_videos_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `playlist_videos_ibfk_2` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Playlist videos
INSERT INTO `playlist_videos` (`playlist_id`, `video_id`) VALUES
(1,1),(1,2);

-- --------------------------------------------------------
-- Table structure for table `comments`
-- --------------------------------------------------------
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('music','video','playlist') NOT NULL,
  `type_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `ratings`
-- --------------------------------------------------------
CREATE TABLE `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('music','video','playlist') NOT NULL,
  `type_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `site_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `settings` (`site_name`, `site_email`) VALUES
('My Sound Website', 'Shakeel@gmail.com');

COMMIT;
