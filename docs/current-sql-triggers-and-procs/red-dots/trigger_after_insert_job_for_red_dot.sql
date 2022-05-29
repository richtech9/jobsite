-- code-notes version 0.1
CREATE TRIGGER trigger_after_insert_job_for_red_dot
  AFTER INSERT 
  ON wp_fl_job
  FOR EACH ROW
  BEGIN

    DECLARE seconds_in_future INT;
    DECLARE CUSTOMER_CODE INT;
    DECLARE FREELANER_CODE INT;
    DECLARE YES_RED_DOT INT;
    DECLARE YES_FUTURE_ACTION INT;
    DECLARE BUYER_TIME_OPTION VARCHAR(191);
    DECLARE FREELANCER_TIME_OPTION VARCHAR(191);

    SET seconds_in_future := 0;
    SET CUSTOMER_CODE := 1;
    SET FREELANER_CODE := 2;
    SET YES_RED_DOT := 1;
    SET YES_FUTURE_ACTION := 1;
    SET BUYER_TIME_OPTION := 'auto_job_approvel_customer_hours';
    SET FREELANCER_TIME_OPTION := 'auto_job_rejected_for_linguist_hours';
    
    /*
    #project freelancer
      Was Hired by Customer
        listen to new row in wp_fl_jobs with linguist_id mapping to the event user
     */
    INSERT INTO wp_fl_red_dots(is_red_dot, event_user_id_role, event_user_id,
                               job_id,project_id, event_name)  VALUES
      (YES_RED_DOT,FREELANER_CODE,
       NEW.linguist_id,NEW.id,NEW.project_id,
       'project_was_hired');

  END

