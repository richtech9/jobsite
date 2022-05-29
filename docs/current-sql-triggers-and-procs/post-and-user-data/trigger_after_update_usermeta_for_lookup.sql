CREATE TRIGGER trigger_after_update_usermeta_for_lookup
  AFTER UPDATE
  ON wp_usermeta
  FOR EACH ROW
  BEGIN
    -- code-notes 2021-March-25 added create_batch
    -- version 1.1   Added total_user_balance

    -- for each of the meta keys, update the correct number for the value, the post id row may already exist thanks to the post triggers
    DECLARE val_rating_as_customer TINYINT;
    DECLARE val_rating_as_freelancer TINYINT;
    DECLARE val_wp_capabilities TINYINT;
    DECLARE val_has_user_image TINYINT;
    DECLARE val_is_test_data TINYINT;
    DECLARE da_user_id_itself BIGINT UNSIGNED;
    DECLARE user_data_lookup_id INT; -- for wp_user_data_lookup
    DECLARE number_test INT;
    DECLARE val_user_hourly_rate INT;
    DECLARE val_total_user_balance FLOAT;

    SET val_rating_as_customer := 0;
    SET val_rating_as_freelancer := 0;
    SET val_wp_capabilities := 0;
    SET val_has_user_image := 0;
    SET val_user_hourly_rate := 0;
    SET val_total_user_balance := 0;
    SET val_is_test_data := 0;

    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO user_data_lookup_id FROM wp_fl_user_data_lookup k WHERE k.user_id = NEW.user_id;

    SELECT u.ID INTO da_user_id_itself
    FROM wp_users u
    WHERE u.ID = NEW.user_id;

    IF da_user_id_itself IS NOT NULL AND user_data_lookup_id IS NULL THEN -- insert new row

      INSERT INTO wp_fl_user_data_lookup(user_id)
      VALUES (NEW.user_id);

      SET user_data_lookup_id := last_insert_id();

    END IF;


    #     average_rating_customer_role   TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'average_rating_customer_role' THEN
      IF NEW.meta_value = '' THEN SET val_rating_as_customer := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_rating_as_customer := 0;
      ELSE
        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[.0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_rating_as_customer := CAST(ROUND(NEW.meta_value) as SIGNED);
        ELSE
          SET val_rating_as_customer := -100;
          INSERT INTO wp_fl_user_lookup_errors(user_lookup_id, column_of_error,error_msg)
          VALUES (user_data_lookup_id,'rating_as_customer',CONCAT('value of rating_as_customer is not numeric: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.rating_as_customer = val_rating_as_customer WHERE k.id = user_data_lookup_id;
    END IF;


    #     average_rating_freelancer_role   TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'average_rating_freelancer_role' THEN
      IF NEW.meta_value = '' THEN SET val_rating_as_freelancer := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_rating_as_freelancer := 0;
      ELSE
        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[.0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_rating_as_freelancer := CAST(ROUND(NEW.meta_value)as SIGNED);
        ELSE
          SET val_rating_as_freelancer := -100;
          INSERT INTO wp_fl_user_lookup_errors(user_lookup_id, column_of_error,error_msg)
          VALUES (user_data_lookup_id,'rating_as_freelancer',CONCAT('value of val_rating_as_freelancer is not numeric: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.rating_as_freelancer = val_rating_as_freelancer WHERE k.id = user_data_lookup_id;
    END IF;

    #     wp_capabilities    INT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'wp_capabilities' THEN
      IF NEW.meta_value like '%customer%' AND NEW.meta_value like '%translator%' THEN SET val_wp_capabilities := 3;
      ELSEIF NEW.meta_value like '%customer%' THEN SET val_wp_capabilities := 1;
      ELSEIF NEW.meta_value like '%translator%' THEN SET val_wp_capabilities := 2;
      ELSE
        SET val_wp_capabilities := 0; # no error if not match
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.wp_capabilities = val_wp_capabilities WHERE k.id = user_data_lookup_id;
    END IF;

    #     user_image                 TINYINT CURRENTLY USED CAN BE INT FLAG, 0 for unset 1 for set
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'user_image' THEN
      IF NEW.meta_value = '' THEN SET val_has_user_image := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_has_user_image := 0;
      ELSE
        SET val_has_user_image := 1; # no error, either set or not set based on empty or not empty value
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.has_user_image = val_has_user_image WHERE k.id = user_data_lookup_id;
    END IF;

    #     create_batch --> is_test_data    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1, if exists and not empty then 1 else then 0
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'create_batch' THEN
      IF NEW.meta_value = '' THEN SET val_is_test_data := 0;
      ELSEIF NEW.meta_value <> '' THEN SET val_is_test_data := 1;
      ELSEIF NEW.meta_value IS NULL THEN SET val_is_test_data := 0;
      ELSE
        SET val_is_test_data := -100;
        INSERT INTO wp_fl_user_lookup_errors(user_lookup_id, column_of_error,error_msg)
        VALUES (user_data_lookup_id,'is_test_data',CONCAT('value of create_batch is not known: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.is_test_data = val_is_test_data WHERE k.id = user_data_lookup_id;
    END IF;

    #user_hourly_rate             int , cast to signed int from text
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'user_hourly_rate' THEN
      IF NEW.meta_value = '' THEN SET val_user_hourly_rate := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_user_hourly_rate := 0;
      ELSE
        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[.0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_user_hourly_rate := CAST(ROUND(NEW.meta_value) as SIGNED);
        ELSE
          SET val_user_hourly_rate := -100;
          INSERT INTO wp_fl_user_lookup_errors(user_lookup_id, column_of_error,error_msg)
          VALUES (user_data_lookup_id,'user_hourly_rate',CONCAT('value of user_hourly_rate is not numeric: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.user_hourly_rate = val_user_hourly_rate WHERE k.id = user_data_lookup_id;
    END IF;



    #total_user_balance             float , cast to float from text
    IF user_data_lookup_id IS NOT NULL AND NEW.meta_key = 'total_user_balance' THEN
      IF NEW.meta_value = '' THEN SET val_total_user_balance := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_total_user_balance := 0;
      ELSE
        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[-.0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_total_user_balance := CAST(NEW.meta_value as decimal(12,2));
        ELSE
          SET val_total_user_balance := -100;
          INSERT INTO wp_fl_user_lookup_errors(user_lookup_id, column_of_error,error_msg)
          VALUES (user_data_lookup_id,'total_user_balance',CONCAT('value of total_user_balance is not numeric: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_fl_user_data_lookup k SET k.total_user_balance = val_total_user_balance WHERE k.id = user_data_lookup_id;
    END IF;

  END

