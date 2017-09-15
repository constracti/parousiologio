SELECT `xa_event`.*
FROM `xa_team`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id`
JOIN `xa_participate` ON `xa_team`.`location_id` = `xa_participate`.`location_id`
JOIN `xa_event` ON `xa_event`.`event_id` = `xa_participate`.`event_id` AND `xa_event`.`season_id` = `xa_team`.`season_id`
JOIN `xa_regard` ON `xa_regard`.`grade_id` = `xa_target`.`grade_id` AND `xa_regard`.`event_id` = `xa_event`.`event_id`
WHERE `xa_team`.`team_id` = ?
ORDER BY `xa_event`.`date` ASC, `xa_event`.`event_id` ASC;