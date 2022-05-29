CREATE TRIGGER trigger_after_delete_linguist_content_chapter
  AFTER DELETE
  ON wp_linguist_content_chapter
  FOR EACH ROW
  BEGIN

    # update parent, set score to 0,
    #  its trigger will recalculate the score and do all the other things

    UPDATE wp_linguist_content SET score = 0 WHERE id = OLD.linguist_content_id;

  END
-- code-notes version 0.1