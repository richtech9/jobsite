CREATE TRIGGER trigger_after_insert_linguist_content_counter
  AFTER INSERT 
  ON wp_linguist_content
  FOR EACH ROW
  BEGIN

    IF NEW.user_id IS NOT NULL AND NEW.parent_content_id IS  NULL THEN
      UPDATE wp_fl_user_data_lookup look SET look.content_created = look.content_created + 1
      WHERE look.user_id = NEW.user_id;
    END IF ;

  END

