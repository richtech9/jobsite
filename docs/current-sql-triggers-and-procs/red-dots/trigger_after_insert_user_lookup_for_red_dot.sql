-- code-notes version 0.2
-- Version History
-- 0.2 Fix for not checking flag before adding red dot
CREATE TRIGGER trigger_after_insert_user_lookup_for_red_dot
  AFTER INSERT
  ON wp_fl_post_user_lookup
  FOR EACH ROW
  BEGIN

    DECLARE seconds_in_future INT;
    DECLARE CUSTOMER_CODE INT;
    DECLARE FREELANER_CODE INT;
    DECLARE YES_RED_DOT INT;
    DECLARE YES_FUTURE_ACTION INT;
    DECLARE BUYER_TIME_OPTION VARCHAR(191);
    DECLARE FREELANCER_TIME_OPTION VARCHAR(191);
    DECLARE CODE_USER_WAS_AWARDED INT;

    SET seconds_in_future := 0;
    SET CUSTOMER_CODE := 1;
    SET FREELANER_CODE := 2;
    SET YES_RED_DOT := 1;
    SET YES_FUTURE_ACTION := 1;
    SET BUYER_TIME_OPTION := 'auto_job_approvel_customer_hours';
    SET FREELANCER_TIME_OPTION := 'auto_job_rejected_for_linguist_hours';
    SET CODE_USER_WAS_AWARDED := 8;

    /*
     #contest freelancer
      customer awarded proposal
        listen to new row on wp_fl_post_user_lookup where the flag = 8
     */

    IF NEW.lookup_flag = 8 THEN
      INSERT INTO wp_fl_red_dots(is_red_dot, event_user_id_role, event_user_id,
                                 contest_id, proposal_id,event_name)  VALUES
        (YES_RED_DOT,FREELANER_CODE,
         NEW.author_id,NEW.post_id,NEW.lookup_val,
         'contest_was_awarded');
      END IF;

  END

