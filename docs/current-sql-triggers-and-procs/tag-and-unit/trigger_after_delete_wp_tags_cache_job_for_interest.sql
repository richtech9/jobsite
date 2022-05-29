CREATE TRIGGER trigger_after_delete_wp_tags_cache_job_for_interest
  AFTER DELETE
  ON wp_tags_cache_job
  FOR EACH ROW
  BEGIN

    UPDATE wp_interest_tags itag
    SET itag.usage_count = IF(itag.usage_count > 0,itag.usage_count -1,0)
    WHERE itag.ID = OLD.tag_id;


  END
-- code-notes version 0.1