CREATE TRIGGER trigger_after_update_linguist_content_counter
  AFTER UPDATE
  ON wp_linguist_content
  FOR EACH ROW
  BEGIN

    IF (NEW.purchased_by IS NOT NULL ) AND (OLD.status <> 'completed') AND  (NEW.status = 'completed') THEN

      UPDATE wp_fl_user_data_lookup look SET look.content_purchased = look.content_purchased + 1
      WHERE look.user_id = NEW.purchased_by;

      UPDATE wp_fl_user_data_lookup look SET look.content_sold = look.content_sold + 1
      WHERE look.user_id = NEW.user_id;

    END IF ;
    -- version 3
  END

