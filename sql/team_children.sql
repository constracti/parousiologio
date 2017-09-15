SELECT `xa_child`.*, `xa_grade`.`grade_name`
FROM `xa_team`
JOIN `xa_follow` ON `xa_team`.`location_id` = `xa_follow`.`location_id` AND `xa_team`.`season_id` = `xa_follow`.`season_id`
JOIN `xa_child` ON `xa_child`.`child_id` = `xa_follow`.`child_id`
JOIN `xa_target` ON `xa_team`.`team_id` = `xa_target`.`team_id` AND `xa_follow`.`grade_id` = `xa_target`.`grade_id`
JOIN `xa_grade` ON `xa_follow`.`grade_id` = `xa_grade`.`grade_id`
WHERE `xa_team`.`team_id` = ?
ORDER BY `xa_child`.`last_name` ASC, `xa_child`.`first_name` ASC, `xa_child`.`child_id` ASC