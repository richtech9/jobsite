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