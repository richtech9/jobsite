CREATE TRIGGER trigger_after_delete_user_data_lookup
  AFTER DELETE
  ON wp_fl_user_data_lookup
  FOR EACH ROW
  BEGIN

    -- delete all display using this, as the other triggers refer to this table
    DELETE FROM wp_display_unit_user_content
    WHERE user_id = OLD.user_id ;

  END
-- code-notes version 0.1
