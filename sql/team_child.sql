SELECT `xa_team`.`team_id`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
WHERE `xa_team`.`team_id` = ? AND `xa_follow`.`child_id` = ?