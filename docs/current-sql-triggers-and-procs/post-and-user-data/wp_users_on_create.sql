CREATE TRIGGER trigger_after_insert_users_for_lookup
  AFTER INSERT
  ON wp_users
  FOR EACH ROW
  BEGIN
    INSERT INTO wp_fl_user_data_lookup(user_id)
    VALUES (NEW.ID);
  END

