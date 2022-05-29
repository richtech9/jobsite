CREATE TRIGGER trigger_after_update_users_for_lookup
  BEFORE UPDATE
  ON wp_users
  FOR EACH ROW
  BEGIN

    DECLARE user_data_lookup_id INT; -- for wp_user_data_lookup

    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO user_data_lookup_id FROM wp_fl_user_data_lookup k WHERE k.user_id = OLD.ID;


    IF user_data_lookup_id IS NULL THEN -- insert new row

      INSERT INTO wp_fl_user_data_lookup(user_id)
      VALUES (OLD.ID);

      SET user_data_lookup_id := last_insert_id();

    END IF;

    UPDATE wp_fl_user_data_lookup k SET
      k.user_id = NEW.ID
    WHERE k.user_id = OLD.ID;

  END

