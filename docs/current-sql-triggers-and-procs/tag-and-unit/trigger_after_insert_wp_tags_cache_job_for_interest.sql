CREATE TRIGGER trigger_after_insert_wp_tags_cache_job_for_interest
  AFTER INSERT
  ON wp_tags_cache_job
  FOR EACH ROW
  BEGIN

    IF NEW.tag_id IS NOT NULL
    THEN
      UPDATE wp_interest_tags itag
      SET itag.usage_count = itag.usage_count + 1
      WHERE itag.ID = NEW.tag_id;
    END IF;

  END
-- code-notes version 0.1