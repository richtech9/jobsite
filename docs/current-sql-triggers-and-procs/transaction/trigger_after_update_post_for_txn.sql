CREATE TRIGGER trigger_after_update_post_for_txn
  AFTER UPDATE
  ON wp_posts
  FOR EACH ROW
  BEGIN
    # code-notes version 3 March-9-2021: Added Post Date
    # code-notes version 2 March-8-2021: Added Post Status
    # code-notes version 1 March-6-2021


    DECLARE val_post_status TINYINT;
    DECLARE old_val_post_status TINYINT;
    DECLARE transaction_lookup_id INT; -- for wp_transaction_lookup
    DECLARE post_author_id BIGINT UNSIGNED;
    DECLARE old_user BIGINT UNSIGNED;
    DECLARE post_meta_id BIGINT UNSIGNED;
    DECLARE old_val_post_created_at DATETIME;

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

      -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
      SELECT k.id,k.user_id ,post_status,post_created_at
      INTO transaction_lookup_id,old_user,old_val_post_status,old_val_post_created_at
      FROM wp_transaction_lookup k WHERE k.post_id = NEW.ID;

      IF transaction_lookup_id IS NULL THEN -- insert new row with author id, its ok if its null

        INSERT INTO wp_transaction_lookup(post_id,user_id,post_status)
        VALUES (NEW.ID,post_author_id,post_status);

        SET transaction_lookup_id := last_insert_id();

      ELSEIF old_user IS NULL OR
              post_author_id IS NULL OR
              post_author_id <>  old_user OR
              val_post_status <> old_val_post_status OR
              NEW.post_date <> old_val_post_created_at OR
              NEW.post_date IS NOT NULL AND old_val_post_created_at IS NULL
      THEN
        UPDATE wp_transaction_lookup k
        SET k.user_id = post_author_id, k.post_status = val_post_status, post_created_at = NEW.post_date
        WHERE k.id = transaction_lookup_id;
      END IF;

      IF post_status_error IS NOT NULL THEN
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'post_status',post_status_error);
      END IF;


      #check to see if related to key needs changing, although post ids don't normally change
      IF NEW.ID <> OLD.ID THEN
        SET post_meta_id := NULL;

        SELECT meta.meta_id INTO post_meta_id
        FROM wp_postmeta meta WHERE meta.meta_key = '_transactionRelatedTo' AND cast(meta.meta_value as unsigned) = OLD.ID;

        IF post_meta_id IS NOT NULL THEN -- insert new row with author id, its ok if its null

          UPDATE wp_postmeta SET meta_value = NEW.ID WHERE meta_id = post_meta_id;

        END IF;
      END IF;


    END IF;
  END

