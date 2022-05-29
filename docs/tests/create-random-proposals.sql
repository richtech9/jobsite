/* Create random proposals, inserts into wp_proposals */

DELIMITER $$
CREATE  PROCEDURE `create_random_proposals`(IN number_to_make bigint unsigned)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;
    DECLARE contest_post_id bigint unsigned;
    DECLARE random_thing int;
    DECLARE loop_count int;

#     DECLARE id_to_link bigint;
#     DECLARE unsigned_id_to_link bigint unsigned;





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

      #get a random contest from batch

      SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;

      SELECT p.post_id,p.user_id INTO contest_post_id,da_user_id FROM temp_contests_in_batch p
      WHERE p.id = random_thing;

      SET loop_count := loop_count  + 1;


      INSERT INTO wp_proposals (id, post_id, job_id, by_user, status, type, rating, mediator_id,
                                rejection_accepted, created_at, updated_at, revision_text, rejection_txt,
                                rejected_at, rejection_requested, number, rating_by_customer, rating_by_freelancer,
                                comments_by_customer, comments_by_freelancer, customer)
      VALUES (NULL, contest_post_id, 0, da_user_id, 'pending', 2, null, 0, 0, NOW(),NOW(),
        '', '', null, '0', 1, null, null, null, null, null);

      INSERT INTO wp_fl_post_user_lookup(post_id,author_id,lookup_flag,lookup_val)
        VALUES (contest_post_id,da_user_id,2,0) ON DUPLICATE KEY UPDATE lookup_val = lookup_val+1;


    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;
