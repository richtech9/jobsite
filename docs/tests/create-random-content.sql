/* Creates random content for testing
  INSERTS into wp_linguist_content,wp_linguist_content_chapter
  */

DELIMITER $$
CREATE  PROCEDURE `create_random_content`(IN number_content_to_make bigint unsigned,
                                           IN test_batch_id VARCHAR(50))
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE current_content_id bigint unsigned;
    DECLARE test_contest_id bigint unsigned;
    DECLARE da_user_id bigint unsigned;
    DECLARE loop_content_number int;
    DECLARE random_thing int;


    DECLARE content_title TEXT;
    DECLARE content_summary TEXT;
    DECLARE number_of_chapters_to_add int;
    DECLARE content_price double;

    DECLARE chapter_number int;
    DECLARE chapter_title TEXT;
    DECLARE chapter_content TEXT;

    DECLARE temp_index INT;


    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;

    DROP TEMPORARY TABLE IF EXISTS temp_contents_in_batch;

    CREATE TEMPORARY TABLE temp_contents_in_batch
    (
      id                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
      content_id           int NOT NULL                COMMENT ' from wp_linguist_content.id ',
      user_id               BIGINT UNSIGNED NULL                COMMENT ' from wp_users.ID '
      COMMENT ' stores newly created content for batch ',
      INDEX idx_content_id (content_id),
      INDEX idx_user_id (user_id)
    )
      ENGINE = MyISAM;

    # initialize constants


    SELECT (MAX(ID) +1) into current_content_id FROM wp_linguist_content;



    IF  number_content_to_make < 0 THEN
      SET msg := CONCAT('number_content_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;
    set loop_content_number := 0;
    START TRANSACTION;
    content_loop: WHILE (loop_content_number < number_content_to_make) DO
      # check to make sure id does not exist in users table, if it does, then skip it
      SELECT ID into test_contest_id FROM wp_linguist_content WHERE ID = current_content_id ;
      IF test_contest_id IS NOT NULL THEN
        set current_content_id := current_content_id + 1;
        ITERATE content_loop;
      END IF ;

      # get random user from batch
      SELECT  FLOOR(RAND()*(number_content_to_make)) + 1 INTO random_thing;

      SELECT p.user_id INTO da_user_id FROM temp_users_in_batch p
      WHERE p.id = random_thing;

      set content_summary := concat('Test Content #', current_content_id, ' batch ', test_batch_id, '::',
                                    loop_content_number, ' ', uuid());
      set content_title := concat('Test Content # ', current_content_id, ' batch ', test_batch_id, '::',
                                  loop_content_number);
      SELECT  FLOOR(RAND()*(200))+20 INTO content_price;
      SELECT  FLOOR(RAND()*(5)) INTO number_of_chapters_to_add;

      INSERT INTO wp_linguist_content (id, user_id, content_title, content_summary,
                                       content_amount, publish_type, content_sale_type,
                                       content_cover_image, description_image, files, content_type, content_view,
                                        updated_at, created_at, offersBy, purchased_by, status,
                                       show_content, revision_text, rejection_txt, rejected_at, rejection_requested,
                                       purchased_at, freezed, rating_by_customer, rating_by_freelancer,
                                       comments_by_customer, comments_by_freelancer)
      VALUES (current_content_id, da_user_id, content_title,
                    content_summary,
                    content_price, 'Publish', 'Fixed',  'linguistcontent/this-is-only-a-test.png',
                  '/jobs/jobs/content-image-704324192.png', null, 0x0, 2,  NOW(), NOW(), '',
                                                                  0, 'pending', 1, '', null, null, '0', NOW(),
              '0', null, null, null, null);


      INSERT INTO temp_contents_in_batch(content_id, user_id) VALUES (current_content_id,da_user_id);
      SET temp_index := LAST_INSERT_ID();
      CALL create_random_project_tags(1,2,temp_index);
      SET loop_content_number := loop_content_number + 1;
      SET chapter_number := 0;
      #insert chapters
      chapter_loop: WHILE (number_of_chapters_to_add > 0) DO
        SET chapter_number := chapter_number + 1;
        SET chapter_title := CONCAT('Chapter ',chapter_number, ' of test book id ',current_content_id);
        SET chapter_content := CONCAT('The content: :',uuid());
        INSERT INTO wp_linguist_content_chapter (id, user_id, linguist_content_id, title, content, content_visible, updated_at, created_at)
         VALUES (NULL, da_user_id, current_content_id, chapter_title, chapter_content, '',NOW(), NOW());

        SET  number_of_chapters_to_add :=  number_of_chapters_to_add - 1;
      END WHILE ;

      set current_content_id := current_content_id + 1;

    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;
