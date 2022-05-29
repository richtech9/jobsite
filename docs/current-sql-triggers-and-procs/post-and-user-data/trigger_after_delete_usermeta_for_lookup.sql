CREATE TRIGGER trigger_after_delete_usermeta_for_lookup
  BEFORE DELETE
  ON wp_usermeta 
  FOR EACH ROW
  BEGIN
    -- history
    -- version 1.1   Added total_user_balance
    -- code-notes 2021-March-25 added create_batch
    -- for each of the meta keys, update the correct number for the value, the post id may already exist thanks to the post triggers

    DECLARE da_user_id_itself BIGINT UNSIGNED;
    DECLARE user_data_lookup_id INT; -- for wp_user_data_lookup

    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO user_data_lookup_id FROM wp_fl_user_data_lookup k WHERE k.user_id = OLD.user_id;

    SELECT u.ID INTO da_user_id_itself
    FROM wp_users u
    WHERE u.ID = OLD.user_id;

    IF da_user_id_itself IS NOT NULL AND user_data_lookup_id IS NULL THEN -- insert new row

      INSERT INTO wp_fl_user_data_lookup(user_id)
      VALUES (OLD.user_id);

      SET user_data_lookup_id := last_insert_id();

    END IF;


    #     average_rating_customer_role          TINYINT
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'average_rating_customer_role' THEN
      UPDATE wp_fl_user_data_lookup k SET k.rating_as_customer = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #     average_rating_freelancer_role     TINYINT
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'average_rating_freelancer_role' THEN
      UPDATE wp_fl_user_data_lookup k SET k.rating_as_freelancer = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #     wp_capabilities    unsigned int
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'wp_capabilities' THEN
      UPDATE wp_fl_user_data_lookup k SET k.wp_capabilities = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #     user_image                 TINYINT
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'user_image' THEN
      UPDATE wp_fl_user_data_lookup k SET k.has_user_image = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #     create_batch --> is_test_data    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1, if exists and not empty then 1 else then 0
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'create_batch' THEN
      UPDATE wp_fl_user_data_lookup k SET k.is_test_data = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #user_hourly_rate   int unsigned
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'user_hourly_rate' THEN
      UPDATE wp_fl_user_data_lookup k SET k.user_hourly_rate = 0 WHERE  k.id = user_data_lookup_id;
    END IF;

    #total_user_balance   float
    IF user_data_lookup_id IS NOT NULL AND OLD.meta_key = 'total_user_balance' THEN
      UPDATE wp_fl_user_data_lookup k SET k.total_user_balance = 0 WHERE  k.id = user_data_lookup_id;
    END IF;


  END

