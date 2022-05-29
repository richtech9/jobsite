CREATE TRIGGER trigger_after_insert_fl_post_user_lookup_counter
  AFTER INSERT 
  ON wp_fl_post_user_lookup
  FOR EACH ROW
  BEGIN

    IF (NEW.lookup_flag = 2) AND NEW.author_id IS NOT NULL THEN
      UPDATE wp_fl_user_data_lookup look SET look.contests_entered = look.contests_entered + 1
      WHERE look.user_id = NEW.author_id;
    END IF ;

  END

