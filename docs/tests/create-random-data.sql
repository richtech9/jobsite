# Calls all the sub procedures to insert test rows. Pass the number to create for each row.
#  If null passed then will look for _test_rows_default in WP options, defaults to 1 if not set
# for each sub procedure, will look for option, if not set, will use the default
# Finally, after all params are set, will call each sub procedure

DELIMITER $$
CREATE  PROCEDURE `create_random_data`(IN number_to_create bigint unsigned,batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE default_amount_to_create int;
    DECLARE number_users_to_create int;

    DECLARE number_projects_to_create int;
    DECLARE number_contests_to_create int;
    DECLARE number_content_to_create int;
    DECLARE number_project_tags_to_create int;
    DECLARE number_proposals_to_create int;
    DECLARE number_bids_to_create int;
    DECLARE percentage_hired int;
    DECLARE number_milestones_to_create int;

        DROP TEMPORARY TABLE IF EXISTS temp_users_in_batch;
        DROP TEMPORARY TABLE IF EXISTS temp_projects_in_batch;
        DROP TEMPORARY TABLE IF EXISTS temp_contests_in_batch;
        DROP TEMPORARY TABLE IF EXISTS temp_contents_in_batch;


    IF number_to_create IS NULL THEN
      SELECT option_value INTO default_amount_to_create FROM wp_options WHERE option_name = '_test_rows_default';
      if (default_amount_to_create IS NULL) THEN
        SET default_amount_to_create := 1;
      END IF;
    ELSEIF   number_to_create < 0 THEN
      SET msg := CONCAT('create_random_data=> number_to_create needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    ELSE
      SET default_amount_to_create := number_to_create;
    END IF;


    IF batch_id IS NULL OR batch_id = '' THEN
      SET batch_id := CONCAT('test-batch-',UUID());
    END IF ;




    SELECT option_value INTO number_users_to_create FROM wp_options WHERE option_name = '_test_rows_users';
    if (number_users_to_create IS NULL) THEN
      SET number_users_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO number_projects_to_create FROM wp_options WHERE option_name = '_test_rows_projects';
    if (number_projects_to_create IS NULL) THEN
      SET number_projects_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO number_content_to_create FROM wp_options WHERE option_name = '_test_rows_content';
    if (number_content_to_create IS NULL) THEN
      SET number_content_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO number_contests_to_create FROM wp_options WHERE option_name = '_test_rows_contests';
    if (number_contests_to_create IS NULL) THEN
      SET number_contests_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO number_project_tags_to_create FROM wp_options WHERE option_name = '_test_number_tags';
    if (number_project_tags_to_create IS NULL) THEN
      SET number_project_tags_to_create := default_amount_to_create ;
    END IF;

    SELECT option_value INTO number_proposals_to_create FROM wp_options WHERE option_name = '_test_rows_proposals';
    if (number_proposals_to_create IS NULL) THEN
      SET number_proposals_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO number_bids_to_create FROM wp_options WHERE option_name = '_test_rows_bids';
    if (number_bids_to_create IS NULL) THEN
      SET number_bids_to_create := default_amount_to_create;
    END IF;

    SELECT option_value INTO percentage_hired FROM wp_options WHERE option_name = '_test_rows_percent_hired';
    if (percentage_hired IS NULL) THEN
      SET percentage_hired := 50;
    END IF;

    SELECT option_value INTO number_milestones_to_create FROM wp_options WHERE option_name = '_test_rows_milestones';
    if (number_milestones_to_create IS NULL) THEN
      SET number_milestones_to_create := default_amount_to_create;
    END IF;

    CALL create_random_users(number_users_to_create,batch_id);
    CALL create_random_projects(number_projects_to_create,batch_id);
    CALL create_random_contests(number_contests_to_create,batch_id);
    CALL create_random_content(number_content_to_create,batch_id);
    CALL create_random_proposals(number_proposals_to_create);
    CALL create_random_bids_and_hires(number_bids_to_create,percentage_hired);
    CALL create_random_milestones_and_payments(number_milestones_to_create);




  END$$
DELIMITER ;