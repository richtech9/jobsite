CREATE TRIGGER trigger_after_delete_wp_tags_cache_job
  AFTER DELETE
  ON wp_tags_cache_job
  FOR EACH ROW
  BEGIN

    # if type is 4 then delete from wp_display_unit_user_content where the job is the user_id and tag is tag_id
    # if type is 2 then delete from wp_display_unit_user_content where the job is the content_id and tag is tag_id

    IF OLD.type = 4 THEN
      DELETE FROM wp_display_unit_user_content WHERE user_id = OLD.job_id AND tag_id = OLD.tag_id;
    END IF;

    IF OLD.type = 2 THEN
      DELETE FROM wp_display_unit_user_content WHERE content_id = OLD.job_id AND tag_id = OLD.tag_id;
    END IF;


  END
-- code-notes version 0.1