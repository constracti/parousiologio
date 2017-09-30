<?php

exit( 'comment out line 3 to execute' );

require_once 'php/core.php';

$db->query( 'DROP TABLE `xa_action`;' );

$db->query( '
UPDATE TABLE `xa_child` SET `last_name` = "-" WHERE `last_name` = "";
UPDATE TABLE `xa_child` SET `first_name` = "-" WHERE `first_name` = "";
UPDATE TABLE `xa_child` SET `home_phone` = NULL WHERE `home_phone` = "";
UPDATE TABLE `xa_child` SET `mobile_phone` = NULL WHERE `mobile_phone` = "";
UPDATE TABLE `xa_child` SET `email_address` = NULL WHERE `email_address` = "";
UPDATE TABLE `xa_child` SET `school` = NULL WHERE `school` = "";
UPDATE TABLE `xa_child` SET `birth_year` = NULL WHERE `birth_year` = "";
UPDATE TABLE `xa_child` SET `fath_name` = NULL WHERE `fath_name` = "";
UPDATE TABLE `xa_child` SET `fath_occup` = NULL WHERE `fath_occup` = "";
UPDATE TABLE `xa_child` SET `fath_mobile` = NULL WHERE `fath_mobile` = "";
UPDATE TABLE `xa_child` SET `fath_email` = NULL WHERE `fath_email` = "";
UPDATE TABLE `xa_child` SET `moth_name` = NULL WHERE `moth_name` = "";
UPDATE TABLE `xa_child` SET `moth_occup` = NULL WHERE `moth_occup` = "";
UPDATE TABLE `xa_child` SET `moth_mobile` = NULL WHERE `moth_mobile` = "";
UPDATE TABLE `xa_child` SET `moth_email` = NULL WHERE `moth_email` = "";
UPDATE TABLE `xa_child` SET `address` = NULL WHERE `address` = "";
UPDATE TABLE `xa_child` SET `city` = NULL WHERE `city` = "";
UPDATE TABLE `xa_child` SET `postal_code` = NULL WHERE `postal_code` = "";
UPDATE TABLE `xa_child` SET `meta` = NULL WHERE `meta` = "";
' );

$db->query( '
TRUNCATE `xa_epoint`;
INSERT INTO `xa_epoint` ( `epoint_id`, `user_id`, `hash`, `ins_tm`, `ins_ip`, `ins_ag`, `exp_tm`, `logins` )
SELECT `cookie_id`, `user_id`, `code_hash`, `insert_time`, `insert_ip`, `agent`, `expire_time`, 0
FROM `xa_cookie`;
DROP TABLE `xa_cookie`;
' );

# TODO rename `date` to `event_date` and `name` to `event_name`
$db->query( 'UPDATE `xa_event` SET `name` = NULL WHERE `name` = "Συγκέντρωση ομάδας";' );
$stmt = $db->prepare( '
SELECT `xa_event_sat`.`event_id` AS `sat_id`, `xa_event_sun`.`event_id` AS `sun_id`
FROM `xa_event` AS `xa_event_sun`
JOIN `xa_event` AS `xa_event_sat` ON `xa_event_sat`.`date` = `xa_event_sun`.`date` - INTERVAL 1 DAY
AND `xa_event_sat`.`season_id` = `xa_event_sun`.`season_id`
AND `xa_event_sat`.`on_saturday` AND NOT `xa_event_sun`.`on_saturday`
AND NOT `xa_event_sat`.`on_sunday` AND `xa_event_sun`.`on_sunday`
WHERE `xa_event_sat`.`name` IS NULL AND `xa_event_sun`.`name` IS NULL;
' );
$stmt->execute();
$rslt = $stmt->get_result();
$stmt->close();
while ( !is_null( $item = $rslt->fetch_object() ) ) {
	$stmt = $db->prepare( 'UPDATE `xa_presence` SET `event_id` = ? WHERE `event_id` = ?;' );
	$stmt->bind_param( 'ii', $item->sun_id, $item->sat_id );
	$stmt->execute();
	$stmt->close();
}
$rslt->free();
$db->query( 'DELETE FROM `xa_event` WHERE `name` IS NULL AND `on_saturday`;' );
$db->query( 'UPDATE TABLE `xa_event` SET `name` = NULL WHERE `name` = "";' );
$db->query( 'ALTER TABLE `xa_event` DROP `on_saturday`;' );
$db->query( 'ALTER TABLE `xa_event` DROP `on_sunday`;' );

$db->query( 'DROP TABLE `xa_role`;' );

$db->query( 'DROP TABLE `xa_theme`;' );

# TODO decrease `role_id` by 1 and rename to `role`
# TODO rename `active_*` to `act_*` AND `*_time` to `*_tm`
$db->query( '
UPDATE TABLE `xa_user` SET `password_hash` = NULL WHERE `password_hash` = "";
UPDATE TABLE `xa_user` SET `last_name` = NULL WHERE `last_name` = "";
UPDATE TABLE `xa_user` SET `first_name` = NULL WHERE `first_name` = "";
UPDATE TABLE `xa_user` SET `home_phone` = NULL WHERE `home_phone` = "";
UPDATE TABLE `xa_user` SET `mobile_phone` = NULL WHERE `mobile_phone` = "";
UPDATE TABLE `xa_user` SET `occupation` = NULL WHERE `occupation` = "";
UPDATE TABLE `xa_user` SET `first_year` = NULL WHERE `first_year` = "";
UPDATE TABLE `xa_user` SET `address` = NULL WHERE `address` = "";
UPDATE TABLE `xa_user` SET `city` = NULL WHERE `city` = "";
UPDATE TABLE `xa_user` SET `postal_code` = NULL WHERE `postal_code` = "";
ALTER TABLE `xa_user` DROP `theme_id`;
ALTER TABLE `xa_user` DROP `inverse_navbar`;
UPDATE TABLE `xa_user` SET `reg_time` = NULL WHERE `reg_time` = "0000-00-00 00:00:00";
UPDATE TABLE `xa_user` SET `reg_ip` = NULL WHERE `reg_ip` = "0.0.0.0" OR `reg_ip` = "";
UPDATE TABLE `xa_user` SET `active_time` = NULL WHERE `active_time` = "0000-00-00 00:00:00";
UPDATE TABLE `xa_user` SET `active_ip` = NULL WHERE `active_ip` = "0.0.0.0" OR `reg_ip` = "";
' );

$db->query( '
TRUNCATE `xa_vlink`;
INSERT INTO `xa_vlink` ( `vlink_id`, `user_id`, `hash`, `type`, `data`, `ins_tm`, `ins_ip`, `ins_ag`, `act_tm`, `act_ip`, `act_ag`, `exp_tm` )
SELECT `verification_id`, `user_id`, `code`, `type`, `data`, `insert_time`, `insert_port`, `insert_agent`, `follow_time`, `follow_ip`, `follow_agent`, `expire_time`
FROM `xa_verification`;
DROP TABLE `xa_verification`;
' );