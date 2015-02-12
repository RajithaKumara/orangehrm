
INSERT INTO `ohrm_config`(`property`, `value`) VALUES ('buzz_refresh_time','60000'),
        ('buzz_share_count','10'),
        ('buzz_initial_comments','2'),
        ('buzz_viewmore_comment','5'),
        ('buzz_like_count','5'),
        ('buzz_time_format','h:i a'),
        ('buzz_most_like_posts','5'),
        ('buzz_post_text_lenth','500'),
        ('buzz_post_text_lines','5'),
        ('buzz_cookie_valid_time','5000'),
        ('buzz_most_like_shares','5');
--
-- Inserting News feed Module to The Database
--
INSERT INTO `ohrm_module`( `name`, `status`) VALUES ('buzz','0');
INSERT INTO `ohrm_data_group` (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`) VALUES
('buzz_link', 'buzz link permition ', 1, 1, 1, 0);

SET @buzz_link_data_group_id := (SELECT id FROM `ohrm_data_group` WHERE `name` = 'buzz_link');

INSERT INTO `ohrm_user_role_data_group` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) VALUES
(2, @buzz_link_data_group_id, 1, 1, 1, 0, 0),
(3, @buzz_link_data_group_id, 1, 1, 1, 0, 0);

INSERT INTO `ohrm_data_group` (`name`, `description`, `can_read`, `can_create`, `can_update`, `can_delete`) VALUES
('buzz_link_admin', 'buzz link permition for admin', 1, 1, 1, 0);

SET @buzz_link_admin_data_group_id := (SELECT id FROM `ohrm_data_group` WHERE `name` = 'buzz_link_admin');

INSERT INTO `ohrm_user_role_data_group` (`user_role_id`, `data_group_id`, `can_read`, `can_create`, `can_update`, `can_delete`, `self`) VALUES
(1, @buzz_link_admin_data_group_id, 1, 1, 1, 0, 0);
-- SET @buzz_module_id := (SELECT `id` FROM `ohrm_module` WHERE `name` = 'buzz');

-- INSERT INTO `ohrm_screen`(`name`, `module_id`, `action_url`) VALUES 
-- ('buzz',@buzz_module_id,'viewBuzz');

-- SET @buzz_screen_id := (SELECT `id` FROM ohrm_screen WHERE `name` = 'buzz');
-- SET @ess_user_role_id :=(SELECT `id` FROM `ohrm_user_role` WHERE `name`='ESS');
-- SET @admin_user_role_id :=(SELECT `id` FROM `ohrm_user_role` WHERE `name`='Admin');
-- SET @supervisor_user_role_id :=(SELECT `id` FROM `ohrm_user_role` WHERE `name`='Supervisor');

-- INSERT INTO ohrm_user_role_screen (user_role_id, screen_id, can_read, can_create, can_update, can_delete)
--  VALUES    (@admin_user_role_id, @buzz_screen_id, 1, 1, 1, 0),
--            (@supervisor_user_role_id, @buzz_screen_id, 1, 1, 1, 0),
--             (@ess_user_role_id, @buzz_screen_id, 1, 1, 1, 0);

-- INSERT INTO `ohrm_menu_item`( `menu_title`, `screen_id`, `level`, `order_hint`) VALUES 
--                 ('News Feed',@buzz_screen_id,'1','100');

--
-- Table structure for table `ohrm_buzz_post`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_post` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `employee_number` int(7) ,
  `text` text ,
  `post_time` datetime NOT NULL,
  `updated_at` timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_post`
--
ALTER TABLE `ohrm_buzz_post`
  ADD CONSTRAINT `buzzPostEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_share`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_share` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `number_of_likes` int(6) DEFAULT NULL,
  `number_of_unlikes` int(6) DEFAULT NULL,
  `number_of_comments` int(6) DEFAULT NULL,
  `share_time` datetime NOT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `text` text,
  `updated_at` timestamp ON UPDATE CURRENT_TIMESTAMP,   
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_share`
--
ALTER TABLE `ohrm_buzz_share`
  ADD CONSTRAINT `buzzShareEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzSharePost` FOREIGN KEY (`post_id`) 
    REFERENCES `ohrm_buzz_post` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_comment`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_comment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `share_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `number_of_likes` int(6) DEFAULT NULL,
  `number_of_unlikes` int(6) DEFAULT NULL,
  `comment_text` text,
  `comment_time` datetime NOT NULL,
  `updated_at` timestamp ON UPDATE CURRENT_TIMESTAMP, 
  PRIMARY KEY (`id`),
  KEY `share_id` (`share_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_comment`
--
ALTER TABLE `ohrm_buzz_comment`
  ADD CONSTRAINT `buzzComentedEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzComentOnShare` FOREIGN KEY (`share_id`) 
    REFERENCES `ohrm_buzz_share` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_like_on_comment`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_like_on_comment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `like_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_comment_like`
--
ALTER TABLE `ohrm_buzz_like_on_comment`
  ADD CONSTRAINT `buzzCommentLikeEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzLikeOnComment` FOREIGN KEY (`comment_id`) 
    REFERENCES `ohrm_buzz_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_share_like`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_like_on_share` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `share_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `like_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `share_id` (`share_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_share_like`
--
ALTER TABLE `ohrm_buzz_like_on_share`
  ADD CONSTRAINT `buzzShareLikeEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzLikeOnshare` FOREIGN KEY (`share_id`) 
    REFERENCES `ohrm_buzz_share` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_photo`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_photo` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `photo` mediumblob,
  `filename` varchar(100) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `width` varchar(20) DEFAULT NULL,
  `height` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachment_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_photo`
--
ALTER TABLE `ohrm_buzz_photo`
  ADD CONSTRAINT `photoAttached` FOREIGN KEY (`post_id`) 
    REFERENCES `ohrm_buzz_post` (`id`) ON DELETE CASCADE;

--
-- Table structure for table `ohrm_buzz_link`
--

CREATE TABLE IF NOT EXISTS `ohrm_buzz_link` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `link` text NOT NULL,
  `type` tinyint(2) DEFAULT NULL,
  `title` VARCHAR( 600 ) NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `attachment_id` (`post_id`),
  KEY `photo_id` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_link`
--
ALTER TABLE `ohrm_buzz_link`
  ADD CONSTRAINT `linkAttached` FOREIGN KEY (`post_id`) 
    REFERENCES `ohrm_buzz_post` (`id`) ON DELETE CASCADE;

--
-- Table structure for table `ohrm_buzz_unlike_on_comment`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_unlike_on_comment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `like_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_comment_like`
--
ALTER TABLE `ohrm_buzz_unlike_on_comment`
  ADD CONSTRAINT `buzzCommentUnLikeEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzUnLikeOnComment` FOREIGN KEY (`comment_id`) 
    REFERENCES `ohrm_buzz_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Table structure for table `ohrm_buzz_share_like`
--
CREATE TABLE IF NOT EXISTS `ohrm_buzz_unlike_on_share` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `share_id` bigint(20) NOT NULL,
  `employee_number` int(7) ,
  `like_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `share_id` (`share_id`),
  KEY `employee_number` (`employee_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
--
-- Constraints for table `ohrm_buzz_share_like`
--
ALTER TABLE `ohrm_buzz_unlike_on_share`
  ADD CONSTRAINT `buzzShareUnLikeEmployee` FOREIGN KEY (`employee_number`) 
    REFERENCES `hs_hr_employee` (`emp_number`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `buzzUNLikeOnshare` FOREIGN KEY (`share_id`) 
    REFERENCES `ohrm_buzz_share` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;