CREATE TRIGGER trigger_after_update_post_data_lookup_counter
  AFTER UPDATE
  ON wp_fl_post_data_lookup
  FOR EACH ROW
  BEGIN

    IF (OLD.fl_job_type = 0) AND (NEW.fl_job_type = 1) AND NEW.author_id IS NOT NULL THEN
      UPDATE wp_fl_user_data_lookup look SET look.projects_created = look.projects_created + 1
      WHERE look.user_id = NEW.author_id;
    END IF ;

    IF (OLD.fl_job_type = 0) AND (NEW.fl_job_type = 2) AND NEW.author_id IS NOT NULL THEN
      UPDATE wp_fl_user_data_lookup look SET look.contests_created = look.contests_created + 1
      WHERE look.user_id = NEW.author_id;
    END IF ;

  END

