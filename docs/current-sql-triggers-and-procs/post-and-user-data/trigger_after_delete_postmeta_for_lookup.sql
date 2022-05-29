CREATE TRIGGER trigger_after_delete_postmeta_for_lookup
  BEFORE DELETE
  ON wp_postmeta
  FOR EACH ROW
  BEGIN
    -- code-notes 2021-March-25 added create_batch
    -- for each of the meta keys, update the correct number for the value, the post id may already exist thanks to the post triggers

    DECLARE val_post_status TINYINT;
    DECLARE val_post_type TINYINT;

    DECLARE post_data_lookup_id INT; -- for wp_post_data_lookup
    DECLARE da_post_id_itself BIGINT UNSIGNED;
    DECLARE post_author_id BIGINT UNSIGNED; -- for getting the wp_users id from the post when creating thp_post_data_lookup
    DECLARE post_type_words VARCHAR(20);
    DECLARE post_status_words VARCHAR(20);

    DECLARE post_status_error TEXT;
    DECLARE post_type_error TEXT;

    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO post_data_lookup_id FROM wp_fl_post_data_lookup k WHERE k.post_id = OLD.post_id;

    SELECT p.ID INTO da_post_id_itself
    FROM wp_posts p
    WHERE p.ID = OLD.post_id AND p.post_type = 'job';

    IF da_post_id_itself IS NOT NULL AND post_data_lookup_id IS NULL THEN -- insert new row with author id, its ok if its null
      SELECT post_author,post_status,post_type
      INTO post_author_id , post_status_words, post_type_words
      FROM wp_posts WHERE ID = OLD.post_id;

      #     post_status                 TINYINT MAP TO INT , pending=> 1, publish=> 2, private => 3
      IF post_status_words = 'pending' THEN SET val_post_status := 1;
      ELSEIF post_status_words = 'publish' THEN SET val_post_status := 2;
      ELSEIF post_status_words = 'private' THEN SET val_post_status := 3;
      ELSEIF post_status_words = '' THEN SET val_post_status := 0;
      ELSEIF post_status_words IS NULL THEN SET val_post_status := 0;
      ELSE
        SET val_post_status := -100;
        SET post_status_error := CONCAT('did not find keyword in post status: ',post_status_words);
      END IF;

      #     post_type                   TINYINT MAP TO INT , job => 1, wallet => 2, revision => 5, faq=> 7
      IF post_type_words = 'job' THEN SET val_post_type := 1;
      ELSEIF post_type_words = 'wallet' THEN SET val_post_type := 2;
      ELSEIF post_type_words = 'revision' THEN SET val_post_type := 5;
      ELSEIF post_type_words = 'faq' THEN SET val_post_type := 7;
      ELSEIF post_type_words = '' THEN SET val_post_type := 0;
      ELSEIF post_type_words IS NULL THEN SET val_post_type := 0;
      ELSE
        SET val_post_type := -100;
        SET post_type_error := CONCAT('did not find keyword in post type: ',post_type_words);
      END IF;

      INSERT INTO wp_fl_post_data_lookup(post_id,author_id,post_status,post_type)
      VALUES (OLD.post_id,post_author_id,val_post_status,val_post_type);

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


    #     project_new_status          TINYINT  (not exists (0), working (1),completed(2), not completed or working (3)
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'project_new_status' THEN
      UPDATE wp_fl_post_data_lookup k SET k.project_new_status = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     is_guaranted                TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'is_guaranted' THEN
      UPDATE wp_fl_post_data_lookup k SET k.is_guaranted = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     is_cancellation_approved    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'is_cancellation_approved' THEN
      UPDATE wp_fl_post_data_lookup k SET k.is_cancellation_approved = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     fl_job_type                 TINYINT CURRENTLY USED CAN BE INT FLAG, 0 for unset 1 for project, 2 for contest
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'fl_job_type' THEN
      UPDATE wp_fl_post_data_lookup k SET k.fl_job_type = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     create_batch --> is_test_data    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1, if exists and not empty then 1 else then 0
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'create_batch' THEN
      UPDATE wp_fl_post_data_lookup k SET k.is_test_data = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     hide_job                    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'hide_job' THEN
        UPDATE wp_fl_post_data_lookup k SET k.hide_job = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #     job_standard_delivery_date  cast to UNIX TIMESTAMP
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'job_standard_delivery_date' THEN
      UPDATE wp_fl_post_data_lookup k SET k.job_standard_delivery_date = 0 WHERE  k.id = post_data_lookup_id;
    END IF;

    #   modified_id                 use numeric_modified_id
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'numeric_modified_id' THEN
      UPDATE wp_fl_post_data_lookup k SET k.modified_id = 0 WHERE k.id = post_data_lookup_id;
    END IF;

    #   job_title                 set to null when missing
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'project_title' THEN
      UPDATE wp_fl_post_data_lookup k SET k.job_title = NULL WHERE k.id = post_data_lookup_id;
    END IF;

    #   job_description                 set to null when missing
    IF post_data_lookup_id IS NOT NULL AND OLD.meta_key = 'project_description' THEN
      UPDATE wp_fl_post_data_lookup k SET k.job_description = NULL WHERE k.id = post_data_lookup_id;
    END IF;

  END

