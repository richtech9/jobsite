
DELIMITER $$

CREATE  PROCEDURE manage_top_list_for_content(IN id_thing bigint unsigned, IN score_thing bigint unsigned)
  manage_da_list: BEGIN
    DECLARE does_option_exist int;
    DECLARE limit_per_tag int;
    DECLARE current_tag_count int;
    DECLARE double_check_tag_count int;
    DECLARE current_tag_id int;
    DECLARE current_thing_has_tag int;
    DECLARE tag_loop_done INT DEFAULT FALSE;
    DECLARE current_lowest_thing_id BIGINT UNSIGNED;

    DEClARE CursorForTags
    CURSOR FOR
      SELECT

        (SELECT count(*)
          FROM wp_display_unit_user_content all_units
          WHERE  all_units.tag_id = tag.tag_id AND
                  all_units.content_id IS NOT NULL
        )                                                 as tag_count,

                                                          tag.tag_id ,

        (SELECT
           EXISTS(SELECT 1  FROM wp_display_unit_user_content my_unit
           WHERE my_unit.content_id = tag.job_id AND my_unit.tag_id = tag.tag_id)
        )
                                                          as thing_has_tag

      FROM wp_tags_cache_job tag

      WHERE type = 2 AND job_id = id_thing;

    DECLARE CONTINUE HANDLER FOR NOT FOUND
    BEGIN
      #         SELECT CONCAT('TLC: Setting upper loop done flag'); 
      SET tag_loop_done = TRUE;
    END;

    # get the limit of things per tag, if the wp_option is not set, the default will be 10
    SET limit_per_tag := 10;
    SELECT EXISTS(SELECT 1  FROM wp_options WHERE option_name = 'freelinguist-limit-top-per-tag')
    INTO does_option_exist;

    IF does_option_exist THEN
      SELECT option_value INTO limit_per_tag FROM wp_options WHERE option_name = 'freelinguist-limit-top-per-tag';
    END IF;

    /*
    -- cursor (tag_cursor) for all the tags in the thing select to get the following info for each tag the thing has
                     which also is in common with the list top tags:
                1) number of things (count_things_per_tag)
                2) lowest live score (lowest_live_score_per_tag) (null if nothing in the tag for category (user or content))
                4) 1 or 0 whether this thing is already in the tag
     */




    OPEN CursorForTags;

    do_tag: LOOP
      -- And then fetch
      FETCH CursorForTags INTO current_tag_count,current_tag_id,current_thing_has_tag;

      #       SELECT CONCAT('TLC: new fetch'),current_tag_count,current_tag_id,current_thing_has_tag; 
      IF tag_loop_done THEN
        #         SELECT CONCAT('TLC: Breaking loop'); 
        LEAVE do_tag;
      END IF;

      -- is user or content already in the list for the tag ? if so do nothing
      IF current_thing_has_tag THEN
        #         SELECT CONCAT('TLC: Already have the tag. Going to next row'); 
        ITERATE do_tag;
      END IF ;

      IF current_tag_count < limit_per_tag THEN
        #         SELECT CONCAT('TLC: Inserting new row'); 
        INSERT INTO wp_display_unit_user_content(tag_id,content_id,score_when_added)
        VALUES(current_tag_id,id_thing,score_thing);
      ELSE
        #         SELECT CONCAT('TLC: Limit was reached'); 
        # 1)find content with current live score for this tag that is less than this score
        # 2)if said content exists, then remove said content from top tags
        # 3)insert self
        BEGIN
          DECLARE lower_thing_not_found INT DEFAULT -1;
          #have continue handler for this block, so loop does not end if there is nothing in this select
          DECLARE CONTINUE HANDLER FOR NOT FOUND
          BEGIN
            #             SELECT CONCAT('TLC: Setting lower loop not found flag'); 
            SET lower_thing_not_found = 1;
          END  ;

          SET current_lowest_thing_id = 0;
          SET double_check_tag_count := 0;

          SELECT
            IFNULL(unit.content_id,0),check_count.da_count_again into current_lowest_thing_id,double_check_tag_count
          FROM wp_linguist_content look
            INNER JOIN wp_display_unit_user_content unit ON look.id = unit.content_id AND unit.tag_id = current_tag_id
            INNER JOIN (
                         SELECT count(*) as da_count_again,tag_id
                         FROM wp_display_unit_user_content
                         WHERE content_id IS NOT NULL AND tag_id = current_tag_id
                         GROUP BY tag_id
                       ) as check_count ON check_count.tag_id
          WHERE look.score < score_thing
          ORDER BY look.score ASC
          LIMIT 1  ;

          #           SELECT CONCAT('TLC: figuring out if can drop any one',current_lowest_thing_id,double_check_tag_count); 

          IF current_lowest_thing_id > 0 THEN
            #             SELECT CONCAT('TLC: deleting that adding this',current_lowest_thing_id,id_thing);
            DELETE FROM wp_display_unit_user_content WHERE content_id = current_lowest_thing_id AND tag_id = current_tag_id;

            INSERT INTO wp_display_unit_user_content(tag_id,content_id,score_when_added)
            VALUES(current_tag_id,id_thing,score_thing);
          END IF ;

        END; # end block of else statement, control now resumes to the outer error handler

      END IF; #end if the current tag count compared to the limit of tags allowed



    END LOOP do_tag;
    CLOSE CursorForTags;


  END$$
DELIMITER ;
-- code-notes version 0.12

#VERSION NOTES
# 0.12
# NOW selects existing tag count in top tags based on the tags for content, and not all tags
# Resets the local variables to zero before the test sql, because if there is nothing lower, this will use the previous value for another tag