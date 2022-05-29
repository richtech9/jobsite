CREATE TRIGGER trigger_before_insert_user_data_lookup
  BEFORE INSERT
  ON wp_fl_user_data_lookup
  FOR EACH ROW
  BEGIN

    IF NEW.last_login_time IS NOT NULL AND NEW.score = 0 THEN
       SET NEW.score :=  (NEW.rating_as_freelancer + 1) * UNIX_TIMESTAMP(NEW.last_login_time);
    END IF;

  END
-- code-notes version 0.1