-- code-notes version 0.6
-- History
-- version 0.2 fix rejected status word
-- version 0.3 go back to using reject instead of rejected
-- version 0.4 added job_id too
-- version 0.5 listen to changes of status to approved_rejection and completed so we cancel auto actions
-- version 0.6 when one red dot action is cancelled, then also cancel the other type also
CREATE TRIGGER trigger_after_update_milestones_for_red_dot
  AFTER UPDATE
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
    Completion is Requested (auto approve completion)
        listen to wp_fl_milestones->status = request_completion
            set future_timestamp using auto_job_approvel_customer_hours
    */

    IF old.status <> 'request_completion' AND new.status = 'request_completion' THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = BUYER_TIME_OPTION;

      SELECT p.post_author INTO project_owner_id FROM wp_posts p WHERE p.ID = NEW.project_id;

      INSERT INTO wp_fl_red_dots(is_red_dot, is_future_action, event_user_id_role, event_user_id,
                                 milestone_id, project_id, job_id, future_timestamp, event_name)  VALUES
        (YES_RED_DOT,YES_FUTURE_ACTION,CUSTOMER_CODE,
         project_owner_id,NEW.id,NEW.project_id,NEW.job_id,
         FROM_UNIXTIME(seconds_in_future),
         'project_asked_to_complete');
    END IF;

    /*
  #
    finished by  completed when customer says ok
    listen to wp_fl_milestones->status changes to (completed)
      if so, for any red dot for the milestone of type project_asked_to_complete that has is_future_action > 0
        then change future_timestamp to null and set is_future_action to -1
  */
    IF old.status NOT IN ('completed') AND new.status IN ('completed') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE milestone_id = new.id AND event_name in ( 'project_asked_to_complete','project_asked_to_reject') AND is_future_action > 0;


    END IF;




    /*
    #project freelancer
    job was rejected by customer (auto approve rejection)
        listen to wp_fl_milestones->status = reject
            set future_timestamp using auto_job_rejected_for_linguist_hours


       */
    IF old.status <> 'reject' AND new.status = 'reject' THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = FREELANCER_TIME_OPTION;

      INSERT INTO wp_fl_red_dots(is_red_dot,is_future_action, event_user_id_role, event_user_id,
                                 milestone_id,project_id,job_id, future_timestamp,event_name)  VALUES
        (YES_RED_DOT,YES_FUTURE_ACTION,FREELANER_CODE,
         NEW.linguist_id,NEW.id,NEW.project_id,NEW.job_id,
         FROM_UNIXTIME(seconds_in_future),
         'project_asked_to_reject');
    END IF;

    /*
    #
    cancelled by  approving rejection when the freelancer says ok
    listen to wp_fl_milestones->status changes to (approved_rejection)
      if so, for any red dot for the milestone of type project_asked_to_reject that has is_future_action > 0
        then change future_timestamp to null and set is_future_action to -1
    */
    IF old.status NOT IN ('approved_rejection') AND new.status IN ('approved_rejection') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE milestone_id = new.id AND event_name  in ( 'project_asked_to_complete','project_asked_to_reject')
            AND is_future_action > 0;


    END IF;


  END

