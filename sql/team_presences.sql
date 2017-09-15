SELECT `xa_child`.`child_id`, `xa_event`.`event_id`, `xa_presence`.`child_id` IS NOT NULL AS `check`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_child` ON `xa_child`.`child_id` = `xa_follow`.`child_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_follow`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
LEFT JOIN `xa_presence` ON `xa_child`.`child_id` = `xa_presence`.`child_id` AND `xa_event`.`event_id` = `xa_presence`.`event_id`
WHERE `xa_team`.`team_id` = ?
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC, `xa_event`.`date` ASC, `xa_event`.`event_id` ASC;