CREATE TRIGGER trigger_after_insert_linguist_content_chapter
  AFTER INSERT
  ON wp_linguist_content_chapter
  FOR EACH ROW
  BEGIN

    # update parent, set score to 0,
    #  its trigger will recalculate the score and do all the other things

    UPDATE wp_linguist_content SET score = 0 WHERE id = NEW.linguist_content_id;

  END
-- code-notes version 0.1