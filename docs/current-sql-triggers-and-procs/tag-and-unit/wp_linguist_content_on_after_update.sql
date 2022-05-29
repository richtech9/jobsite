CREATE TRIGGER trigger_after_update_linguist_content
  AFTER UPDATE
  ON wp_linguist_content
  FOR EACH ROW
  BEGIN

    IF NEW.score != OLD.score THEN
      CALL manage_top_list(NEW.id,'content',NEW.score);
    END IF;

  END
-- code-notes version 0.2