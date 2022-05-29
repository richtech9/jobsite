<?php


class FreelinguistCronBaseRepeat extends FreelinguistCronBase
{
    const OPTION_NAME = 'defined-in-child';
    const ACTION_NAME = 'defined-in-child';
    const ACTION_GROUP_NAME ='defined-in-child';
    const STOP_ACTION_NAME = 'defined-in-child';
    const B_START_IMMEDIATE_ACTION = false;
    const WORD_FOR_PAGE = 'iteration';

    const MY_VERY_OWN_ARGS = ['defined-in-child'];

    const MAX_LOG_SIZE = 100;


    //overrides stop ,start, resume to do the timed tasks correctly

    public static function stop()
    {
        //can't hurt to stop things that never ran, I think, so will not do checks here to see if task in AS queue
        as_unschedule_all_actions(
            static::ACTION_NAME,
            static::MY_VERY_OWN_ARGS,
            static::ACTION_GROUP_NAME
        );
        parent::stop();
    }

    public static function run()
    {
        //stop any old timer
        as_unschedule_all_actions(
            static::ACTION_NAME,
            static::MY_VERY_OWN_ARGS,
            static::ACTION_GROUP_NAME
        );


        parent::run(); //parent will not start the single action, as we defined B_START_IMMEDIATE_ACTION to be false here
        $interval_in_seconds = static::get_timer_seconds();
        if (!$interval_in_seconds) {
            static::stop(); //don't run with 0 seconds
            throw new RuntimeException("Cannot run if seconds interval is not set");
        }

        $timestamp = time(); //start from now
        as_schedule_recurring_action($timestamp, $interval_in_seconds,
            static::ACTION_NAME, static::MY_VERY_OWN_ARGS, static::ACTION_GROUP_NAME);

    }

    public static function resume()
    {

        //stop any old timer
        as_unschedule_all_actions(
            static::ACTION_NAME,
            static::MY_VERY_OWN_ARGS,
            static::ACTION_GROUP_NAME
        );

        parent::resume(); //will not start the single action, as we defined B_START_IMMEDIATE_ACTION to be false here

        $interval_in_seconds = static::get_timer_seconds();
        $timestamp = time(); //start from now
        as_schedule_recurring_action($timestamp, $interval_in_seconds,
            static::ACTION_NAME,
            static::MY_VERY_OWN_ARGS,
            static::ACTION_GROUP_NAME);
    }



    public static function process_cron_controls() {
        $our_class_name = static::class;
        throw new LogicException("process_cron_controls() is Not implemented for the derived class of $our_class_name");
    }


}