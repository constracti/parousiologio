<?php

exit( 'comment out line 3 to execute' );

# TODO: NULL if ''

require_once 'php/core.php';

$stmt = $db->prepare( '
SELECT `xa_event_sat`.`event_id` AS `sat_id`, `xa_event_sun`.`event_id` AS `sun_id`
FROM `xa_event` AS `xa_event_sun`
JOIN `xa_event` AS `xa_event_sat` ON `xa_event_sat`.`date` = `xa_event_sun`.`date` - INTERVAL 1 DAY
AND `xa_event_sat`.`season_id` = `xa_event_sun`.`season_id`
AND `xa_event_sat`.`on_saturday` AND NOT `xa_event_sun`.`on_saturday`
AND NOT `xa_event_sat`.`on_sunday` AND `xa_event_sun`.`on_sunday`
WHERE `xa_event_sat`.`name` = "Συγκέντρωση ομάδας" AND `xa_event_sun`.`name` = "Συγκέντρωση ομάδας"
' );
$stmt->execute();
$rslt = $stmt->get_result();
$stmt->close();
while ( !is_null( $item = $rslt->fetch_object() ) ) {
	$stmt = $db->prepare( 'UPDATE `xa_presence` SET `event_id` = ? WHERE `event_id` = ?' );
	$stmt->bind_param( 'ii', $item->sun_id, $item->sat_id );
	$stmt->execute();
	$stmt->close();
}
$rslt->free();
$db->query( 'DELETE FROM `xa_event` WHERE `name` = "Συγκέντρωση ομάδας" AND `on_saturday`' );
$db->query( 'UPDATE `xa_event` SET `name` = NULL WHERE `name` = "Συγκέντρωση ομάδας"' );

$db->query( 'DROP TABLE `xa_action`' );
$db->query( 'TRUNCATE `xa_epoint`' );
$db->query( '
INSERT INTO `xa_epoint` ( `epoint_id`, `user_id`, `hash`, `ins_tm`, `ins_ip`, `ins_ag`, `exp_tm`, `logins` )
SELECT `cookie_id`, `user_id`, `code_hash`, `insert_time`, `insert_ip`, `agent`, `expire_time`, 0
FROM `xa_cookie`
' );
$db->query( 'DROP TABLE `xa_cookie`' );
$db->query( 'ALTER TABLE `xa_event` DROP `on_saturday`' );
$db->query( 'ALTER TABLE `xa_event` DROP `on_sunday`' );
$db->query( 'DROP TABLE `xa_role`' );
$db->query( 'DROP TABLE `xa_theme`' );
$db->query( 'ALTER TABLE `xa_user` DROP `theme_id`' );
$db->query( 'ALTER TABLE `xa_user` DROP `inverse_navbar`' );
$db->query( 'TRUNCATE `xa_vlink`' );
$db->query( '
INSERT INTO `xa_vlink` ( `vlink_id`, `user_id`, `hash`, `type`, `data`, `ins_tm`, `ins_ip`, `ins_ag`, `act_tm`, `act_ip`, `act_ag`, `exp_tm` )
SELECT `verification_id`, `user_id`, `code`, `type`, `data`, `insert_time`, `insert_port`, `insert_agent`, `follow_time`, `follow_ip`, `follow_agent`, `expire_time`
FROM `xa_verification`
' );
$db->query( 'DROP TABLE `xa_verification`' );