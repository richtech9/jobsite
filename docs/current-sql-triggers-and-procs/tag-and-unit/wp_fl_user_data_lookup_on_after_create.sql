CREATE TRIGGER trigger_after_insert_user_data_lookup
  AFTER INSERT
  ON wp_fl_user_data_lookup
  FOR EACH ROW
  BEGIN


    IF NEW.score > 0 THEN
      CALL manage_top_list(NEW.user_id,'user',NEW.score);
    END IF;

  END
-- code-notes version 0.1
