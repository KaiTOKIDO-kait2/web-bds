-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 13, 2026 lúc 03:52 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `realestatephp_new`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `about`
--

CREATE TABLE `about` (
  `id` int(10) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `image` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `about`
--

INSERT INTO `about` (`id`, `title`, `content`, `image`) VALUES
(10, 'Về chúng tôi', '<p class=\"text_all_p_tag_css\">Ch&agrave;o mừng bạn đến với hệ thống quản l&yacute; bất động sản. Ch&uacute;ng t&ocirc;i kết nối người mua, người b&aacute;n v&agrave; m&ocirc;i giới tr&ecirc;n một nền tảng dễ sử dụng.</p>\r\n<p class=\"text_all_p_tag_css\">Mục ti&ecirc;u của ch&uacute;ng t&ocirc;i l&agrave; cung cấp th&ocirc;ng tin minh bạch, dễ t&igrave;m kiếm v&agrave; dễ quản l&yacute; tin đăng bất động sản.&nbsp;<br /><br /></p>\r\n<p class=\"text_all_p_tag_css\">By Admin</p>', 'condos-pool.png');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
--

CREATE TABLE `admin` (
  `aid` int(10) NOT NULL,
  `auser` varchar(50) NOT NULL,
  `aemail` varchar(50) NOT NULL,
  `apass` varchar(50) NOT NULL,
  `adob` date NOT NULL,
  `aphone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `admin`
--

INSERT INTO `admin` (`aid`, `auser`, `aemail`, `apass`, `adob`, `aphone`) VALUES
(10, 'admin', 'admin@gmail.com', '40bd001563085fc35165329ea1ff5c5ecbdbbeef', '1990-01-01', '1234567890');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_event`
--

CREATE TABLE `chatbot_event` (
  `id` bigint(20) NOT NULL,
  `user_uid` int(11) DEFAULT NULL,
  `public_session_id` varchar(80) DEFAULT NULL,
  `property_id` int(11) NOT NULL,
  `event_type` varchar(60) NOT NULL,
  `source` varchar(60) NOT NULL DEFAULT 'chatbot',
  `metadata_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_context_state`
--

CREATE TABLE `chat_context_state` (
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `filters_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'SearchFilters đã merge' CHECK (json_valid(`filters_json`)),
  `last_property_ids_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Mảng pid kết quả gần nhất (tối đa 50)' CHECK (json_valid(`last_property_ids_json`)),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Cấu trúc bảng cho bảng `chat_message`
--

CREATE TABLE `chat_message` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `session_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('user','assistant','system') NOT NULL DEFAULT 'user',
  `content` mediumtext NOT NULL,
  `intent_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'intent + filters snapshot sau bước parse' CHECK (json_valid(`intent_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Cấu trúc bảng cho bảng `chat_session`
--

CREATE TABLE `chat_session` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_id` char(36) NOT NULL COMMENT 'UUID gửi từ client/PHP',
  `user_uid` int(11) DEFAULT NULL COMMENT 'user.uid nếu đã đăng nhập',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Cấu trúc bảng cho bảng `city`
--

CREATE TABLE `city` (
  `cid` int(50) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `cname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `city`
--


--
-- Cấu trúc bảng cho bảng `contact`
--

CREATE TABLE `contact` (
  `cid` int(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `feedback`
--

CREATE TABLE `feedback` (
  `fid` int(50) NOT NULL,
  `uid` int(50) NOT NULL,
  `fdescription` varchar(300) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `request_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `property`
--

CREATE TABLE `property` (
  `pid` int(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `pcontent` longtext NOT NULL,
  `type` varchar(100) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `direction` varchar(50) NOT NULL,
  `stype` varchar(100) NOT NULL,
  `bedroom` int(50) NOT NULL,
  `bathroom` int(50) NOT NULL,
  `balcony` int(50) NOT NULL,
  `kitchen` int(50) NOT NULL,
  `hall` int(50) NOT NULL,
  `floor` varchar(50) NOT NULL,
  `size` int(50) NOT NULL,
  `price` varchar(50) NOT NULL,
  `location` varchar(200) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `ward_id` int(11) DEFAULT NULL,
  `pimage` varchar(300) NOT NULL,
  `pimage1` varchar(300) NOT NULL,
  `pimage2` varchar(300) NOT NULL,
  `pimage3` varchar(300) NOT NULL,
  `pimage4` varchar(300) NOT NULL,
  `uid` int(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `approval_status` varchar(20) NOT NULL DEFAULT 'approved',
  `approval_seen` tinyint(1) NOT NULL DEFAULT 1,
  `reviewed_at` datetime DEFAULT NULL,
  `mapimage` varchar(300) NOT NULL,
  `topmapimage` varchar(300) NOT NULL,
  `groundmapimage` varchar(300) NOT NULL,
  `totalfloor` varchar(50) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `isFeatured` int(11) DEFAULT NULL,
  `view_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Cấu trúc bảng cho bảng `property_amenity`
--

CREATE TABLE `property_amenity` (
  `id` int(11) NOT NULL,
  `property_id` int(50) NOT NULL,
  `property_age` int(11) DEFAULT NULL,
  `swimming_pool` tinyint(1) NOT NULL DEFAULT 0,
  `parking` tinyint(1) NOT NULL DEFAULT 0,
  `gym` tinyint(1) NOT NULL DEFAULT 0,
  `near_school` tinyint(1) NOT NULL DEFAULT 0,
  `security` tinyint(1) NOT NULL DEFAULT 0,
  `near_hospital` tinyint(1) NOT NULL DEFAULT 0,
  `near_market` tinyint(1) NOT NULL DEFAULT 0,
  `wifi` tinyint(1) NOT NULL DEFAULT 0,
  `elevator` tinyint(1) NOT NULL DEFAULT 0,
  `cctv` tinyint(1) NOT NULL DEFAULT 0,
  `water_source` enum('nuoc_ngam','bon_chua') DEFAULT NULL,
  `frontage_m` decimal(10,2) DEFAULT NULL COMMENT 'Mat tien (m)',
  `access_road_m` decimal(10,2) DEFAULT NULL COMMENT 'Duong vao (m)',
  `interior_level` enum('co_ban','day_du','khong') DEFAULT NULL COMMENT 'Noi that',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Cấu trúc bảng cho bảng `property_favorite`
--

CREATE TABLE `property_favorite` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `property_inquiry`
--

CREATE TABLE `property_inquiry` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `agent_uid` int(11) NOT NULL,
  `inquirer_uid` int(11) DEFAULT NULL,
  `inquirer_name` varchar(120) NOT NULL,
  `work_email` varchar(160) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `requirement` text NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `case_status` enum('new','contacted','scheduled','viewed','completed','cancelled') NOT NULL DEFAULT 'new',
  `desired_budget` varchar(120) DEFAULT NULL,
  `desired_area` varchar(255) DEFAULT NULL,
  `desired_move_in_time` varchar(120) DEFAULT NULL,
  `appointment_status` enum('none','pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'none',
  `appointment_requested_at` datetime DEFAULT NULL,
  `appointment_confirmed_at` datetime DEFAULT NULL,
  `appointment_note` text DEFAULT NULL,
  `viewed_at` datetime DEFAULT NULL,
  `result_note` text DEFAULT NULL,
  `workflow_updated_at` datetime DEFAULT NULL,
  `contacted_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `property_inquiry_log`
--

CREATE TABLE `property_inquiry_log` (
  `id` bigint(20) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `action_key` varchar(100) NOT NULL,
  `action_label` varchar(255) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `actor_type` enum('admin','agent','owner','user','system') NOT NULL DEFAULT 'system',
  `actor_id` int(11) DEFAULT NULL,
  `actor_name` varchar(120) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `property_owner_call_click`
--

CREATE TABLE `property_owner_call_click` (
  `id` bigint(20) NOT NULL,
  `property_id` int(11) NOT NULL,
  `owner_uid` int(11) NOT NULL,
  `caller_uid` int(11) DEFAULT NULL,
  `caller_ip` varchar(45) DEFAULT NULL,
  `caller_user_agent` varchar(255) DEFAULT NULL,
  `clicked_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `property_type`
--

CREATE TABLE `property_type` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `property_type`
--

INSERT INTO `property_type` (`id`, `name`, `slug`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Căn hộ chung cư', 'can-ho-chung-cu', 1, 10, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(2, 'Chung cư mini', 'chung-cu-mini', 1, 20, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(3, 'Nhà', 'nha', 1, 30, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(4, 'Biệt thự', 'biet-thu', 1, 40, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(5, 'Nhà mặt phố', 'nha-mat-pho', 1, 50, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(6, 'Nhà trọ', 'nha-tro', 1, 60, '2026-05-13 20:38:31', '2026-05-13 20:38:31'),
(7, 'Văn phòng', 'van-phong', 1, 70, '2026-05-13 20:38:31', '2026-05-13 20:38:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user`
--

CREATE TABLE `user` (
  `uid` int(50) NOT NULL,
  `uname` varchar(100) NOT NULL,
  `uemail` varchar(100) NOT NULL,
  `uphone` varchar(20) NOT NULL,
  `upass` varchar(255) NOT NULL,
  `must_reset_password` tinyint(1) NOT NULL DEFAULT 0,
  `utype` varchar(50) NOT NULL,
  `blocked` tinyint(1) NOT NULL DEFAULT 0,
  `uimage` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Cấu trúc bảng cho bảng `wards`
--

CREATE TABLE `wards` (
  `wid` int(50) NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `wname` varchar(100) NOT NULL,
  `city_id` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `wards`
--

-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `about`
--
ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`aid`);

--
-- Chỉ mục cho bảng `chatbot_event`
--
ALTER TABLE `chatbot_event`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chatbot_event_user` (`user_uid`,`created_at`),
  ADD KEY `idx_chatbot_event_property` (`property_id`,`created_at`),
  ADD KEY `idx_chatbot_event_type` (`event_type`,`created_at`);

--
-- Chỉ mục cho bảng `chat_context_state`
--
ALTER TABLE `chat_context_state`
  ADD PRIMARY KEY (`session_id`);

--
-- Chỉ mục cho bảng `chat_message`
--
ALTER TABLE `chat_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_message_session` (`session_id`,`created_at`);

--
-- Chỉ mục cho bảng `chat_session`
--
ALTER TABLE `chat_session`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_chat_session_public_id` (`public_id`),
  ADD KEY `idx_chat_session_user` (`user_uid`),
  ADD KEY `idx_chat_session_updated` (`updated_at`);

--
-- Chỉ mục cho bảng `city`
--
ALTER TABLE `city`
  ADD PRIMARY KEY (`cid`),
  ADD UNIQUE KEY `uq_city_code` (`code`);

--
-- Chỉ mục cho bảng `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`cid`);

--
-- Chỉ mục cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`fid`),
  ADD KEY `idx_feedback_uid` (`uid`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_token_hash` (`token_hash`);

--
-- Chỉ mục cho bảng `property`
--
ALTER TABLE `property`
  ADD PRIMARY KEY (`pid`),
  ADD KEY `idx_property_uid` (`uid`),
  ADD KEY `idx_property_city_id` (`city_id`),
  ADD KEY `idx_property_type_id` (`type_id`),
  ADD KEY `idx_property_ward_id` (`ward_id`);

--
-- Chỉ mục cho bảng `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_property_amenity_property_id` (`property_id`),
  ADD KEY `idx_property_amenity_property_id` (`property_id`);

--
-- Chỉ mục cho bảng `property_embedding`
--
ALTER TABLE `property_embedding`
  ADD PRIMARY KEY (`property_id`);

--
-- Chỉ mục cho bảng `property_favorite`
--
ALTER TABLE `property_favorite`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_property` (`uid`,`pid`),
  ADD KEY `idx_favorite_uid` (`uid`),
  ADD KEY `idx_favorite_pid` (`pid`);

--
-- Chỉ mục cho bảng `property_inquiry`
--
ALTER TABLE `property_inquiry`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_property_inquiry_agent` (`agent_uid`),
  ADD KEY `idx_property_inquiry_property` (`property_id`),
  ADD KEY `idx_property_inquiry_created` (`created_at`),
  ADD KEY `idx_property_inquiry_status` (`status`),
  ADD KEY `idx_pi_appointment_status` (`appointment_status`),
  ADD KEY `idx_pi_workflow_updated_at` (`workflow_updated_at`),
  ADD KEY `idx_property_inquiry_appointment_status` (`appointment_status`),
  ADD KEY `idx_property_inquiry_created_at` (`created_at`),
  ADD KEY `idx_property_inquiry_workflow_updated_at` (`workflow_updated_at`),
  ADD KEY `idx_pi_case_status` (`case_status`),
  ADD KEY `fk_property_inquiry_inquirer` (`inquirer_uid`);

--
-- Chỉ mục cho bảng `property_inquiry_log`
--
ALTER TABLE `property_inquiry_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pil_inquiry_id` (`inquiry_id`),
  ADD KEY `idx_pil_action_key` (`action_key`),
  ADD KEY `idx_pil_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `property_owner_call_click`
--
ALTER TABLE `property_owner_call_click`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pocc_property` (`property_id`),
  ADD KEY `idx_pocc_owner_uid` (`owner_uid`),
  ADD KEY `idx_pocc_clicked_at` (`clicked_at`),
  ADD KEY `fk_property_owner_call_click_caller` (`caller_uid`);

--
-- Chỉ mục cho bảng `property_type`
--
ALTER TABLE `property_type`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_property_type_name` (`name`),
  ADD UNIQUE KEY `uq_property_type_slug` (`slug`),
  ADD KEY `idx_property_type_active_sort` (`is_active`,`sort_order`,`id`);

--
-- Chỉ mục cho bảng `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`uid`);

--
-- Chỉ mục cho bảng `wards`
--
ALTER TABLE `wards`
  ADD PRIMARY KEY (`wid`),
  ADD UNIQUE KEY `uq_wards_city_name` (`city_id`,`wname`),
  ADD UNIQUE KEY `uq_wards_code` (`code`),
  ADD KEY `idx_wards_city_id` (`city_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `about`
--
ALTER TABLE `about`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `aid` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chatbot_event`
--
ALTER TABLE `chatbot_event`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `chat_message`
--
ALTER TABLE `chat_message`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=783;

--
-- AUTO_INCREMENT cho bảng `chat_session`
--
ALTER TABLE `chat_session`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT cho bảng `city`
--
ALTER TABLE `city`
  MODIFY `cid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT cho bảng `contact`
--
ALTER TABLE `contact`
  MODIFY `cid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `feedback`
--
ALTER TABLE `feedback`
  MODIFY `fid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `property`
--
ALTER TABLE `property`
  MODIFY `pid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=638;

--
-- AUTO_INCREMENT cho bảng `property_amenity`
--
ALTER TABLE `property_amenity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=255;

--
-- AUTO_INCREMENT cho bảng `property_favorite`
--
ALTER TABLE `property_favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `property_inquiry`
--
ALTER TABLE `property_inquiry`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT cho bảng `property_inquiry_log`
--
ALTER TABLE `property_inquiry_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=244;

--
-- AUTO_INCREMENT cho bảng `property_owner_call_click`
--
ALTER TABLE `property_owner_call_click`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `property_type`
--
ALTER TABLE `property_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `user`
--
ALTER TABLE `user`
  MODIFY `uid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT cho bảng `wards`
--
ALTER TABLE `wards`
  MODIFY `wid` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13340;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chatbot_event`
--
ALTER TABLE `chatbot_event`
  ADD CONSTRAINT `fk_chatbot_event_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chat_context_state`
--
ALTER TABLE `chat_context_state`
  ADD CONSTRAINT `fk_chat_context_session` FOREIGN KEY (`session_id`) REFERENCES `chat_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `chat_message`
--
ALTER TABLE `chat_message`
  ADD CONSTRAINT `fk_chat_message_session` FOREIGN KEY (`session_id`) REFERENCES `chat_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `fk_feedback_user` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property`
--
ALTER TABLE `property`
  ADD CONSTRAINT `fk_property_city` FOREIGN KEY (`city_id`) REFERENCES `city` (`cid`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_type_id` FOREIGN KEY (`type_id`) REFERENCES `property_type` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_user` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_ward` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`wid`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_amenity`
--
ALTER TABLE `property_amenity`
  ADD CONSTRAINT `fk_property_amenity_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_embedding`
--
ALTER TABLE `property_embedding`
  ADD CONSTRAINT `fk_property_embedding_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_favorite`
--
ALTER TABLE `property_favorite`
  ADD CONSTRAINT `fk_property_favorite_property` FOREIGN KEY (`pid`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_favorite_user` FOREIGN KEY (`uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_inquiry`
--
ALTER TABLE `property_inquiry`
  ADD CONSTRAINT `fk_property_inquiry_agent` FOREIGN KEY (`agent_uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_inquiry_inquirer` FOREIGN KEY (`inquirer_uid`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_inquiry_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_inquiry_log`
--
ALTER TABLE `property_inquiry_log`
  ADD CONSTRAINT `fk_property_inquiry_log_inquiry` FOREIGN KEY (`inquiry_id`) REFERENCES `property_inquiry` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `property_owner_call_click`
--
ALTER TABLE `property_owner_call_click`
  ADD CONSTRAINT `fk_property_owner_call_click_caller` FOREIGN KEY (`caller_uid`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_owner_call_click_owner` FOREIGN KEY (`owner_uid`) REFERENCES `user` (`uid`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_property_owner_call_click_property` FOREIGN KEY (`property_id`) REFERENCES `property` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `wards`
--
ALTER TABLE `wards`
  ADD CONSTRAINT `fk_wards_city` FOREIGN KEY (`city_id`) REFERENCES `city` (`cid`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
