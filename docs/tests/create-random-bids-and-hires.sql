/* Create random bids: adds rows to wp_comments, wp_commentmeta (bid_price)

    # comments add row
      INSERT INTO wp_comments (comment_ID, comment_post_ID, comment_author, comment_author_email, comment_author_url, comment_author_IP, comment_date, comment_date_gmt, comment_content, comment_karma, comment_approved, comment_agent, comment_type, comment_parent, user_id)
        VALUES (NULL, random_project_id, da_user_login, da_user_email, '', '127.0.0.1', NOW(), NOW(), 'test bid', 0, '1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0', 'job_bid', 0, da_user_id);

     # commentmeta add two rows for each comment
     # lowest_project_price => post meta estimated_budgets of the project, string of characters before _
      INSERT INTO wp_commentmeta (meta_id, comment_id, meta_key, meta_value) VALUES
        (NULL, new_comment_id, 'bid_price', lowest_project_price);


      # and adds post meta (_bid_placed_by_{user_id} value user_id
       INSERT INTO wp_postmeta (meta_id, post_id, meta_key, meta_value) VALUES
       (NULL, random_project_id, CONCAT('_bid_placed_by_',da_user_id), da_user_id);

    # Of the bids, it will randomly hire a percentage of them
    # if random number between 1 and 100 <= percentage_hired
     # the add in the entry to wp_fl_jobs

     job_sequence_number => number of wp_fl_jobs with this bid_id = new_comment_id, + 1
     project_name => post meta of modified_id for  random_project_id
     hiring_user_id => randomly picked from batch, not equal to da_user_id

     INSERT INTO wp_fl_job (ID, job_seq, title, content, author, linguist_id, project_id, bid_id,
      amount, meta, post_date, job_status, rating_by_customer, comments_by_customer, comments_by_freelancer,
       rating_by_freelancer, updated_at)

     VALUES (NULL, job_sequence_number, project_name, 'Test hire note', hiring_user_id, da_user_id, random_project_id, new_comment_id,
            0, '', NOW(), 'pending', null, null, null, null, NOW());



 After this , next procedure will hire and pay milestones, randomly
 */
DELIMITER $$
CREATE  PROCEDURE `create_random_bids_and_hires`(IN number_to_make bigint unsigned,
                                               IN percentage_hired int)
  proc_random_bids: BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE count_users bigint unsigned;
    DECLARE da_hiring_user_id bigint unsigned;
    DECLARE da_bidding_user_id bigint unsigned;
    DECLARE random_project_id bigint unsigned;
    DECLARE da_bidding_user_login VARCHAR(60);
    DECLARE da_bidding_user_email VARCHAR(100);
    DECLARE new_comment_id bigint unsigned;
    DECLARE lowest_project_price VARCHAR(100);
    DECLARE rand_1_and_100 int;
    DECLARE job_sequence_number int;
    DECLARE project_name VARCHAR(100);

    DECLARE existing_php_serialized longtext;
    DECLARE php_serialized_array longtext;
    DECLARE new_array_index int;
    DECLARE new_job_id bigint unsigned;

    DECLARE random_thing int;
    DECLARE loop_count int;



    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
      ROLLBACK;
      RESIGNAL;  -- raise again the sql exception to the caller
    END;



    IF  number_to_make < 0 THEN
      SET msg := CONCAT('create_random_bids_and_hires=> number_to_make needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    IF  percentage_hired <= 0 OR percentage_hired > 100 THEN
      SET msg := CONCAT('create_random_bids_and_hires=> percentage_hired needs to be between 1 and 100 ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    SELECT count(u.id) INTO count_users FROM temp_users_in_batch u WHERE 1;

    IF  count_users < 2 THEN
      SET msg := CONCAT('Need at least two different users in this batch. One to make the bid and one to hire');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;
    END IF;

    SET loop_count := 0;

    START TRANSACTION;

    bid_loop: WHILE (loop_count < number_to_make ) DO

      SET loop_count := loop_count + 1;
      # get random project from user in batch
      SELECT  FLOOR(RAND()*(number_to_make)) + 1 INTO random_thing;

      SELECT p.post_id,p.user_id INTO random_project_id, da_hiring_user_id FROM temp_projects_in_batch p
      WHERE p.id = random_thing;

      # get random bidder from users in batch , where the bidder is not the project owner
      SELECT p.user_id INTO da_bidding_user_id FROM temp_users_in_batch p
      WHERE p.user_id <> da_hiring_user_id
      ORDER BY RAND()
      LIMIT 1;

      # get the bidder details
      SELECT u.user_login,u.user_email
      INTO da_bidding_user_login,da_bidding_user_email
      FROM wp_users u
      WHERE u.ID = da_bidding_user_id;


      INSERT INTO wp_comments (comment_ID, comment_post_ID, comment_author, comment_author_email,
                               comment_author_url, comment_author_IP, comment_date, comment_date_gmt,
                               comment_content, comment_karma, comment_approved, comment_agent,
                               comment_type, comment_parent, user_id)
      VALUES (NULL, random_project_id, da_bidding_user_login, da_bidding_user_email, '', '127.0.0.1', NOW(), NOW(), 'test bid',
                    0, '1',
              'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:79.0) Gecko/20100101 Firefox/79.0',
              'job_bid', 0, da_bidding_user_id);

      SET new_comment_id := last_insert_id();

      INSERT INTO wp_fl_post_user_lookup(post_id,author_id,lookup_flag,lookup_val)
      VALUES (random_project_id,da_bidding_user_id,1,0) ON DUPLICATE KEY UPDATE lookup_val = lookup_val+1;

      # commentmeta add two rows for each comment
      # lowest_project_price => post meta estimated_budgets of the project, string of characters before _
      SELECT CAST(LEFT(meta_value,INSTR(meta_value,'_')-1) as signed)   INTO lowest_project_price
        FROM wp_postmeta where post_id = random_project_id AND meta_key = 'estimated_budgets' ;

      INSERT INTO wp_commentmeta (meta_id, comment_id, meta_key, meta_value) VALUES
        (NULL, new_comment_id, 'bid_price', lowest_project_price);



      #randomly decide to hire
      SELECT  FLOOR(RAND()*(100)) + 1 INTO rand_1_and_100;
      IF rand_1_and_100 > percentage_hired THEN
        ITERATE bid_loop;
      END IF ;

#     job_sequence_number => number of wp_fl_jobs with this project_id = random project id, + 1
#     project_name => post meta of modified_id for  random_project_id
#     hiring_user_id => randomly picked from batch, not equal to da_user_id

      SELECT (count(*) + 1) INTO job_sequence_number FROM wp_fl_job WHERE project_id = random_project_id;
      SELECT CONCAT(meta_value,'_',job_sequence_number) INTO project_name FROM wp_postmeta
        WHERE  post_id = random_project_id AND meta_key =  'modified_id' ;





      INSERT INTO wp_fl_job (ID, job_seq, title, content, author, linguist_id, project_id, bid_id,
                             amount, meta, post_date, job_status, rating_by_customer, comments_by_customer, comments_by_freelancer,
                             rating_by_freelancer, updated_at)

      VALUES (NULL, job_sequence_number, project_name, 'Test hire note', da_hiring_user_id, da_bidding_user_id,
                    random_project_id, new_comment_id,
                    0, '', NOW(), 'pending', null, null, null, null, NOW());

      SET new_job_id := last_insert_id();


    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;