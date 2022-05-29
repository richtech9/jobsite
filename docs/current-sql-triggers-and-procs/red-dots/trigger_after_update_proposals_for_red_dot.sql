-- code-notes version 0.3
-- History
-- version 0.2 listen to changes of status to rejected and completed so we cancel auto actions
-- version 0.3 when one red dot action is cancelled, then also cancel the other type also
CREATE TRIGGER trigger_after_update_proposals_for_red_dot
  AFTER UPDATE
  ON wp_proposals
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
    #contest customer
    freelancer requested completion (auto approve completion)
    listen for wp_proposals->status === request_completion
    set future time using auto_job_approvel_customer_hours
    */


    IF old.status <> 'request_completion' AND new.status = 'request_completion' THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = BUYER_TIME_OPTION;

      SELECT p.post_author INTO project_owner_id FROM wp_posts p WHERE p.ID = NEW.post_id;

      INSERT INTO wp_fl_red_dots(is_red_dot, is_future_action, event_user_id_role, event_user_id,
                                 proposal_id,contest_id,  future_timestamp, event_name)  VALUES
        (YES_RED_DOT,YES_FUTURE_ACTION,CUSTOMER_CODE,
         project_owner_id,NEW.id,NEW.post_id,
         FROM_UNIXTIME(seconds_in_future),
         'contest_asked_to_complete');
    END IF;


    /*
    #
      finished by  completed when customer says ok
      listen to wp_proposals->status changes to (completed)
        if so, for any red dot for the proposal of type contest_asked_to_complete that has is_future_action > 0
          then change future_timestamp to null and set is_future_action to -1
    */
    IF old.status NOT IN ('completed') AND new.status IN ('completed') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE proposal_id = new.id AND event_name in ('contest_asked_to_complete','contest_asked_to_reject') AND is_future_action > 0;

    END IF;



    /*
     #contest customer
    freelancer hired mediator
        listen for wp_proposals->status === hire_mediator
    */

    IF old.status <> 'hire_mediator' AND new.status = 'hire_mediator' THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = BUYER_TIME_OPTION;

      SELECT p.post_author INTO project_owner_id FROM wp_posts p WHERE p.ID = NEW.post_id;

      INSERT INTO wp_fl_red_dots(is_red_dot,  event_user_id_role, event_user_id,
                                 proposal_id, contest_id, event_name)  VALUES
        (YES_RED_DOT,CUSTOMER_CODE,
         project_owner_id,NEW.id, NEW.post_id,
         'contest_was_put_in_mediation');
    END IF;





      /*
      #contest freelancer
      customer rejected proposal (auto approve rejection)
          listen to wp_proposals->rejection_requested going from to 1 from 0
              set future_time using auto_job_rejected_for_linguist_hours
     */

    IF old.rejection_requested = 0 AND new.rejection_requested = 1 THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = FREELANCER_TIME_OPTION;

      INSERT INTO wp_fl_red_dots(is_red_dot,is_future_action, event_user_id_role, event_user_id,
                                 proposal_id,contest_id, future_timestamp,event_name)  VALUES
        (YES_RED_DOT,YES_FUTURE_ACTION,FREELANER_CODE,
         NEW.by_user,NEW.id,NEW.post_id,
         FROM_UNIXTIME(seconds_in_future),
         'contest_asked_to_reject');
    END IF;

    /*
    #
    cancelled by  approving rejection when the freelancer says ok
    listen to wp_fl_milestones->status changes to (approved_rejection)
      if so, for any red dot for the proposal of type contest_asked_to_reject that has is_future_action > 0
        then change future_timestamp to null and set is_future_action to -1
    */
    IF old.status NOT IN ('rejected') AND new.status IN ('rejected') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE proposal_id = new.id AND event_name in ('contest_asked_to_complete','contest_asked_to_reject') AND is_future_action > 0;


    END IF;

  END

