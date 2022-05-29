/*
Create random procedure tags: 25% each type: user,contest,project, content. Each using a random tag
Can create tags for one specific item by passing in (1,4,23) for example:
              to add to one user whose index in the temp table is 23
*/

DELIMITER $$
CREATE  PROCEDURE `create_random_project_tags`(IN number_to_make bigint unsigned,
                                                  IN force_type_to_use int,
                                                  IN force_index int
)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;


    DECLARE tag_id_string TEXT;
    DECLARE number_tags_to_use int;
    DECLARE number_choice_for_type int;
    DECLARE id_to_link bigint;
    DECLARE unsigned_id_to_link bigint unsigned;
    DECLARE type_to_use int;
    DECLARE loop_count int;
    DECLARE random_thing int;

    DECLARE tag_1 BIGINT;
    DECLARE tag_2 BIGINT;
    DECLARE tag_3 BIGINT;




    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_project_tags needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    START TRANSACTION;

    SET loop_count := 0;

    project_tag_loop: WHILE (loop_count < number_to_make) DO
      SET loop_count := loop_count + 1;
      SET tag_1 := NULL;
      SET tag_2 := NULL;
      SET tag_3 := NULL;
      SET tag_id_string := NULL;

      #get the random tag string to insert (1 to 3 tags randomly chosen, separated by commas )
      SELECT  FLOOR(RAND()*(3)) + 1 INTO number_tags_to_use;

       CASE number_tags_to_use
         WHEN 1 THEN

           SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
           WHERE 1
           ORDER BY RAND()
           LIMIT 1;

           SET tag_id_string := tag_1;

         WHEN 2 THEN

           SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
           WHERE 1
           ORDER BY RAND()
           LIMIT 1;

           SELECT t.ID  INTO tag_2 FROM wp_interest_tags t
           WHERE t.ID <> tag_1
           ORDER BY RAND()
           LIMIT 1;

           SET tag_id_string := CONCAT(tag_1,',',tag_2);

         WHEN 3 THEN
           SELECT t.ID  INTO tag_1 FROM wp_interest_tags t
           WHERE 1
           ORDER BY RAND()
           LIMIT 1;

           SELECT t.ID  INTO tag_2 FROM wp_interest_tags t
           WHERE t.ID <> tag_1
           ORDER BY RAND()
           LIMIT 1;

           SELECT t.ID  INTO tag_3 FROM wp_interest_tags t
           WHERE t.ID <> tag_1 AND t.ID <> tag_2
           ORDER BY RAND()
           LIMIT 1;

           SET tag_id_string := CONCAT(tag_1,',',tag_2,',',tag_3);

       ELSE
         BEGIN
         END;
       END CASE ;

      #select the id to associate, and its type
      IF force_type_to_use IS NULL THEN
        SELECT  FLOOR(RAND()*(4)) + 1 INTO number_choice_for_type;
      ELSE
        SET number_choice_for_type := force_type_to_use;
      END IF;


      CASE number_choice_for_type
        WHEN 1 THEN
          BEGIN
            -- project
            SET type_to_use := 1;

            # get random project from user in batch
            IF force_index IS NULL THEN
              SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
            ELSE
              SET random_thing := force_index;
            END IF;

            SELECT p.post_id,p.user_id INTO unsigned_id_to_link,da_user_id FROM temp_projects_in_batch p
            WHERE p.id = random_thing;

            SET id_to_link := CAST(unsigned_id_to_link as SIGNED);

          END;

        WHEN 2 THEN
        BEGIN
          -- content
          SET type_to_use := 2;
          # get random content from user in batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.content_id,p.user_id INTO id_to_link,da_user_id FROM temp_contents_in_batch p
          WHERE p.id = random_thing;

        END;

        WHEN 3 THEN
        BEGIN
          -- contest
          SET type_to_use := 3;
          # get random contest from user in batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.post_id,p.user_id INTO unsigned_id_to_link,da_user_id FROM temp_contests_in_batch p
          WHERE p.id = random_thing;

          SET id_to_link := CAST(unsigned_id_to_link as SIGNED);

        END;

        WHEN 4 THEN
        BEGIN
          -- user
          SET type_to_use := 4;
          # get random user from batch
          IF force_index IS NULL THEN
            SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;
          ELSE
            SET random_thing := force_index;
          END IF;

          SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
          WHERE p.id = random_thing;

          SET id_to_link := CAST(da_user_id as SIGNED);

        END;

      END CASE ;

      IF tag_1 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_1,id_to_link,type_to_use);
      END IF;

      IF tag_2 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_2,id_to_link,type_to_use);
      END IF;

      IF tag_3 IS NOT NULL THEN
        INSERT INTO wp_tags_cache_job(tag_id,job_id,type) VALUES (tag_3,id_to_link,type_to_use);
      END IF;


    END WHILE ;

    COMMIT;



  END$$
DELIMITER ;