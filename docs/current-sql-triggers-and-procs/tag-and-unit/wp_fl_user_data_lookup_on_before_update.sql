CREATE TRIGGER trigger_before_update_user_data_lookup
  BEFORE UPDATE
  ON wp_fl_user_data_lookup
  FOR EACH ROW
  BEGIN

    IF
      NEW.last_login_time IS NOT NULL AND
      (
        NEW.last_login_time > OLD.last_login_time OR
        OLD.last_login_time IS NULL OR
        NEW.score = 0 OR -- allow resetting of score
        NEW.rating_as_freelancer <> OLD.rating_as_freelancer
      )
    THEN
       SET NEW.score :=  (NEW.rating_as_freelancer + 1) * UNIX_TIMESTAMP(NEW.last_login_time);
    END IF;

  END
-- code-notes version 0.3