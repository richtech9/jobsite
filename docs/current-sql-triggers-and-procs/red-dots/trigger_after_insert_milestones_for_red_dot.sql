-- code-notes version 0.3
-- History
-- version 0.2 only notice milestones not created by the project owner
-- version 0.3 add job_id too
CREATE TRIGGER trigger_after_insert_milestones_for_red_dot
  AFTER INSERT
  ON wp_fl_milestones
  FOR EACH ROW
  BEGIN

    DECLARE project_owner_id BIGINT UNSIGNED;
    DECLARE seconds_in_future INT;
    DECLARE CUSTOMER_CODE INT;
    DECLARE FREELANER_CODE INT;
    DECLARE YES_RED_DOT INT;
    DECLARE YES_FUTURE_ACTION INT;
    DECLARE BUYER_TIME_OPTION VARCHAR(191);
    DECLARE FREELANCER_TIME_OPTION VARCHAR(191);

    SET project_owner_id := NULL;
    SET seconds_in_future := 0;
    SET CUSTOMER_CODE := 1;
    SET FREELANER_CODE := 2;
    SET YES_RED_DOT := 1;
    SET YES_FUTURE_ACTION := 1;
    SET BUYER_TIME_OPTION := 'auto_job_approvel_customer_hours';
    SET FREELANCER_TIME_OPTION := 'auto_job_rejected_for_linguist_hours';




    /*
    # project customer
    Milestone request made by freelancer
        listen to new wp_fl_milestones with status of requested
    */
    IF  NEW.status = 'requested' THEN

      SELECT p.post_author INTO project_owner_id FROM wp_posts p WHERE p.ID = NEW.project_id;

      if (project_owner_id <> NEW.author) THEN
        INSERT INTO wp_fl_red_dots(is_red_dot, event_user_id_role, event_user_id,
                                   milestone_id,job_id,project_id, event_name)  VALUES
          (YES_RED_DOT,CUSTOMER_CODE,project_owner_id,NEW.id,NEW.job_id,
           NEW.project_id,
           'project_requested_milestone');
      END IF ;
    END IF;




  END

