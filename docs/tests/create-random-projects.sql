/* Creates projects by users in the batch
  inserts rows into wp_posts,wp_postmeta
  */

DELIMITER $$
CREATE  PROCEDURE `create_random_projects`(IN number_projects_to_make bigint unsigned,
  IN test_batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE current_project_id bigint unsigned;
    DECLARE test_project_id bigint unsigned;
    DECLARE da_user_id bigint unsigned;
    DECLARE project_loop_count int;
    DECLARE freelinguist_project_number int;
    DECLARE random_thing int;

    DECLARE _project_name VARCHAR(50);
    DECLARE _project_title VARCHAR(50);
    DECLARE _project_deadline_days int;

    DECLARE budget_min DOUBLE;
    DECLARE budget_max DOUBLE;

    DECLARE temp_index INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    # initialize constants

    DROP TEMPORARY TABLE IF EXISTS temp_projects_in_batch;

    CREATE TEMPORARY TABLE temp_projects_in_batch
    (
      id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      post_id               BIGINT UNSIGNED NOT NULL                COMMENT ' from wp_posts.ID ',
      user_id               BIGINT UNSIGNED NULL                COMMENT ' from wp_users.ID '
      COMMENT ' stores newly created projects for batch ',
      INDEX idx_post_id (post_id),
      INDEX idx_user_id (user_id)
    )
      ENGINE = MyISAM;



    SELECT option_value INTO freelinguist_project_number FROM wp_options where option_name = 'custom_pro_id';

    SELECT (MAX(ID) +1) into current_project_id FROM wp_posts;

    SET _project_deadline_days := 7;  #change to add number of days into the future the project is due

    IF  number_projects_to_make < 0 THEN
      SET msg := CONCAT('number_projects_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    set project_loop_count := 0;
    START TRANSACTION;
    project_loop: WHILE (project_loop_count < number_projects_to_make  ) DO
      # check to make sure id does not exist in users table, if it does, then skip it
      SELECT ID into test_project_id FROM wp_posts WHERE ID = current_project_id ;
      IF test_project_id IS NOT NULL THEN
        set current_project_id := current_project_id + 1;
        ITERATE project_loop;
      END IF ;

      # get random user from batch
      SELECT  FLOOR(RAND()*(number_projects_to_make)) + 1 INTO random_thing;

      SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
      WHERE p.id = random_thing;


      set freelinguist_project_number := freelinguist_project_number + 1;
      set _project_name := concat('P__18_', freelinguist_project_number);
      set _project_title := concat('p__18_', freelinguist_project_number);

      INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content,
                            post_title, post_excerpt, post_status, comment_status, ping_status,
                            post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt,
                            post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
      VALUES
        (current_project_id, da_user_id, NOW(), NOW(), '', _project_name, '', 'publish', 'open', 'closed', '',
          _project_title, '', '', NOW(), NOW(), '', 0, 'http://test.com/job/test', 0, 'job',
         '', 0);
      SET project_loop_count := project_loop_count + 1;
      # insert basic project meta

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, 'modified_id', _project_name);

      # 2020-07-24
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
        VALUES (NULL, current_project_id, 'job_standard_delivery_date',
                DATE_FORMAT(DATE_ADD(NOW(), INTERVAL _project_deadline_days DAY), '%Y-%m-%d') );

      #create_batch
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, 'create_batch', test_batch_id);

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
        VALUES (NULL, current_project_id, 'project_title', CONCAT('Test Project ',freelinguist_project_number,' batch ',test_batch_id,'::', project_loop_count));

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
        VALUES (NULL, current_project_id, 'project_description',
                CONCAT('test project generated via script: project batch of ',test_batch_id, '  :: ', project_loop_count));

      SELECT  FLOOR(RAND()*(100)) + 2.0 INTO budget_min;
      SELECT  FLOOR(RAND()*(200)) + budget_min INTO budget_max;

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, 'estimated_budgets',CONCAT(budget_min, '_',budget_max));
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, 'fl_job_type', 'project');
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, 'project_status', 'project_completed');

      #todo try to place bids for project (php serialization? worth it??)
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_project_id, '_bid_placed_by', 'empty');

      # example 20200804
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
        VALUES (NULL, current_project_id, 'job_created_date', DATE_FORMAT(NOW(), '%Y%m%d'));


      INSERT INTO temp_projects_in_batch(post_id, user_id) VALUES (current_project_id,da_user_id);
      SET temp_index := LAST_INSERT_ID();
      CALL create_random_project_tags(1,1,temp_index);
      set current_project_id := current_project_id + 1;
    END WHILE ;

    #update the custom option value so the other projects do not have the same numbers
    UPDATE wp_options set option_value = freelinguist_project_number where option_name = 'custom_pro_id';

    COMMIT;



  END$$
DELIMITER ;