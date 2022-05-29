CREATE TRIGGER trigger_after_update_on_wp_proposals
  AFTER UPDATE
  ON wp_proposals
  FOR EACH ROW
  BEGIN

    DECLARE author_id_from_post BIGINT UNSIGNED;

    -- multiple sql updates with completes status
    IF (OLD.status <> 'completed') AND (NEW.status = 'completed')  THEN -- update for customer and freelancer

      SET author_id_from_post := NULL;
      -- see if need to create the freelancer already has a completed milestone in this project
      SELECT k.post_author INTO author_id_from_post
      FROM wp_posts k
      WHERE k.ID = NEW.post_id;

      IF author_id_from_post IS NOT NULL THEN
        UPDATE wp_fl_user_data_lookup look SET look.contests_awarding = look.contests_awarding + 1
        WHERE look.user_id = author_id_from_post;
      END IF;

      IF NEW.by_user IS NOT NULL THEN
        UPDATE wp_fl_user_data_lookup look SET look.contests_won = look.contests_won + 1
        WHERE look.user_id = NEW.by_user;
      END IF;

    END IF; -- end if status is completed

  END

