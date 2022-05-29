/* Create random milestones and payments: adds rows to wp_payment_history, wp_fl_transaction ,wp_fl_milestones

    select random job into random_job_id  from batch of users, that has a bid
    set da_user_id as the user which made the random job
    select random_project_id as the job's project
    select random_bid_id as the job's first bid (if there is more than one)

    see if wallet of project owner has amount, if not, then fill wallet with payment history and set user meta of total_user_balance
    SELECT CAST(meta_value as DECIMAL) INTO da_user_balance FROM wp_usermeta WHERE user_id = da_user_id AND meta_key = 'total_user_balance'

    IF da_user_balance IS NOT NULL
    INSERT INTO wp_payment_history (id, txn_id, payment_amount, payment_status, description, payment_type,
                        item_name, user_id, item_id, zip_code, refill_by, order_type, created_time)
     VALUES (NULL, CONCAT('test-tx-',UUID()), 999999, 'Completed', null, 'Test', 'Refill', da_user_id, null, null, 0, 0, NOW());

    INSERT INTO wp_usermeta (umeta_id, user_id, meta_key, meta_value) VALUES (NULL, da_user_id, 'total_user_balance', '99999.00');
    SET da_user_balance = 99999.00;


    END IF;


    # add the milestone

    SELECT (MAX(number) +1) INTO milestone_number from wp_fl_milestones where project_id=random_project_id AND linguist_id =  freelancer_user_id;

    INSERT INTO wp_fl_milestones (ID, job_id, project_id, bid_id, content, amount, author, linguist_id, delivery_date,
                                  post_date, post_modified, status, dispute, created_at, updated_at, revision_text,
                                  rejection_txt, rejected_at, rejection_requested, completion_requested,
                                  completed_at, number)
    VALUES (NULL, random_job, random_project_id, random_bid_id, CONCAT('Test milestone ', uuid()), random_bid_amount,
                  da_user_id, freelancer_user_id, NOW(), NOW(), null,
      'completed', 0, NOW(), NOW(), '', '', null, '0', '1', NOW(), milestone_number);

    new_milestone_id is new insert id

    #make message saying milestone created, and remove the cost of it (ignoring fees) from the user wallet

    INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                   user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
    VALUES (NULL, CONCAT('test-tx-',UUID()), -random_bid_amount, 'done', 'Milestone created', 'milestone_created_by_customer',
                 '', '', da_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

    #update balance
    UPDATE wp_usermeta set meta_value = (da_user_balance -  random_bid_amount) WHERE meta_key = 'total_user_balance'
          AND user_id = da_user_id;
    #add message

    INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                    customer, freelancer, added_by)
    VALUES (NULL, CONCAT('Milestone created by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

    # complete milestone (with message history) and give amount to freelancer
    INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                    customer, freelancer, added_by)
    VALUES (NULL, CONCAT('Milestone completed by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

    SELECT CAST(meta_value as DECIMAL) INTO freelancer_balance FROM wp_usermeta WHERE meta_key = 'total_user_balance'
          AND user_id = freelancer_user_id;

    SET  freelancer_balance:= freelancer_balance +   random_bid_amount;

     UPDATE wp_usermeta set meta_value = freelancer_balance WHERE meta_key = 'total_user_balance'
          AND user_id = freelancer_user_id;


 */

DELIMITER $$
CREATE  PROCEDURE `create_random_milestones_and_payments`(IN number_to_make bigint unsigned)
  proc_milestones_payments: BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE da_user_id bigint unsigned;
    DECLARE freelancer_user_id bigint unsigned;
    DECLARE random_project_id bigint unsigned;
    DECLARE random_job_id bigint unsigned;
    DECLARE random_bid_id BIGINT unsigned;
    DECLARE random_bid_amount DOUBLE;
    DECLARE da_user_balance DOUBLE;
    DECLARE freelancer_balance DOUBLE;
    DECLARE milestone_number INT;
    DECLARE new_milestone_id BIGINT;
    DECLARE da_user_display_name VARCHAR(250);

    DECLARE count_job int;

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


    START TRANSACTION;

    SET loop_count := 0;


    milestone_loop: WHILE (loop_count < number_to_make ) DO

      #     select random job into random_job_id  from batch of users, that has a bid
      #     set da_user_id as the user which made the random job
      #     select random_project_id as the job's project
      #     select random_bid_id as the job's first bid (if there is more than one)

      #get a random job from batch that does not already have a milestone

      SET loop_count := loop_count + 1;
      SET random_job_id := NULL;


      SELECT count(*),j.ID,j.bid_id,j.linguist_id,j.author,display_name,j.project_id
      INTO count_job,random_job_id,random_bid_id,freelancer_user_id,da_user_id,da_user_display_name , random_project_id
      FROM wp_fl_job j
        INNER JOIN temp_users_in_batch u ON u.user_id = j.author
        INNER JOIN wp_users ON wp_users.ID = u.user_id
        LEFT JOIN wp_fl_milestones stone ON stone.job_id = j.id
      WHERE stone.ID IS NULL
      ORDER BY RAND()
      LIMIT 1;

      # it could be there are no more jobs to give milestones to, that have not already received one
      IF random_job_id IS NULL THEN
        COMMIT ;
        LEAVE proc_milestones_payments;
      END IF;



      # get the bid amount
      SELECT m.meta_value INTO random_bid_amount FROM wp_commentmeta m
      WHERE  m.comment_id =  random_bid_id AND m.meta_key = 'bid_price';



      #   see if wallet of project owner has amount, if not, then fill wallet with payment history and set user meta of total_user_balance
      SELECT CAST(meta_value as DECIMAL) INTO da_user_balance FROM wp_usermeta WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

      IF da_user_balance < random_bid_amount THEN

        INSERT INTO wp_payment_history (id, txn_id, payment_amount, payment_status, description, payment_type,
                                        item_name, user_id,  refill_by,  created_time)
        VALUES (NULL, CONCAT('test-tx-',UUID()), 999999, 'Completed', null, 'Test', 'Refill', da_user_id, null, NOW());

        SET da_user_balance = 99999.00;

      END IF;


      # add the milestone

      SELECT (MAX(number) +1) INTO milestone_number from wp_fl_milestones where project_id=random_project_id AND linguist_id =  freelancer_user_id;
      IF milestone_number IS NULL THEN
        SET milestone_number := 1;
      END IF;

      INSERT INTO wp_fl_milestones (ID, job_id, project_id, bid_id, content, amount, author, linguist_id, delivery_date,
                                    post_date, post_modified, status, dispute, created_at, updated_at, revision_text,
                                    rejection_txt, rejected_at, rejection_requested, completion_requested,
                                    completed_at, number)
      VALUES (NULL, random_job_id, random_project_id, random_bid_id, CONCAT('Test milestone ', uuid()), random_bid_amount,
                    da_user_id, freelancer_user_id, NOW(), NOW(), NULL,
        'completed', 0, NOW(), NOW(), '', '', NULL, '0', '1', NOW(), milestone_number);

      SET new_milestone_id := LAST_INSERT_ID();

      #make message saying milestone created, and remove the cost of it (ignoring fees) from the user wallet

      INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                     user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
      VALUES (NULL, CONCAT('test-tx-',UUID()), -random_bid_amount, 'done', 'Milestone created', 'milestone_created_by_customer',
                    'wallet', '', da_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

      #update balance
      UPDATE wp_usermeta set meta_value = (da_user_balance -  random_bid_amount) WHERE user_id = da_user_id AND meta_key = 'total_user_balance';

      #add message

      INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                      customer, freelancer, added_by)
      VALUES (NULL, CONCAT('Milestone created by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

      # complete milestone (with message history) and give amount to freelancer
      INSERT INTO wp_message_history (id, message, milestone_id, proposal_id, content_id, created_at,
                                      customer, freelancer, added_by)
      VALUES (NULL, CONCAT('Milestone completed by ',da_user_display_name), new_milestone_id, 0, 0, NOW(), 0, 0, da_user_id);

      SELECT CAST(meta_value as DECIMAL) INTO freelancer_balance FROM wp_usermeta WHERE meta_key = 'total_user_balance'
                                                                                        AND user_id = freelancer_user_id;

      SET  freelancer_balance:= freelancer_balance +   random_bid_amount;

      UPDATE wp_usermeta set meta_value = freelancer_balance WHERE user_id = freelancer_user_id AND meta_key = 'total_user_balance';


      INSERT INTO wp_fl_transaction (ID, txn_id, amount, payment_status, description, type, gateway, gateway_txn_id,
                                     user_id, user_id_added_by, project_id, job_id, milestone_id, time, refundable)
      VALUES (NULL, CONCAT('test-tx-',UUID()), random_bid_amount, 'done', 'Milestone Completed', 'milestone_completed',
                    'wallet', '', freelancer_user_id, da_user_id, random_project_id ,random_job_id , new_milestone_id, NOW(), 0);

    END WHILE ;


    COMMIT;



  END$$
DELIMITER ;