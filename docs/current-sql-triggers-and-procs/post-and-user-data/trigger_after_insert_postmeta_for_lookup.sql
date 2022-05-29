CREATE TRIGGER trigger_after_insert_postmeta_for_lookup
  AFTER INSERT
  ON wp_postmeta
  FOR EACH ROW
  BEGIN
    -- code-notes 2021-March-25 added create_batch
    -- for each of the meta keys, update the correct number for the value, the post id row may already exist thanks to the post triggers
    DECLARE val_project_new_status TINYINT;
    DECLARE val_is_guaranted TINYINT;
    DECLARE val_is_cancellation_approved TINYINT;
    DECLARE val_fl_job_type TINYINT;
    DECLARE val_hide_job TINYINT;
    DECLARE val_is_test_data TINYINT;
    DECLARE val_post_status TINYINT;
    DECLARE val_post_type TINYINT;
    DECLARE val_job_standard_delivery_date INT;
    DECLARE val_modified_id INT;
    DECLARE da_post_id_itself BIGINT UNSIGNED;
    DECLARE post_data_lookup_id INT; -- for wp_post_data_lookup
    DECLARE post_author_id BIGINT UNSIGNED; -- for getting the wp_users id from the post when creating thp_post_data_lookup
    DECLARE post_type_words VARCHAR(20);
    DECLARE post_status_words VARCHAR(20);

    DECLARE val_job_title TEXT;
    DECLARE val_job_description MEDIUMTEXT;

    DECLARE post_status_error TEXT;
    DECLARE post_type_error TEXT;
    DECLARE date_test INT;

    SET val_project_new_status := 0;
    SET val_is_guaranted := 0;
    SET val_is_cancellation_approved := 0;
    SET val_fl_job_type := 0;
    SET val_is_test_data := 0;
    SET val_hide_job := 0;
    SET val_post_status := 0;
    SET val_post_type := 0;
    SET val_job_standard_delivery_date := 0;
    SET val_modified_id := 0;

    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO post_data_lookup_id FROM wp_fl_post_data_lookup k WHERE k.post_id = NEW.post_id;

    SELECT p.ID INTO da_post_id_itself
    FROM wp_posts p
    WHERE p.ID = NEW.post_id AND p.post_type = 'job';

    IF da_post_id_itself IS NOT NULL AND post_data_lookup_id IS NULL THEN -- insert new row with author id, its ok if its null
      SELECT post_author,post_status,post_type
      INTO post_author_id , post_status_words, post_type_words
      FROM wp_posts WHERE ID = NEW.post_id;

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
      VALUES (NEW.post_id,post_author_id,val_post_status,val_post_type);

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
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'project_new_status' THEN
      IF NEW.meta_value like '%working%' THEN SET val_project_new_status := 1;
      ELSEIF NEW.meta_value like '%completed%' THEN SET val_project_new_status := 2;
      ELSEIF NEW.meta_value like '%rejected%' THEN SET val_project_new_status := 3;
      ELSEIF NEW.meta_value like '%delivery%' THEN SET val_project_new_status := 4;
      ELSEIF NEW.meta_value like '%dispute%' THEN SET val_project_new_status := 5;
      ELSEIF NEW.meta_value like '%mediation%' THEN SET val_project_new_status := 6;
      ELSEIF NEW.meta_value like '%delivering%' THEN SET val_project_new_status := 7;
      ELSEIF NEW.meta_value like '%review%' THEN SET val_project_new_status := 8;
      ELSEIF NEW.meta_value = '' THEN SET val_project_new_status := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_project_new_status := 0;
      ELSE
        SET val_project_new_status := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'project_new_status',CONCAT('did not find keyword in project_new_status: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.project_new_status = val_project_new_status WHERE k.id = post_data_lookup_id;
    END IF;

    #     is_guaranted                TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'is_guaranted' THEN
      IF NEW.meta_value = '1' THEN SET val_is_guaranted := 1;
      ELSEIF NEW.meta_value = '0' THEN SET val_is_guaranted := 0;
      ELSEIF NEW.meta_value = '' THEN SET val_is_guaranted := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_is_guaranted := 0;
      ELSE
        SET val_is_guaranted := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'is_guaranted',CONCAT('did not recognize value in is_guaranted: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.is_guaranted = val_is_guaranted WHERE k.id = post_data_lookup_id;
    END IF;

    #     is_cancellation_approved    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'is_cancellation_approved' THEN
      IF NEW.meta_value = '1' THEN SET val_is_cancellation_approved := 1;
      ELSEIF NEW.meta_value = '0' THEN SET val_is_cancellation_approved := 0;
      ELSEIF NEW.meta_value = '' THEN SET val_is_cancellation_approved := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_is_cancellation_approved := 0;
      ELSE
        SET val_is_cancellation_approved := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'is_cancellation_approved',CONCAT('did not recognize value in is_cancellation_approved: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.is_cancellation_approved = val_is_cancellation_approved WHERE k.id = post_data_lookup_id;
    END IF;


    #     create_batch --> is_test_data    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1, if exists and not empty then 1 else then 0
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'create_batch' THEN
      IF NEW.meta_value = '' THEN SET val_is_test_data := 0;
      ELSEIF NEW.meta_value <> '' THEN SET val_is_test_data := 1;
      ELSEIF NEW.meta_value IS NULL THEN SET val_is_test_data := 0;
      ELSE
        SET val_is_test_data := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'is_test_data',CONCAT('did not recognize value in create_batch: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.is_test_data = val_is_test_data WHERE k.id = post_data_lookup_id;
    END IF;


    #     fl_job_type                 TINYINT CURRENTLY USED CAN BE INT FLAG, 0 for unset 1 for project, 2 for contest
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'fl_job_type' THEN
      IF NEW.meta_value = 'project' THEN SET val_fl_job_type := 1;
      ELSEIF NEW.meta_value = 'contest' THEN SET val_fl_job_type := 2;
      ELSE
        SET val_fl_job_type := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'fl_job_type',CONCAT('did not recognize value in fl_job_type: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.fl_job_type = val_fl_job_type WHERE k.id = post_data_lookup_id;
    END IF;

    #     hide_job                    TINYINT CURRENTLY USED CAN BE INT FLAG 0 or 1
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'hide_job' THEN
      IF NEW.meta_value = '1' THEN SET val_hide_job := 1;
      ELSEIF NEW.meta_value = '0' THEN SET val_hide_job := 0;
      ELSEIF NEW.meta_value = '' THEN SET val_hide_job := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_hide_job := 0;
      ELSE
        SET val_hide_job := -100;
        INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
        VALUES (post_data_lookup_id,'hide_job',CONCAT('did not recognize value in hide_job: ',NEW.meta_value));
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.hide_job = val_hide_job WHERE k.id = post_data_lookup_id;
    END IF;

    #     job_standard_delivery_date  cast to UNIX TIMESTAMP
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'job_standard_delivery_date' THEN
      IF NEW.meta_value = '' THEN SET val_job_standard_delivery_date := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_job_standard_delivery_date := 0;
      ELSE
        # test to see if valid date string in expected format
        SELECT NEW.meta_value REGEXP '^(19|20)[0-9]{2}-(0?[1-9]|1[012])-(0?[1-9]|1[0-9]|2[0-9]|3[01])$'INTO date_test ;
        IF date_test = 1 THEN
          SELECT unix_timestamp(NEW.meta_value) INTO val_job_standard_delivery_date;
          IF val_job_standard_delivery_date IS NULL THEN
            SET val_job_standard_delivery_date := -100;
            INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
            VALUES (post_data_lookup_id,'job_standard_delivery_date',CONCAT('did not recognize value in job_standard_delivery_date: ',NEW.meta_value));
          END IF;
        ELSE
          SET val_job_standard_delivery_date := -100;
          INSERT INTO wp_fl_post_lookup_errors(post_lookup_id, column_of_error,error_msg)
          VALUES (post_data_lookup_id,'job_standard_delivery_date',CONCAT('value of job_standard_delivery_date is not Year-Month-Day: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.job_standard_delivery_date = val_job_standard_delivery_date WHERE k.id = post_data_lookup_id;
    END IF;

    #   modified_id                 use numeric_modified_id
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'numeric_modified_id' THEN
      IF NEW.meta_value = '' THEN SET val_modified_id := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_modified_id := 0;
      ELSE
        SET val_modified_id := NEW.meta_value ;
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.modified_id = val_modified_id WHERE k.id = post_data_lookup_id;
    END IF;

    #   job_title                 set to null if empty string
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'project_title' THEN
      IF NEW.meta_value = '' THEN SET val_job_title := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_job_title := NULL;
      ELSE
        SET val_job_title := NEW.meta_value ;
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.job_title = val_job_title WHERE k.id = post_data_lookup_id;
    END IF;

    #   job_description                 set to null if empty string
    IF post_data_lookup_id IS NOT NULL AND NEW.meta_key = 'project_description' THEN
      IF NEW.meta_value = '' THEN SET val_job_description := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_job_description := NULL;
      ELSE
        SET val_job_description := NEW.meta_value ;
      END IF;

      UPDATE wp_fl_post_data_lookup k SET k.job_description = val_job_description WHERE k.id = post_data_lookup_id;
    END IF;

  END

