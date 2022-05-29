CREATE TRIGGER trigger_after_delete_proposals_for_chat_room
  AFTER DELETE
  ON wp_proposals
  FOR EACH ROW
  BEGIN

    -- code-notes version 0.1 March 17, 2021

   IF OLD.chat_room_id IS NOT NULL THEN
     UPDATE wp_fl_chat_rooms SET is_active = 0 WHERE id = OLD.chat_room_id;
   END IF;


  END

