CREATE TRIGGER trigger_after_update_wp_tags_cache_job_for_interest
  AFTER UPDATE
  ON wp_tags_cache_job
  FOR EACH ROW
  BEGIN

    IF NEW.tag_id <> OLD.tag_id OR
       (NEW.tag_id IS NULL AND OLD.tag_id IS NOT NULL)
    THEN
      UPDATE wp_interest_tags itag
      SET itag.usage_count = IF(itag.usage_count > 0,itag.usage_count -1,0)
      WHERE itag.ID = OLD.tag_id;
    END IF;

    IF NEW.tag_id <> OLD.tag_id OR
       (OLD.tag_id IS NULL AND NEW.tag_id IS NOT NULL)
    THEN
      UPDATE wp_interest_tags itag
      SET itag.usage_count = itag.usage_count + 1
      WHERE itag.ID = NEW.tag_id;
    END IF;

  END
-- code-notes version 0.1