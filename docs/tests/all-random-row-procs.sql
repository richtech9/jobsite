-- code-notes version 1.01
-- HISTORY
-- version 1.01 updated for new payment table

/* Create Procedure to make test users : inserts rows into wp_users, wp_usermeta */

DELIMITER $$
CREATE  PROCEDURE `create_random_users`(IN number_users_to_create bigint unsigned,batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE current_user_id bigint unsigned;
    DECLARE test_user_id bigint unsigned;
    DECLARE number_users_created int;

    DECLARE _xmpp_guest_username VARCHAR(50);
    DECLARE _xmpp_guest_password VARCHAR(50);

    DECLARE _email VARCHAR(50);
    DECLARE _name_base VARCHAR(50);
    DECLARE _hashed_pw VARCHAR(50);

    DECLARE temp_index INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    DROP TEMPORARY TABLE IF EXISTS temp_users_in_batch;

    CREATE TEMPORARY TABLE temp_users_in_batch
    (
      id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      user_id               BIGINT UNSIGNED NULL                COMMENT ' from wp_users.ID '
      COMMENT ' stores newly created users for batch ',
      INDEX idx_user_id (user_id)
    )
      ENGINE = MyISAM;

    # initialize constants
    set _hashed_pw := '$P$BMc51GoMb5AzlZ.QH5gmaSVRF.fnZO/';
    set _xmpp_guest_username := 'AnnouncementforGuest-1001';
    set _xmpp_guest_password := 'YzodNNn6';
    SELECT (MAX(ID) +1) into current_user_id FROM wp_users;


    IF number_users_to_create < 0 THEN
      SET msg := CONCAT('number_users_to_create needs to be greater than zero ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;
    set number_users_created := 0;
    start transaction;
    user_loop: WHILE (number_users_to_create > 0) DO
      # check to make sure id does not exist in users table, if it does, then skip it
      SELECT ID into test_user_id FROM wp_users WHERE ID = current_user_id ;
      IF test_user_id IS NOT NULL THEN
        set current_user_id := current_user_id + 1;
        ITERATE user_loop;
      END IF ;
      set _email = concat('test-user-', current_user_id, '@test.com');
      set _name_base = concat('test-user-', current_user_id);
      set number_users_to_create := number_users_to_create -1;

      INSERT INTO wp_users(ID, user_login, user_pass, user_nicename, user_email, user_url, user_registered, user_activation_key, user_status, display_name)
      VALUES(NULL,_name_base,_hashed_pw,_name_base,_email,'',NOW(),'',0,_name_base);
      SET current_user_id :=  last_insert_id();
      SET number_users_created := number_users_created + 1;
      #insert basic meta
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'nickname', _name_base);
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'first_name', concat(_name_base,'-first'));
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'last_name',  concat(_name_base,'-last'));
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'description', 'Test user generated automatically');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'rich_editing', 'true');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'syntax_highlighting', 'true');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'comment_shortcuts', 'false');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'admin_color', 'fresh');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'use_ssl', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'show_admin_bar_front', 'true');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'locale', '');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'wp_capabilities', 'a:1:{s:10:"translator";b:1;}');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'wp_user_level', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'xmpp_username', _xmpp_guest_username);
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'xmpp_password', _xmpp_guest_password);
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'create_batch', batch_id);
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'dismissed_wp_pointers', '');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, '_user_type', 'translator');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'has_to_be_activated', '');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_processing_id', '39');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_residence_country', '224');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_translater_rating', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_customer_rating', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'translator_success_rate', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'customer_success_rate', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'wp_nav_menu_recently_edited', '4');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'total_user_balance', '0');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_phone', '9365551212');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'user_description', 'test user');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, '_signed_tax_form', '');
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'display_name',   concat(_name_base,'-display-name')); -- ',
      INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, current_user_id, 'last_login_time', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s'));

      INSERT into temp_users_in_batch(user_id) VALUES (current_user_id);
      SET temp_index := LAST_INSERT_ID();
      CALL create_random_project_tags(1,4,temp_index);
      set current_user_id := current_user_id + 1;
    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;

-- code-notes projects

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

      # add 30 days to now, timestamp
      INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value)
      VALUES (NULL, current_project_id, 'automatic_job_canceled_time', UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 30 DAY)));


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

-- code-notes contests

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


-- code-notes content

/* Creates random content for testing
  INSERTS into wp_linguist_content,wp_linguist_content_chapter
  */

DELIMITER $$
CREATE  PROCEDURE `create_random_content`(IN number_content_to_make bigint unsigned,
                                          IN test_batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE current_content_id bigint unsigned;
    DECLARE test_contest_id bigint unsigned;
    DECLARE da_user_id bigint unsigned;
    DECLARE loop_content_number int;
    DECLARE random_thing int;


    DECLARE content_title TEXT;
    DECLARE content_summary TEXT;
    DECLARE number_of_chapters_to_add int;
    DECLARE content_price double;

    DECLARE chapter_number int;
    DECLARE chapter_title TEXT;
    DECLARE chapter_content TEXT;

    DECLARE temp_index INT;


    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    DROP TEMPORARY TABLE IF EXISTS temp_contents_in_batch;

    CREATE TEMPORARY TABLE temp_contents_in_batch
    (
      id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      content_id           int NOT NULL                COMMENT ' from wp_linguist_content.id ',
      user_id               BIGINT UNSIGNED NULL                COMMENT ' from wp_users.ID '
      COMMENT ' stores newly created content for batch ',
      INDEX idx_content_id (content_id),
      INDEX idx_user_id (user_id)
    )
      ENGINE = MyISAM;

    # initialize constants


    SELECT (MAX(ID) +1) into current_content_id FROM wp_linguist_content;



    IF  number_content_to_make < 0 THEN
      SET msg := CONCAT('number_content_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;
    set loop_content_number := 0;
    START TRANSACTION;
    content_loop: WHILE (loop_content_number < number_content_to_make) DO
      # check to make sure id does not exist in users table, if it does, then skip it
      SELECT ID into test_contest_id FROM wp_linguist_content WHERE ID = current_content_id ;
      IF test_contest_id IS NOT NULL THEN
        set current_content_id := current_content_id + 1;
        ITERATE content_loop;
      END IF ;

      # get random user from batch
      SELECT  FLOOR(RAND()*(number_content_to_make)) + 1 INTO random_thing;

      SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
      WHERE p.id = random_thing;

      set content_summary := concat('Test Content #', current_content_id, ' batch ', test_batch_id, '::',
                                    loop_content_number, ' ', uuid());
      set content_title := concat('Test Content # ', current_content_id, ' batch ', test_batch_id, '::',
                                  loop_content_number);
      SELECT  FLOOR(RAND()*(200))+20 INTO content_price;
      SELECT  FLOOR(RAND()*(5)) INTO number_of_chapters_to_add;

      INSERT INTO wp_linguist_content (id, user_id, content_title, content_summary,
                                       content_amount, publish_type, content_sale_type,
                                       content_cover_image, description_image, files, content_type, content_view,
                                        updated_at, created_at, offersBy, purchased_by, status,
                                       show_content, revision_text, rejection_txt, rejected_at, rejection_requested,
                                       purchased_at, freezed, rating_by_customer, rating_by_freelancer,
                                       comments_by_customer, comments_by_freelancer)
      VALUES (current_content_id, da_user_id, content_title,
                                  content_summary,
                                  content_price, 'Publish', 'Fixed',  'linguistcontent/this-is-only-a-test.png',
                                  '/jobs/jobs/content-image-704324192.png', null, 0x0, 2,  NOW(), NOW(), '',
                                                                                  0, 'pending', 1, '', null, null, '0', NOW(),
              '0', null, null, null, null);


      INSERT INTO temp_contents_in_batch(content_id, user_id) VALUES (current_content_id,da_user_id);
      SET temp_index := LAST_INSERT_ID();
      CALL create_random_project_tags(1,2,temp_index);
      SET loop_content_number := loop_content_number + 1;
      SET chapter_number := 0;
      #insert chapters
      chapter_loop: WHILE (number_of_chapters_to_add > 0) DO
        SET chapter_number := chapter_number + 1;
        SET chapter_title := CONCAT('Chapter ',chapter_number, ' of test book id ',current_content_id);
        SET chapter_content := CONCAT('The content: :',uuid());
        INSERT INTO wp_linguist_content_chapter (id, user_id, linguist_content_id, title, content, content_visible, updated_at, created_at)
        VALUES (NULL, da_user_id, current_content_id, chapter_title, chapter_content, '',NOW(), NOW());

        SET  number_of_chapters_to_add :=  number_of_chapters_to_add - 1;
      END WHILE ;

      set current_content_id := current_content_id + 1;

    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;


-- code-notes project tags

/*
Create random procedure tags: 25% each type: user,contest,project, content. Each using a random tag
Can create tags for one specific item by passing in (1,4,23) for example:
              to add to one user whose index in the temp table is 23
*/

DELIMITER $$
CREATE  PROCEDURE `create_random_project_tags`(IN number_to_make bigint unsigned,
                                               IN force_type_to_use int,
                                               IN force_index int
)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;


    DECLARE tag_id_string TEXT;
    DECLARE number_tags_to_use int;
    DECLARE number_choice_for_type int;
    DECLARE id_to_link bigint;
    DECLARE unsigned_id_to_link bigint unsigned;
    DECLARE type_to_use int;
    DECLARE loop_count int;
    DECLARE random_thing int;

    DECLARE tag_1 BIGINT;
    DECLARE tag_2 BIGINT;
    DECLARE tag_3 BIGINT;




    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_project_tags needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    START TRANSACTION;

    SET loop_count := 0;

    project_tag_loop: WHILE (loop_count < number_to_make) DO
      SET loop_count := loop_count + 1;
      SET tag_1 := NULL;
      SET tag_2 := NULL;
      SET tag_3 := NULL;
      SET tag_id_string := NULL;

      #get the random tag string to insert (1 to 3 tags randomly chosen, separated by commas )
      SELECT  FLOOR(RAND()*(3)) + 1 INTO number_tags_to_use;

      CASE number_tags_to_use
        WHEN 1 THEN

        SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
        WHERE 1
        ORDER BY RAND()
        LIMIT 1;

        SET tag_id_string := tag_1;

        WHEN 2 THEN

        SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
        WHERE 1
        ORDER BY RAND()
        LIMIT 1;

        SELECT t.ID  INTO tag_2 FROM wp_interest_tags t
        WHERE t.ID <> tag_1
        ORDER BY RAND()
        LIMIT 1;

        SET tag_id_string := CONCAT(tag_1,',',tag_2);

        WHEN 3 THEN
        SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
        WHERE 1
        ORDER BY RAND()
        LIMIT 1;

        SELECT t.ID  INTO tag_2 FROM wp_interest_tags t
        WHERE t.ID <> tag_1
        ORDER BY RAND()
        LIMIT 1;

        SELECT t.ID  INTO tag_3 FROM wp_interest_tags t
        WHERE t.ID <> tag_1 AND t.ID <> tag_2
        ORDER BY RAND()
        LIMIT 1;

        SET tag_id_string := CONCAT(tag_1,',',tag_2,',',tag_3);

      ELSE
        BEGIN
        END;
      END CASE ;

      #select the id to associate, and its type
      IF force_type_to_use IS NULL THEN
        SELECT  FLOOR(RAND()*(4)) + 1 INTO number_choice_for_type;
      ELSE
        SET number_choice_for_type := force_type_to_use;
      END IF;


      CASE number_choice_for_type
        WHEN 1 THEN
        BEGIN
          -- project
          SET type_to_use := 1;

          # get random project from user in batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.post_id,p.user_id INTO unsigned_id_to_link,da_user_id FROM temp_projects_in_batch p
          WHERE p.id = random_thing;

          SET id_to_link := CAST(unsigned_id_to_link as SIGNED);

        END;

        WHEN 2 THEN
        BEGIN
          -- content
          SET type_to_use := 2;
          # get random content from user in batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.content_id,p.user_id INTO id_to_link,da_user_id FROM temp_contents_in_batch p
          WHERE p.id = random_thing;

        END;

        WHEN 3 THEN
        BEGIN
          -- contest
          SET type_to_use := 3;
          # get random contest from user in batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.post_id,p.user_id INTO unsigned_id_to_link,da_user_id FROM temp_contests_in_batch p
          WHERE p.id = random_thing;

          SET id_to_link := CAST(unsigned_id_to_link as SIGNED);

        END;

        WHEN 4 THEN
        BEGIN
          -- user
          SET type_to_use := 4;
          # get random user from batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
          WHERE p.id = random_thing;

          SET id_to_link := CAST(da_user_id as SIGNED);

        END;

      END CASE ;

      IF tag_1 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_1,id_to_link,type_to_use);
      END IF;

      IF tag_2 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_2,id_to_link,type_to_use);
      END IF;

      IF tag_3 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_3,id_to_link,type_to_use);
      END IF;


    END WHILE ;

    COMMIT;



  END$$
DELIMITER ;



-- code-notes proposals

/* Create random proposals, inserts into wp_proposals */

DELIMITER $$
CREATE  PROCEDURE `create_random_proposals`(IN number_to_make bigint unsigned)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;
    DECLARE contest_post_id bigint unsigned;
    DECLARE random_thing int;
    DECLARE loop_count int;

    #     DECLARE id_to_link bigint;
    #     DECLARE unsigned_id_to_link bigint unsigned;





    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_project_tags needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    START TRANSACTION;

    SET loop_count := 0;

    project_tag_loop: WHILE (loop_count < number_to_make) DO

      #get a random contest from batch

      SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;

      SELECT p.post_id,p.user_id INTO contest_post_id,da_user_id FROM temp_contests_in_batch p
      WHERE p.id = random_thing;

      SET loop_count := loop_count  + 1;


      INSERT INTO wp_proposals (id, post_id, job_id, by_user, status, type, rating, mediator_id,
                                rejection_accepted, created_at, updated_at, revision_text, rejection_txt,
                                rejected_at, rejection_requested, number, rating_by_customer, rating_by_freelancer,
                                comments_by_customer, comments_by_freelancer, customer)
      VALUES (NULL, contest_post_id, 0, da_user_id, 'pending', 2, null, 0, 0, NOW(),NOW(),
        '', '', null, '0', 1, null, null, null, null, null);

      INSERT INTO wp_fl_post_user_lookup(post_id,author_id,lookup_flag,lookup_val)
      VALUES (contest_post_id,da_user_id,2,0) ON DUPLICATE KEY UPDATE lookup_val = lookup_val+1;

    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;



-- code-notes bids and hires

/* Create random bids: adds rows to wp_comments, wp_commentmeta (bid_price)

    # comments add row
      INSERT INTO wp_comments (comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id)
        VALUES (NULL, random_project_id, da_user_login, da_user_email, '', '127.0.0.1', NOW(), NOW(), 'test bid', 0, '1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0', 'job_bid', 0, da_user_id);

     # commentmeta add two rows for each comment
     # lowest_project_price => post meta estimated_budgets of the project, string of characters before _
      INSERT INTO wp_commentmeta (meta_id, comment_id, meta_key, meta_value) VALUES
        (NULL, new_comment_id, 'bid_price', lowest_project_price);


      # and adds post meta (_bid_placed_by_{user_id} value user_id
       INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES
       (NULL, random_project_id, CONCAT('_bid_placed_by_',da_user_id), da_user_id);

    # Of the bids, it will randomly hire a percentage of them
    # if random number between 1 and 100 <= percentage_hired
     # the add in the entry to wp_fl_jobs

     job_sequence_number => number of wp_fl_jobs with this bid_id = new_comment_id, + 1
     project_name => post meta of modified_id for  random_project_id
     hiring_user_id => randomly picked from batch, not equal to da_user_id

     INSERT INTO wp_fl_job (ID, job_seq, title, content, author, linguist_id, project_id, bid_id,
      amount, meta, post_date, job_status, rating_by_customer, comments_by_customer, comments_by_freelancer,
       rating_by_freelancer, updated_at)

     VALUES (NULL, job_sequence_number, project_name, 'Test hire note', hiring_user_id, da_user_id, random_project_id, new_comment_id,
            0, '', NOW(), 'pending', null, null, null, null, NOW());


 After this , next procedure will hire and pay milestones, randomly
 */
DELIMITER $$
CREATE  PROCEDURE `create_random_bids_and_hires`(IN number_to_make bigint unsigned,
                                                 IN percentage_hired int)
  proc_random_bids: BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE count_users bigint unsigned;
    DECLARE da_hiring_user_id bigint unsigned;
    DECLARE da_bidding_user_id bigint unsigned;
    DECLARE random_project_id bigint unsigned;
    DECLARE da_bidding_user_login VARCHAR(60);
    DECLARE da_bidding_user_email VARCHAR(100);
    DECLARE new_comment_id bigint unsigned;
    DECLARE lowest_project_price VARCHAR(100);
    DECLARE rand_1_and_100 int;
    DECLARE job_sequence_number int;
    DECLARE project_name VARCHAR(100);

    DECLARE existing_php_serialized longtext;
    DECLARE php_serialized_array longtext;
    DECLARE new_array_index int;
    DECLARE new_job_id bigint unsigned;

    DECLARE random_thing int;
    DECLARE loop_count int;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_bids_and_hires=> number_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    IF  percentage_hired <= 0 OR percentage_hired > 100 THEN
      SET msg := CONCAT('create_random_bids_and_hires=> percentage_hired needs to be between 1 and 100 ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    SELECT count(u.id) INTO count_users FROM temp_users_in_batch u WHERE 1;

    IF  count_users < 2 THEN
      SET msg := CONCAT('Need at least two different users in this batch. One to make the bid and one to hire');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    SET loop_count := 0;

    START TRANSACTION;

    bid_loop: WHILE (loop_count < number_to_make ) DO

      SET loop_count := loop_count + 1;
      # get random project from user in batch
      SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;

      SELECT p.post_id,p.user_id INTO random_project_id, da_hiring_user_id FROM temp_projects_in_batch p
      WHERE p.id = random_thing;

      # get random bidder from users in batch , where the bidder is not the project owner
      SELECT p.user_id INTO da_bidding_user_id FROM temp_users_in_batch p
      WHERE p.user_id <> da_hiring_user_id
      ORDER BY RAND()
      LIMIT 1;

      # get the bidder details
      SELECT u.user_login,u.user_email
      INTO da_bidding_user_login,da_bidding_user_email
      FROM wp_users u
      WHERE u.ID = da_bidding_user_id;


      INSERT INTO wp_comments (comment_ID, comment_post_ID, comment_author, comment_author_email,
                               comment_author_url, comment_author_IP, comment_date, comment_date_gmt,
                               comment_content, comment_karma, comment_approved, comment_agent,
                               comment_type, comment_parent, user_id)
      VALUES (NULL, random_project_id, da_bidding_user_login, da_bidding_user_email, '', '127.0.0.1', NOW(), NOW(), 'test bid',
                    0, '1',
              'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0',
              'job_bid', 0, da_bidding_user_id);

      SET new_comment_id := last_insert_id();

      INSERT INTO wp_fl_post_user_lookup(post_id,author_id,lookup_flag,lookup_val)
      VALUES (random_project_id,da_bidding_user_id,1,0) ON DUPLICATE KEY UPDATE lookup_val = lookup_val+1;

      # commentmeta add two rows for each comment
      # lowest_project_price => post meta estimated_budgets of the project, string of characters before _
      SELECT CAST(LEFT(meta_value,INSTR(meta_value,'_')-1) as signed)   INTO lowest_project_price
      FROM wp_postmeta where post_id = random_project_id AND meta_key = 'estimated_budgets' ;

      INSERT INTO wp_commentmeta (meta_id, comment_id, meta_key, meta_value) VALUES
        (NULL, new_comment_id, 'bid_price', lowest_project_price);



      #randomly decide to hire
      SELECT  FLOOR(RAND()*(100)) + 1 INTO rand_1_and_100;
      IF rand_1_and_100 > percentage_hired THEN
        ITERATE bid_loop;
      END IF ;

      #     job_sequence_number => number of wp_fl_jobs with this project_id = random project id, + 1
      #     project_name => post meta of modified_id for  random_project_id
      #     hiring_user_id => randomly picked from batch, not equal to da_user_id

      SELECT (count(*) + 1) INTO job_sequence_number FROM wp_fl_job WHERE project_id = random_project_id;
      SELECT CONCAT(meta_value,'_',job_sequence_number) INTO project_name FROM wp_postmeta
      WHERE  post_id = random_project_id AND meta_key =  'modified_id' ;





      INSERT INTO wp_fl_job (ID, job_seq, title, content, author, linguist_id, project_id, bid_id,
                             amount, meta, post_date, job_status, rating_by_customer, comments_by_customer, comments_by_freelancer,
                             rating_by_freelancer, updated_at)

      VALUES (NULL, job_sequence_number, project_name, 'Test hire note', da_hiring_user_id, da_bidding_user_id,
                    random_project_id, new_comment_id,
                    0, '', NOW(), 'pending', null, null, null, null, NOW());

      SET new_job_id := last_insert_id();


    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;


-- code-notes milestones and payments

/* Create random milestones and payments: adds rows to wp_payment_history, wp_fl_transaction ,wp_fl_milestones

    select random job into random_job_id  from batch of users, that has a bid
    set da_user_id as the user which made the random job
    select random_project_id as the job's project
    select random_bid_id as the job's first bid (if there is more than one)

    see if wallet of project owner has amount, if not, then fill wallet with payment history and set user meta of total_user_balance
    SELECT CAST(meta_value as DECIMAL) INTO da_user_balance FROM wp_usermeta WHERE user_id = da_user_id AND meta_key = 'total_user_balance'

    IF da_user_balance IS NOT NULL
    INSERT INTO wp_payment_history (id, txn_id, payment_amount, payment_status, description, payment_type,
                        item_name, user_id, item_id, zip_code, refill_by, order_type, created_time)
     VALUES (NULL, CONCAT('test-tx-',UUID()), 999999, 'Completed', null, 'Test', 'Refill', da_user_id, null, null, 0, 0, NOW());

    INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, da_user_id, 'total_user_balance', '99999.00');
    SET da_user_balance = 99999.00;


    END IF;


    # add the milestone

    SELECT (MAX(number) +1) INTO milestone_number from wp_fl_milestones where project_id=random_project_id AND linguist_id =  freelancer_user_id;

    INSERT INTO wp_fl_milestones (ID, job_id, project_id, bid_id, content, amount, author, linguist_id, delivery_date,
                                  post_date, post_modified, status, dispute, created_at, updated_at, revision_text,
                                  rejection_txt, rejected_at, rejection_requested, completion_requested,
                                  completed_at, number)
    VALUES (NULL, random_job, random_project_id, random_bid_id, CONCAT('Test milestone ', uuid()), random_bid_amount,
                  da_user_id, freelancer_user_id, NOW(), NOW(), null,
      'completed', 0, NOW(), NOW(), '', '', null, '0', '1', NOW(), milestone_number);

    new_milestone_id is new insert id

    #make message saying milestone created, and remove the cost of it (ignoring fees) from the user wallet

    INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                   user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
    VALUES (NULL, CONCAT('test-tx-',UUID()), -random_bid_amount, 'done', 'Milestone created', 'milestone_created_by_customer',
                 '', '', da_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

    #update balance
    UPDATE wp_usermeta set meta_value = (da_user_balance -  random_bid_amount) WHERE meta_key = 'total_user_balance'
          AND user_id = da_user_id;
    #add message

    INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                    customer, freelancer, added_by)
    VALUES (NULL, CONCAT('Milestone created by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

    # complete milestone (with message history) and give amount to freelancer
    INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                    customer, freelancer, added_by)
    VALUES (NULL, CONCAT('Milestone completed by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

    SELECT CAST(meta_value as DECIMAL) INTO freelancer_balance FROM wp_usermeta WHERE meta_key = 'total_user_balance'
          AND user_id = freelancer_user_id;

    SET  freelancer_balance:= freelancer_balance +   random_bid_amount;

     UPDATE wp_usermeta set meta_value = freelancer_balance WHERE meta_key = 'total_user_balance'
          AND user_id = freelancer_user_id;


 */

DELIMITER $$
CREATE  PROCEDURE `create_random_milestones_and_payments`(IN number_to_make bigint unsigned)
  proc_milestones_payments: BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;
    DECLARE freelancer_user_id bigint unsigned;
    DECLARE random_project_id bigint unsigned;
    DECLARE random_job_id bigint unsigned;
    DECLARE random_bid_id BIGINT unsigned;
    DECLARE random_bid_amount DOUBLE;
    DECLARE da_user_balance DOUBLE;
    DECLARE freelancer_balance DOUBLE;
    DECLARE milestone_number INT;
    DECLARE new_milestone_id BIGINT;
    DECLARE da_user_display_name VARCHAR(250);

    DECLARE count_job int;

    DECLARE loop_count int;


    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_bids_and_hires=> number_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;


    START TRANSACTION;

    SET loop_count := 0;


    milestone_loop: WHILE (loop_count < number_to_make ) DO

      #     select random job into random_job_id  from batch of users, that has a bid
      #     set da_user_id as the user which made the random job
      #     select random_project_id as the job's project
      #     select random_bid_id as the job's first bid (if there is more than one)

      #get a random job from batch that does not already have a milestone

      SET loop_count := loop_count + 1;
      SET random_job_id := NULL;


      SELECT count(*),j.ID,j.bid_id,j.linguist_id,j.author,display_name,j.project_id
      INTO count_job,random_job_id,random_bid_id,freelancer_user_id,da_user_id,da_user_display_name , random_project_id
      FROM wp_fl_job j
        INNER JOIN temp_users_in_batch u ON u.user_id = j.author
        INNER JOIN wp_users ON wp_users.ID = u.user_id
        LEFT JOIN wp_fl_milestones stone ON stone.job_id = j.id
      WHERE stone.ID IS NULL
      ORDER BY RAND()
      LIMIT 1;

      # it could be there are no more jobs to give milestones to, that have not already received one
      IF random_job_id IS NULL THEN
        COMMIT ;
        LEAVE proc_milestones_payments;
      END IF;



      # get the bid amount
      SELECT m.meta_value INTO random_bid_amount FROM wp_commentmeta m
      WHERE  m.comment_id =  random_bid_id AND m.meta_key = 'bid_price';



      #   see if wallet of project owner has amount, if not, then fill wallet with payment history and set user meta of total_user_balance
      SELECT CAST(meta_value as DECIMAL) INTO da_user_balance FROM wp_usermeta WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

      IF da_user_balance < random_bid_amount THEN


        INSERT INTO wp_payment_history (id, txn_id, payment_amount, payment_status, description, payment_type,
                                        item_name, user_id,  refill_by,  created_time)
        VALUES (NULL, CONCAT('test-tx-',UUID()), 999999, 'Completed', null, 'Test', 'Refill', da_user_id, null, NOW());

        SET da_user_balance = 99999.00;

      END IF;


      # add the milestone

      SELECT (MAX(number) +1) INTO milestone_number from wp_fl_milestones where project_id=random_project_id AND linguist_id =  freelancer_user_id;
      IF milestone_number IS NULL THEN
        SET milestone_number := 1;
      END IF;

      INSERT INTO wp_fl_milestones (ID, job_id, project_id, bid_id, content, amount, author, linguist_id, delivery_date,
                                    post_date, post_modified, status, dispute, created_at, updated_at, revision_text,
                                    rejection_txt, rejected_at, rejection_requested, completion_requested,
                                    completed_at, number)
      VALUES (NULL, random_job_id, random_project_id, random_bid_id, CONCAT('Test milestone ', uuid()), random_bid_amount,
                    da_user_id, freelancer_user_id, NOW(), NOW(), NULL,
        'completed', 0, NOW(), NOW(), '', '', NULL, '0', '1', NOW(), milestone_number);

      SET new_milestone_id := LAST_INSERT_ID();

      #make message saying milestone created, and remove the cost of it (ignoring fees) from the user wallet

      INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                     user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
      VALUES (NULL, CONCAT('test-tx-',UUID()), -random_bid_amount, 'done', 'Milestone created', 'milestone_created_by_customer',
                    'wallet', '', da_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

      #update balance
      UPDATE wp_usermeta set meta_value = (da_user_balance -  random_bid_amount) WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

      #add message

      INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                      customer, freelancer, added_by)
      VALUES (NULL, CONCAT('Milestone created by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

      # complete milestone (with message history) and give amount to freelancer
      INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                      customer, freelancer, added_by)
      VALUES (NULL, CONCAT('Milestone completed by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

      SELECT CAST(meta_value as DECIMAL) INTO freelancer_balance FROM wp_usermeta WHERE meta_key = 'total_user_balance'
                                                                                        AND user_id = freelancer_user_id;

      SET  freelancer_balance:= freelancer_balance +   random_bid_amount;

      UPDATE wp_usermeta set meta_value = freelancer_balance WHERE user_id = freelancer_user_id AND meta_key = 'total_user_balance';


      INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                     user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
      VALUES (NULL, CONCAT('test-tx-',UUID()), random_bid_amount, 'done', 'Milestone Completed', 'milestone_completed',
                    'wallet', '', freelancer_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;


-- code-notes add random data

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


-- code-notes loop for random data

# Makes a thousand entries each loop

DELIMITER $$
CREATE  PROCEDURE `create_thousand_entries_each_loop`(IN number_of_loops_to_do int unsigned)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE  number_of_loops int;

    IF number_of_loops_to_do IS NULL THEN

      SET msg := CONCAT('create_thousand_entries_each_loop=> number_of_loops_to_do needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;

    END IF;

    SET number_of_loops := 0;

    main_loop: WHILE (number_of_loops < number_of_loops_to_do ) DO
      SET number_of_loops := number_of_loops + 1;
      CALL create_random_data(1000,null);
    END WHILE;


  END$$
DELIMITER ;


