DELIMITER $$

/*
* until_max_id MUST be the highest valid user id to convert

 */
CREATE  PROCEDURE `update_test_user_meta`(IN until_max_id bigint unsigned)
  updating_the_umeta: BEGIN
    DECLARE msg VARCHAR(255);

    DECLARE da_test_user_id bigint unsigned;
    DECLARE test_meta_id bigint unsigned;
    DECLARE rand_1_and_100_select_role int;
    DECLARE rand_1_and_100_image int;
    DECLARE rand_1_and_5 int;
    DECLARE finished INTEGER DEFAULT 0;
    DECLARE show_output int;

    DEClARE testUsers
    CURSOR FOR
      SELECT DISTINCT u.ID as test_b_id FROM wp_users u
        INNER JOIN wp_usermeta m ON m.meta_key = 'create_batch' AND m.user_id = u.ID
      WHERE u.ID <= until_max_id ORDER BY u.ID ASC ;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      IF show_output = 1 THEN SELECT CONCAT('Rolling back '); END IF;
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    IF  until_max_id < 0 THEN
      SET msg := CONCAT('until_max_id needs to be a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;


    OPEN testUsers;



    START TRANSACTION;

    SET finished := 0;  -- not used anymore
    SET show_output := 0;  -- set to one to have a lot of select statements run to show debugging

    IF show_output = 1 THEN SELECT CONCAT('Cursor found  ',FOUND_ROWS(),' rows'); END IF;

    getUser: LOOP
      FETCH testUsers INTO da_test_user_id;

      IF show_output = 1 THEN SELECT CONCAT('Finished is of value  ',finished); END IF;
      IF show_output = 1 THEN SELECT CONCAT('Starting loop with user id of ',da_test_user_id); END IF;

      -- decide if this is a freelancer or customer
      SELECT  FLOOR(RAND()*(100)) + 1 INTO rand_1_and_100_select_role;

      IF show_output = 1 THEN SELECT CONCAT('Random number is ', rand_1_and_100_select_role); END IF;

      IF rand_1_and_100_select_role <= 50 THEN
        -- freelancer role with rating

        IF show_output = 1 THEN SELECT CONCAT('Freelancer Role (bottom half) '); END IF;

        -- check if average_rating_freelancer_role meta exists
        SET test_meta_id := NULL;
        SELECT umeta_id INTO test_meta_id FROM wp_usermeta WHERE user_id = da_test_user_id AND meta_key = 'average_rating_freelancer_role';
        IF show_output = 1 THEN SELECT CONCAT('test meta id for average_rating_freelancer_role ', IF(test_meta_id IS NULL,'NULL',test_meta_id)); END IF;
        IF test_meta_id IS NULL THEN
          -- create random rating between 1 and 5
          SELECT  FLOOR(RAND()*(5)) + 1 INTO rand_1_and_5;
          INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES (da_test_user_id,'average_rating_freelancer_role',rand_1_and_5);
          IF show_output = 1 THEN SELECT CONCAT('added a rating of  ',rand_1_and_5, ' AND THE insert id is ', LAST_INSERT_ID()); END IF;
        END IF;

        -- get the wp_capabilities key, it should exist, if not then insert
        SET test_meta_id := NULL;
        SELECT umeta_id INTO test_meta_id FROM wp_usermeta WHERE user_id = da_test_user_id AND meta_key = 'wp_capabilities';
        IF show_output = 1 THEN SELECT CONCAT('test meta id for wp_capabilities ', IF(test_meta_id IS NULL,'NULL',test_meta_id)); END IF;

        IF test_meta_id IS NOT NULL THEN
          -- update role
          IF show_output = 1 THEN SELECT CONCAT('updating role to freelancer '); END IF;
          UPDATE wp_usermeta SET meta_value = 'a:1:{s:10:"translator";b:1;}' WHERE umeta_id = test_meta_id;
        ELSE
          -- insert role

          IF show_output = 1 THEN SELECT CONCAT('inserting wp_capabilities with translator value  ', '. The insert id is ', LAST_INSERT_ID()); END IF;
          INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value)
          VALUES (NULL, da_test_user_id, 'wp_capabilities', 'a:1:{s:10:"translator";b:1;}');
        END IF;


      ELSE
        -- customer role with rating

        IF show_output = 1 THEN SELECT CONCAT('Customer Role (top half) '); END IF;

        -- get the wp_capabilities key, it should exist, if not then insert
        SET test_meta_id := NULL;
        SELECT umeta_id INTO test_meta_id FROM wp_usermeta WHERE user_id = da_test_user_id AND meta_key = 'wp_capabilities';
        IF show_output = 1 THEN SELECT CONCAT('test meta id for wp_capabilities ', IF(test_meta_id IS NULL,'NULL',test_meta_id)); END IF;

        IF test_meta_id IS NOT NULL THEN
          -- update role
          IF show_output = 1 THEN SELECT CONCAT('updating role to customer '); END IF;
          UPDATE wp_usermeta SET meta_value = 'a:1:{s:8:"customer";b:1;}' WHERE umeta_id = test_meta_id;
        ELSE
          -- insert role
          INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value)
          VALUES (NULL, da_test_user_id, 'wp_capabilities', 'a:1:{s:8:"customer";b:1;}');
          IF show_output = 1 THEN SELECT CONCAT('inserting wp_capabilities with customer value  ', '. The insert id is ', LAST_INSERT_ID()); END IF;
        END IF;

        -- check if average_rating_customer_role meta exists
        SET test_meta_id := NULL;
        SELECT umeta_id INTO test_meta_id FROM wp_usermeta WHERE user_id = da_test_user_id AND meta_key = 'average_rating_customer_role';
        IF show_output = 1 THEN SELECT CONCAT('test meta id for average_rating_customer_role ', IF(test_meta_id IS NULL,'NULL',test_meta_id)); END IF;


        IF test_meta_id IS NULL THEN
          -- create random rating between 1 and 5
          SELECT  FLOOR(RAND()*(5)) + 1 INTO rand_1_and_5;
          INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES (da_test_user_id,'average_rating_customer_role',rand_1_and_5);

          IF show_output = 1 THEN SELECT CONCAT('added a rating of  ',rand_1_and_5, ' AND THE insert id is ', LAST_INSERT_ID()); END IF;
        END IF;


      END IF;

      -- for both, add a user image 60% of the time

      -- decide if this is a freelancer or customer
      SELECT  FLOOR(RAND()*(100)) + 1 INTO rand_1_and_100_image;
      IF show_output = 1 THEN SELECT CONCAT('Random Chance of image being set is  ',rand_1_and_100_image,'%'); END IF;

      IF rand_1_and_100_image <=60 THEN
        -- check if user image meta exists
        SET test_meta_id := NULL;
        SELECT umeta_id INTO test_meta_id FROM wp_usermeta WHERE user_id = da_test_user_id AND meta_key = 'user_image';

        IF show_output = 1 THEN SELECT CONCAT('test meta id for user_image ', IF(test_meta_id IS NULL,'NULL',test_meta_id)); END IF;


        IF test_meta_id IS NULL THEN
          INSERT INTO wp_usermeta(user_id, meta_key, meta_value) VALUES (da_test_user_id,'user_image','userprofile/test-user-image.png');
          IF show_output = 1 THEN SELECT CONCAT('inserting user_image   ', '. The insert id is ', LAST_INSERT_ID()); END IF;
        END IF;
      END IF;



      -- if this id >= the stop point, then exit
      IF da_test_user_id >= until_max_id THEN
        IF show_output = 1 THEN SELECT CONCAT('Leaving loop The current id of    ',da_test_user_id, ' IS >= than ',until_max_id ); END IF;
        LEAVE getUser;
      END IF;

      IF finished = 1 THEN
        IF show_output = 1 THEN SELECT CONCAT('Leaving loop because the cursor ended . Ending with ',da_test_user_id); END IF;
        LEAVE getUser;
      END IF;

    END LOOP getUser;
    CLOSE testUsers;

    IF show_output = 1 THEN SELECT CONCAT('End loop and committing '); END IF;
    COMMIT;

  END$$
DELIMITER ;