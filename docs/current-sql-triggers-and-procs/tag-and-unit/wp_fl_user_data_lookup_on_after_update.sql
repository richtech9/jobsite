CREATE TRIGGER trigger_after_update_user_data_lookup
  AFTER UPDATE
  ON wp_fl_user_data_lookup
  FOR EACH ROW
  BEGIN

    IF NEW.score != OLD.score OR NEW.test_flag != OLD.test_flag THEN
      CALL manage_top_list(NEW.user_id,'user',NEW.score);
    END IF;

  END
-- code-notes version 0.2

#VERSION HISTORY
# version 0.2 add in comparision of test flag so the top tags can be activated without changing the login
