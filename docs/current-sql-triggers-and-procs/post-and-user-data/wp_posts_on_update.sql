CREATE TRIGGER trigger_after_update_posts_for_lookup
  BEFORE UPDATE
  ON wp_posts
  FOR EACH ROW
  BEGIN
    DECLARE post_data_lookup_id INT; -- for wp_post_data_lookup
    DECLARE val_post_status TINYINT;
    DECLARE val_post_type TINYINT;
    DECLARE post_author_id BIGINT UNSIGNED;

    DECLARE post_status_error TEXT;
    DECLARE post_type_error TEXT;

    IF NEW.post_type = 'job' THEN
      SET val_post_status := 0;
      SET val_post_type := 0;

      #     post_status                 TINYINT MAP TO INT , pending=> 1, publish=> 2, private => 3
      IF NEW.post_status = 'pending' THEN SET val_post_status := 1;
      ELSEIF NEW.post_status = 'publish' THEN SET val_post_status := 2;
      ELSEIF NEW.post_status = 'private' THEN SET val_post_status := 3;
      ELSEIF NEW.post_status = '' THEN SET val_post_status := 0;
      ELSEIF NEW.post_status IS NULL THEN SET val_post_status := 0;
      ELSE
        SET val_post_status := -100;
        SET post_status_error := CONCAT('did not find keyword in post status: ',NEW.post_status);
      END IF;

      #     post_type                   TINYINT MAP TO INT , job => 1, wallet => 2, revision => 5, faq=> 7
      IF NEW.post_type = 'job' THEN SET val_post_type := 1;
      ELSEIF NEW.post_type = 'wallet' THEN SET val_post_type := 2;
      ELSEIF NEW.post_type = 'revision' THEN SET val_post_type := 5;
      ELSEIF NEW.post_type = 'faq' THEN SET val_post_type := 7;
      ELSEIF NEW.post_type = '' THEN SET val_post_type := 0;
      ELSEIF NEW.post_type IS NULL THEN SET val_post_type := 0;
      ELSE
        SET val_post_type := -100;
        SET post_type_error := CONCAT('did not find keyword in post type: ',NEW.post_type);
      END IF;

      #post author empty make null

      IF NEW.post_author = '' THEN SET post_author_id := NULL;
      ELSEIF NEW.post_author IS NULL THEN SET post_author_id := NULL;
      ELSE
        SET post_author_id := NEW.post_author;
      END IF;

      -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
      SELECT k.id INTO post_data_lookup_id FROM wp_fl_post_data_lookup k WHERE k.post_id = NEW.ID;

      IF post_data_lookup_id IS NULL THEN -- insert new row with author id, its ok if its null

        INSERT INTO wp_fl_post_data_lookup(post_id,author_id,post_status,post_type)
        VALUES (OLD.ID,post_author_id,val_post_status,val_post_type);

        SET post_data_lookup_id := last_insert_id();

        IF post_status_error IS NOT NULL THEN
          INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
          VALUES (post_data_lookup_id,'post_status',post_status_error);
        END IF;

        IF post_type_error IS NOT NULL THEN
          INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
          VALUES (post_data_lookup_id,'post_type',post_type_error);
        END IF;

      END IF;

      UPDATE wp_fl_post_data_lookup k SET
        k.author_id = post_author_id,
        k.post_status = val_post_status,
        k.post_type = val_post_type,
        k.post_id = NEW.ID
      WHERE k.post_id = OLD.ID;
    END IF;
  END

