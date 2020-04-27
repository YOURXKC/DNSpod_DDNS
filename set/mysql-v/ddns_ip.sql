/*
 Navicat Premium Data Transfer

 Source Server         : ddnsip
 Source Server Type    : MySQL
 Source Server Version : 80016
 Source Host           : 192.168.1.41:3306
 Source Schema         : ddns_ip

 Target Server Type    : MySQL
 Target Server Version : 80016
 File Encoding         : 65001

 Date: 28/04/2020 02:44:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ip_table
-- ----------------------------
DROP TABLE IF EXISTS `ip_table`;
CREATE TABLE `ip_table`  (
  `id` int(11) NOT NULL,
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_croatian_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ip_table
-- ----------------------------
INSERT INTO `ip_table` VALUES (1, '0.0.0.0');

SET FOREIGN_KEY_CHECKS = 1;
