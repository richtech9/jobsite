/* Creates contests by users in the batch
  inserts rows into wp_posts,wp_postmeta,wp_fl_transaction
  */

DELIMITER $$
CREATE  PROCEDURE `create_random_contests`(IN number_contests_to_make bigint unsigned,
                                           IN test_batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE current_contest_id bigint unsigned;
    DECLARE test_contest_id bigint unsigned;
    DECLARE da_user_id bigint unsigned;
    DECLARE contest_loop_number int;
    DECLARE freelinguist_project_number int;


    DECLARE _project_name VARCHAR(50);
    DECLARE _project_title VARCHAR(50);
    DECLARE _project_deadline_days int;
    DECLARE _is_guaranteed int;

    DECLARE da_user_balance DOUBLE;
    DECLARE contest_fee DOUBLE;
    DECLARE random_thing int;

    DECLARE temp_index INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    DROP TEMPORARY TABLE IF EXISTS temp_contests_in_batch;

    CREATE TEMPORARY TABLE temp_contests_in_batch
    (
      id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      post_id               BIGINT UNSIGNED NOT NULL                COMMENT ' from wp_posts.ID ',
      user_id               BIGINT UNSIGNED NULL                COMMENT ' from wp_users.ID '
      COMMENT ' stores newly created contests for batch ',
      INDEX idx_post_id (post_id),
      INDEX idx_user_id (user_id)
    )
      ENGINE = MyISAM;

    # initialize constants

    SELECT option_value INTO freelinguist_project_number FROM wp_options where option_name = 'custom_pro_id';

    SELECT (MAX(ID) +1) into current_contest_id FROM wp_posts;



    SET _project_deadline_days := 7;  #change to add number of days into the future the project is due

    IF  number_contests_to_make < 0 THEN
      SET msg := CONCAT('number_projects_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;
    set contest_loop_number := 0;
    START TRANSACTION;
    contest_loop: WHILE (contest_loop_number < number_contests_to_make) DO
      # check to make sure id does not exist in users table, if it does, then skip it
      SELECT ID into test_contest_id FROM wp_posts WHERE ID = current_contest_id ;
      IF test_contest_id IS NOT NULL THEN
        set current_contest_id := current_contest_id + 1;
        ITERATE contest_loop;
      END IF ;

      # get random user from batch
      SELECT  FLOOR(RAND()*(number_contests_to_make)) + 1 INTO random_thing;

      SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
      WHERE p.id = random_thing;

      SELECT  FLOOR(RAND()*(1-0+1))+0 INTO _is_guaranteed;

      set freelinguist_project_number := freelinguist_project_number + 1;
      set _project_name := concat('C__18_', freelinguist_project_number);
      set _project_title := concat('c__18_', freelinguist_project_number);

      INSERT INTO wp_posts (ID, post_author, post_date, post_date_gmt, post_content,
                            post_title, post_excerpt, post_status, comment_status, ping_status,
                            post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt,
                            post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
      VALUES
        (current_contest_id, da_user_id, NOW(), NOW(), '', _project_name, '', 'publish', 'open', 'closed', '',
          _project_title, '', '', NOW(), NOW(), '', 0, 'http://test.com/job/test', 0, 'job',
         '', 0);
      SET contest_loop_number := contest_loop_number + 1;

      SELECT  FLOOR(RAND()*(200)) + 11.0 INTO contest_fee;
      # insert basic project meta

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'modified_id', _project_name);

      # 2020-07-24
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
      VALUES (NULL, current_contest_id, 'job_standard_delivery_date',
              DATE_FORMAT(DATE_ADD(NOW(), INTERVAL _project_deadline_days DAY), '%Y-%m-%d') );

      #create_batch
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'create_batch', test_batch_id);

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
      VALUES (NULL, current_contest_id, 'project_title', CONCAT('Test Contest ', freelinguist_project_number, ' batch ',
                                                                test_batch_id,'::',contest_loop_number));

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
      VALUES (NULL, current_contest_id, 'project_description',
              CONCAT('test contest generated via script: project batch of ', test_batch_id, ' , user batch of ', test_batch_id,'::',contest_loop_number));

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'estimated_budgets', CAST(contest_fee as signed));
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'fl_job_type', 'contest');
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'project_status', 'pending');

      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, '_bid_placed_by', 'empty');

      # example 20200804
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
      VALUES (NULL, current_contest_id, 'job_created_date', DATE_FORMAT(NOW(), '%Y%m%d'));




      #da contest stuff


      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'is_guaranted', _is_guaranteed); #0 randomly half the time
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, current_contest_id, 'contest_prize', 'deducted');



      #make sure da_user has a balance, if not add one, and then do transaction to charge for the contest amount (ignoring fees)

        #   see if wallet of project owner has amount, if not, then fill wallet with payment history and set user meta of total_user_balance
        SELECT CAST(meta_value as DECIMAL) INTO da_user_balance FROM wp_usermeta WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

        IF  da_user_balance < contest_fee THEN

          INSERT INTO wp_payment_history (id, txn_id, payment_amount, payment_status, description, payment_type,
                                          item_name, user_id,  refill_by,  created_time)
          VALUES (NULL, CONCAT('test-tx-',UUID()), 999999, 'Completed', null, 'Test', 'Refill', da_user_id, null, NOW());

          SET da_user_balance = 99999.00;

        END IF;


      INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                     user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
      VALUES (NULL, CONCAT('test-tx-',UUID()), -contest_fee, 'done', 'post competition', 'contest_created',
                    'wallet', '', da_user_id, da_user_id, current_contest_id ,0 , 0, NOW(), 0);


      #update balance
      SET da_user_balance := da_user_balance - contest_fee;
      UPDATE wp_usermeta set meta_value = (da_user_balance) WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

      INSERT INTO temp_contests_in_batch(post_id, user_id) VALUES (current_contest_id,da_user_id);
      SET temp_index := LAST_INSERT_ID();
      CALL create_random_project_tags(1,3,temp_index);
      set current_contest_id := current_contest_id + 1;
    END WHILE ;

    #update the custom option value so the other projects do not have the same numbers
    UPDATE wp_options set option_value = freelinguist_project_number where option_name = 'custom_pro_id';

    COMMIT;



  END$$
DELIMITER ;