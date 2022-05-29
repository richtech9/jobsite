CREATE TRIGGER trigger_after_update_on_wp_fl_milestones
  AFTER UPDATE
  ON wp_fl_milestones
  FOR EACH ROW
  BEGIN

    DECLARE flag_id_complete_job INT;
    SET flag_id_complete_job := NULL;

    IF NEW.status = 'completed' THEN -- insert new row

      -- see if need to create the freelancer already has a completed milestone in this project
      SELECT k.id INTO flag_id_complete_job
      FROM wp_fl_post_user_lookup k
      WHERE k.author_id = NEW.linguist_id AND k.post_id = NEW.project_id AND k.lookup_flag = 4;

      IF flag_id_complete_job IS NULL AND NEW.linguist_id IS NOT NULL THEN
        -- update the count for the linguist and create the flag
        UPDATE wp_fl_user_data_lookup look SET look.jobs_worked_completed = look.jobs_worked_completed + 1
        WHERE look.user_id = NEW.linguist_id;

        INSERT INTO wp_fl_post_user_lookup (post_id, author_id, lookup_flag, lookup_val)
        VALUES (NEW.project_id,NEW.linguist_id,4,NEW.ID);

      END IF; -- end if we did not mark this project as completed for this user

    END IF; -- end if status is completed or approve

  END

