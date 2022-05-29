CREATE TRIGGER trigger_after_insert_linguist_content
  AFTER INSERT
  ON wp_linguist_content
  FOR EACH ROW
  BEGIN

    IF NEW.score > 0 THEN
      CALL manage_top_list(NEW.id,'content',NEW.score);
    END IF;

  END
-- code-notes version 0.2