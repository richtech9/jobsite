CREATE TRIGGER trigger_after_insert_post_for_txn
  AFTER INSERT
  ON wp_posts
  FOR EACH ROW
  BEGIN
    # code-notes version 3 March-9-2021: Added Post Date
    # code-notes version 2 March-8-2021: Added Post Status
    # code-notes version 1 March-6-2021

    DECLARE val_post_status TINYINT;
    DECLARE post_author_id BIGINT UNSIGNED;
    DECLARE transaction_lookup_id INT; -- for wp_post_data_lookup
    DECLARE post_status_error TEXT;

    IF NEW.post_type = 'wallet' THEN

      #post author empty make null

      IF NEW.post_author = '' THEN SET post_author_id := NULL;
      ELSEIF NEW.post_author = 0 THEN SET post_author_id := NULL;
      ELSEIF NEW.post_author IS NULL THEN SET post_author_id := NULL;
      ELSE
        SET post_author_id := NEW.post_author;
      END IF;

      #     post_status                 TINYINT  none=0|pending=1|publish=2|private=3|new_transaction=4|pending_transaction=5|failed_transaction=6
      IF NEW.post_status = 'pending' THEN SET val_post_status := 1;
      ELSEIF NEW.post_status = 'publish' THEN SET val_post_status := 2;
      ELSEIF NEW.post_status = 'private' THEN SET val_post_status := 3;
      ELSEIF NEW.post_status = 'new_transaction' THEN SET val_post_status := 4;
      ELSEIF NEW.post_status = 'pending_transaction' THEN SET val_post_status := 5;
      ELSEIF NEW.post_status = 'failed_transaction' THEN SET val_post_status := 6;
      ELSEIF NEW.post_status = '' THEN SET val_post_status := 0;
      ELSEIF NEW.post_status IS NULL THEN SET val_post_status := 0;
      ELSE
        SET val_post_status := -100;
        SET post_status_error := CONCAT('did not find keyword in post status: ',NEW.post_status);
      END IF;

      INSERT INTO wp_transaction_lookup(post_id,user_id,post_status,post_created_at)
      VALUES (NEW.ID,post_author_id,val_post_status,NEW.post_date);

      SET transaction_lookup_id := last_insert_id();

      IF post_status_error IS NOT NULL THEN
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'post_status',post_status_error);
      END IF;


    END IF;
  END

