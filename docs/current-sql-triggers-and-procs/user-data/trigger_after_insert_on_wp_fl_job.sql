CREATE TRIGGER trigger_after_insert_on_wp_fl_job
  AFTER INSERT 
  ON wp_fl_job
  FOR EACH ROW
  BEGIN

    UPDATE wp_fl_user_data_lookup SET jobs_worked = jobs_worked + 1
    WHERE user_id = NEW.linguist_id;

    IF NEW.author IS NOT NULL THEN
      UPDATE wp_fl_user_data_lookup look SET look.projects_hiring = look.projects_hiring + 1
      WHERE look.user_id = NEW.author;
    END IF;

  END

