-- code-notes version 0.4
-- History
-- version 0.2 listen to changes of status to rejected,cancelled and completed to cancel auto actions
-- version 0.3 set red dot as read for normal action, when we are setting the dot as completed for future
-- version 0.4 when one red dot action is cancelled, then also cancel the other type also
CREATE TRIGGER trigger_after_update_content_for_red_dot
  AFTER UPDATE
  ON wp_linguist_content
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
  # content customer
  Completion is Requested (auto approve completion)
    listen to wp_linguist_content->status changes to request_completion
      set future_timestamp using auto_job_approvel_customer_hours option
    @since VERSION 0.2
  */
    IF old.status <> 'request_completion' AND new.status = 'request_completion' THEN
      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = BUYER_TIME_OPTION;

      INSERT INTO wp_fl_red_dots(is_red_dot, is_future_action, event_user_id_role, event_user_id,
                                  content_id,  future_timestamp, event_name)  VALUES
                                  (YES_RED_DOT,YES_FUTURE_ACTION,CUSTOMER_CODE,
                                   NEW.purchased_by,NEW.id,
                                   FROM_UNIXTIME(seconds_in_future),
                                   'content_asked_to_complete');
    END IF;



  /*
 # content customer
   finished by  completed
   listen to wp_linguist_content->status changes to (completed)
     if so, for any red dot for the content of type content_asked_to_complete that has is_future_action > 0
       then change future_timestamp to null and set is_future_action to -1
     @since VERSION 0.2
 */
    IF old.status NOT IN ('completed') AND new.status IN ('completed') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE content_id = new.id AND event_name in ('content_asked_to_complete','content_asked_to_reject')
            AND is_future_action > 0;


    END IF;



  /*
  # content freelancer
    finished by  rejected or cancelled
    listen to wp_linguist_content->status changes to (rejected,cancelled)
      if so, for any red dot for the content of type content_asked_to_reject that has is_future_action > 0
        then change future_timestamp to null and set is_future_action to -1
  */
    IF old.status NOT IN ('rejected','cancelled') AND new.status IN ('rejected','cancelled') THEN

      UPDATE wp_fl_red_dots SET is_future_action = -1,  future_action_done_at = NOW(),is_red_dot = -1
      WHERE content_id = new.id AND event_name  in ('content_asked_to_complete','content_asked_to_reject')
            AND is_future_action > 0;


    END IF;


    /*
    # content freelancer
    Revision is Requested
    listen to wp_linguist_content->status changes to request_revision
    */

    IF old.status <> 'request_revision' AND new.status = 'request_revision' THEN

      INSERT INTO wp_fl_red_dots(is_red_dot, event_user_id_role, event_user_id,
                                 content_id, event_name)  VALUES
                  (YES_RED_DOT,FREELANER_CODE,NEW.user_id,NEW.id,'content_asked_to_revise');
    END IF;

    /*
    # content freelancer
    Rejection is Requested (auto approve rejection)
      listen to wp_linguist_content->status changes to request_rejection
        set future_timestamp using auto_job_rejected_for_linguist_hours
    */

    IF old.status <> 'request_rejection' AND new.status = 'request_rejection' THEN

      SELECT UNIX_TIMESTAMP(NOW()) + (CAST(option_value as SIGNED) * 60 * 60) INTO seconds_in_future
      FROM wp_options WHERE option_name = FREELANCER_TIME_OPTION;

      INSERT INTO wp_fl_red_dots(is_red_dot,is_future_action, event_user_id_role, event_user_id,
                                 content_id, future_timestamp,event_name)  VALUES
                              (YES_RED_DOT,YES_FUTURE_ACTION,FREELANER_CODE,
                               NEW.user_id,NEW.id,
                               FROM_UNIXTIME(seconds_in_future),
                               'content_asked_to_reject');
    END IF;


    /*
    # content freelancer
      content was purchased
        listen to wp_linguist_content->purchased_by changes to non null  going from null to value
    */

    IF old.purchased_by IS NULL AND NEW.purchased_by IS NOT NULL THEN

      INSERT INTO wp_fl_red_dots(is_red_dot, event_user_id_role, event_user_id,
                                 content_id, event_name)  VALUES
        (YES_RED_DOT,FREELANER_CODE,NEW.user_id,NEW.id,'content_was_purchased');
    END IF;


  END
