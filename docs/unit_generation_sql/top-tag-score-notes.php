<?php



/*
 * sql scratch copy
 -- freelancer units


-- old sql once we have a tag id

-- strategy , created inner join with the ids, then get the details from that driving the where

EXPLAIN SELECT user_id FROM wp_fl_user_data_lookup look
WHERE rating_as_freelancer  AND has_user_image
order by last_update desc limit 10;

(SELECT t.id,t.tag_id,  valid_users.user_id
FROM wp_tags_cache_job t
  INNER JOIN (
    SELECT look.user_id,look.last_update FROM wp_fl_user_data_lookup look
    WHERE look.rating_as_freelancer  AND look.has_user_image
    order by look.last_update desc
    ) as valid_users ON valid_users.user_id = t.job_id
WHERE t.tag_id = 31 AND t.type = 4
ORDER BY valid_users.last_update desc
LIMIT 10)
UNION ALL
(SELECT t.id,t.tag_id,  valid_users.user_id
 FROM wp_tags_cache_job t
   INNER JOIN (
                SELECT look.user_id,look.last_update FROM wp_fl_user_data_lookup look
                WHERE look.rating_as_freelancer  AND look.has_user_image
                order by look.last_update desc
              ) as valid_users ON valid_users.user_id = t.job_id
 WHERE t.tag_id = 30 AND t.type = 4
 ORDER BY valid_users.last_update desc
 LIMIT 10)
UNION ALL
(SELECT t.id,t.tag_id,  valid_users.user_id
 FROM wp_tags_cache_job t
   INNER JOIN (
                SELECT look.user_id,look.last_update FROM wp_fl_user_data_lookup look
                WHERE look.rating_as_freelancer  AND look.has_user_image
                order by look.last_update desc
              ) as valid_users ON valid_users.user_id = t.job_id
 WHERE t.tag_id = 29 AND t.type = 4
 ORDER BY valid_users.last_update desc
 LIMIT 10)

;

explain SELECT t.id,t.tag_id,  valid_users.user_id
FROM wp_tags_cache_job t
  INNER JOIN (
               SELECT look.user_id,look.last_update FROM wp_fl_user_data_lookup look
               WHERE look.rating_as_freelancer  AND look.has_user_image
               order by look.last_update desc
             ) as valid_users ON t.tag_id = 29 AND valid_users.user_id = t.job_id AND  t.type = 4
WHERE 1
ORDER BY valid_users.last_update desc limit 10;

select t.tag_id,look.user_id,look.rating_as_freelancer,look.has_user_image, look.last_update
FROM wp_tags_cache_job t
INNER JOIN wp_fl_user_data_lookup look ON look.user_id = t.job_id
where 1;

select count(*) as da_counter
FROM wp_tags_cache_job t
  INNER JOIN wp_fl_user_data_lookup look ON look.user_id = t.job_id
where 1;




select t.id,t.tag_id FROM wp_tags_cache_job t WHERE t.tag_id = 31 AND t.type = 4;

select t.id,t.tag_id FROM wp_tags_cache_job t WHERE t.tag_id = 31 AND t.type = 4 AND job_id = 3604;



CREATE INDEX time_idx ON wp_fl_user_data_lookup (last_update DESC);

-- we want users that have a user icon, and rating_as_freelancer
SELECT
  u.ID primary_id,
  u.user_nicename,
  '' user_id,
  u.ID as the_id,
  u.display_name title,
  (SELECT meta_value FROM `wp_usermeta` WHERE `meta_key` = 'description' and user_id=u.ID) description,
  '0' price,
  (SELECT meta_value FROM `wp_usermeta` WHERE `meta_key` = '_description_image' and user_id=u.ID) description_image,
  (SELECT meta_value FROM `wp_usermeta` WHERE `meta_key` = 'user_image' and user_id=u.ID) image,
  '' content_sale_type,
  'translator' job_type,
  '0' is_sold
FROM wp_tags_cache_job wtcj
  LEFT JOIN wp_users u ON u.ID=wtcj.job_id
  INNER JOIN wp_usermeta wu ON u.ID = wu.user_id
WHERE wu.meta_key = 'wp_capabilities'
      AND wu.meta_value LIKE '%translator%' AND wtcj.type=4 AND wtcj.tag_id=31 GROUP BY u.ID;
 */

/*
 Implementation plans per documentation and spec/tag, display unit, and elasticsearch preprocessing.txt
 This table does two things it keeps the list of top tags (with both the user_id and content_id as null)
 */

/*
CREATE TABLE wp_display_unit_user_content
(
  id INT AUTO_INCREMENT PRIMARY KEY,
  tag_id   BIGINT  NOT NULL comment 'pk of tag',
  user_id   BIGINT UNSIGNED DEFAULT NULL comment 'null if this is a content id,or top tag definition else will be pk of user',
  content_id INT DEFAULT NULL comment 'null if this is a user id,or top tag definition else will be pk of content',
  is_top_tag int NOT NULL DEFAULT 0,
  score_when_added  BIGINT UNSIGNED DEFAULT 0 NOT NULL comment 'for debugging only and not part of logic',
  test_flag INT DEFAULT 0 NOT NULL comment 'for passing in temp values to mark tests',
  last_update TIMESTAMP DEFAULT NOW() ON UPDATE NOW() comment 'for seeing when the last time this row changed',
  when_added DATETIME  DEFAULT NOW() NOT NULL comment 'when_added will not change when last_update does',
  when_html_updated DATETIME  DEFAULT NULL comment 'updated via cron job when it creates the html',
  html_generated MEDIUMTEXT DEFAULT NULL comment 'updated via cron job when it creates the html',

  CONSTRAINT udx_user_per_tag UNIQUE (user_id,tag_id) comment 'each user can only be associated with the same tag once',
  CONSTRAINT udx_content_per_tag UNIQUE(content_id,tag_id) comment 'each content can only be associated with the same tag once',

  INDEX idx_user_content_tag (user_id, content_id, tag_id) comment 'for looking up list of top tags when content and user is null',
  INDEX is_top_tag_idx ON wp_display_unit_user_content (is_top_tag DESC),

  CONSTRAINT display_unit_user_content_has_tag_id_fk
  FOREIGN KEY (tag_id) REFERENCES wp_interest_tags (id)
    ON UPDATE CASCADE ON DELETE CASCADE,

  CONSTRAINT display_unit_user_content_has_user_id_fk
  FOREIGN KEY (user_id) REFERENCES wp_users (id)
    ON UPDATE CASCADE ON DELETE CASCADE ,

  CONSTRAINT display_unit_user_content_has_content_id_fk
  FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id)
    ON UPDATE CASCADE ON DELETE CASCADE

)
  engine=InnoDB
;
-----------
CREATE INDEX idx_when_html_updated ON wp_display_unit_user_content (when_html_updated);

ALTER TABLE wp_fl_user_data_lookup ADD score bigint unsigned DEFAULT 0 NOT NULL;
ALTER TABLE wp_linguist_content ADD score bigint unsigned DEFAULT 0 NOT NULL;
ALTER TABLE wp_tags_cache_job ADD test_flag tinyint DEFAULT 0 NOT NULL;

ALTER TABLE wp_linguist_content_chapter
ADD CONSTRAINT wp_linguist_content_chapter_has_content_fk
FOREIGN KEY (linguist_content_id) REFERENCES wp_linguist_content (id)
ON UPDATE CASCADE ON DELETE CASCADE;

CREATE INDEX idx_job_type ON wp_tags_cache_job (job_id, type);

ALTER TABLE wp_linguist_content MODIFY user_id bigint unsigned NOT NULL;
ALTER TABLE wp_linguist_content
ADD CONSTRAINT wp_linguist_content_wp_users_ID_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE INDEX wp_linguist_content_score_index ON wp_linguist_content (score DESC);

CREATE INDEX score_idx ON wp_fl_user_data_lookup (score DESC);

ALTER TABLE wp_homepage_interest_per_id ADD when_html_updated DATETIME DEFAULT NULL NULL;
ALTER TABLE wp_homepage_interest_per_id ADD html_generated mediumtext DEFAULT NULL NULL;
CREATE INDEX idx_purchased_by ON wp_linguist_content (purchased_by);

----

CREATE INDEX idx_publish_type ON wp_linguist_content (publish_type);


CREATE INDEX idx_author_type_approved ON wp_comments (comment_author, comment_type, comment_approved);

CREATE INDEX idx_publish_type_purchased_by ON wp_linguist_content (publish_type, purchased_by);
CREATE INDEX idx_item_status_order_type ON wp_payment_history (item_id, payment_status, order_type);
ALTER TABLE wp_fl_job MODIFY author bigint(20) unsigned NOT NULL;



ALTER TABLE wp_fl_job
ADD CONSTRAINT wp_fl_job_author_has_wp_users_id
FOREIGN KEY (author) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_job
ADD CONSTRAINT wp_fl_job_linguist_has_wp_users_id_fk
FOREIGN KEY (linguist_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_linguist_content ADD parent_content_id int DEFAULT NULL  NULL;
ALTER TABLE wp_linguist_content
  MODIFY COLUMN parent_content_id int DEFAULT NULL  AFTER id;


ALTER TABLE wp_linguist_content
ADD CONSTRAINT wp_linguist_content_has_own_parent_id_fk
FOREIGN KEY (parent_content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_content_files
ADD CONSTRAINT wp_content_files_has_content_id_fk
FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_content_files ADD public_file_name text NULL;

ALTER TABLE wp_linguist_content ADD max_to_be_sold int DEFAULT 1 NOT NULL;
ALTER TABLE wp_linguist_content
  MODIFY COLUMN max_to_be_sold int NOT NULL DEFAULT 1 AFTER user_id;

ALTER TABLE wp_linguist_content MODIFY usage_type enum('deprecated', 'Unlimited') NOT NULL DEFAULT 'deprecated';
CREATE INDEX idx_user_id_comment_type_comment_approved ON wp_comments (user_id, comment_type, comment_approved);

ALTER TABLE wp_proposals
ADD CONSTRAINT wp_proposals_by_user_has_wp_users_ID_fk
FOREIGN KEY (by_user) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

-- comment indexes !!
DROP INDEX idx_author_type_approved_id ON wp_comments;

DROP INDEX comment_approved_date_gmt ON wp_comments;
CREATE INDEX comment_approved_date_gmt ON wp_comments (comment_approved(1), comment_date_gmt);

DROP INDEX idx_author_type_approved ON wp_comments;
CREATE INDEX idx_author_type_approved ON wp_comments (comment_author(10), comment_type(10), comment_approved(1));

DROP INDEX idx_user_id_comment_type_comment_approved ON wp_comments;
CREATE INDEX idx_user_id_comment_type_comment_approved ON wp_comments (user_id, comment_type(10), comment_approved(1));


ALTER TABLE wp_fl_post_data_lookup ADD job_title text NULL;
ALTER TABLE wp_fl_post_data_lookup ADD job_description mediumtext NULL;
 */

/*
PROCEDURES:
      PROC manage_top_list(id_thing,type_of_id [user|content],current_score)
        one place to call all the logic of maintaining the wp_display_unit_user_content
        IF current_score or id_thing null then do nothing
        the caller will already know the score, so no need to do an extra select to look that up,pass in as param
            -- due to sql cursor limitations, this top procedure will call the correct procedure below,
                but both follow the same logic)

        PROCS manage_top_list_for_users(id_thing,current_score) and manage_top_list_for_content(id_thing,current_score)
        -------------------------------------------


        -- find limit-per-tag its under wp_options freelinguist-limit-top-per-tag, default 10 if missing

        -- cursor (tag_cursor) for all the tags in the thing select to get the following info for each tag the thing has
                     which also is in common with the list top tags:
                1) number of things (count_things_per_tag)
                2) lowest live score (lowest_live_score_per_tag) (null if nothing in the tag for category (user or content))
                3) ID of thing with the lowest score thing_id_with_lowest_score(null if nothing in the tag for for category (user or content))
                4) 1 or 0 whether this thing is already in the tag


        FOR EACH TAG (tag_cursor):
        -- is user or content already in the list for the tag ? if so do nothing



        -- ELSE if not in the tag
            IF  count_things_per_tag is less than limit-per-tag go ahead
                     add this to the wp_display_unit_user_content
            ELSE IF current_score >  lowest_live_score_per_tag
                    delete thing_id_with_lowest_score from wp_display_unit_user_content
                    add id_thing



TRIGGERS:
on wp_fl_post_user_lookup
    INSERT AFTER
        -- IF new last_login_time IS NOT NULL AND score IS NULL
                #update score
                New.score =  (NEW.rating_as_freelancer + 1) * UNIX_TIMESTAMP(last_login_time);

        -- IF New.score IS NOT NULL
                CALL manage_top_list(NEW.user_id,'user',NEW.score)


    UPDATE AFTER
        -- IF   NEW.last_login_time IS NOT NULL AND
               ((NEW.last_login_time > OLD.last_login_time) OR OLD.last_login_time IS NULL )AND
                score IS NULL
                #update score
                NEW.score =  (NEW.rating_as_freelancer + 1) * UNIX_TIMESTAMP(last_login_time);

        -- IF NEW.score IS NOT NULL AND
                CALL manage_top_list(NEW.user_id,'user',NEW.score)

     (no delete trigger needed as user in the wp_display_unit_user_content will be removed via FK


wp_linguist_content

    INSERT AFTER
        # update score when created
         rating = (NEW.rating_by_customer IS NULL ? 0 : NEW.rating_by_customer
         update score
            New.score =  (rating + 1) * UNIX_TIMESTAMP(NOW());

        -- IF New.score IS NOT NULL
                CALL manage_top_list(NEW.user_id,'content',NEW.score)


    UPDATE AFTER
         # update score each time data is changed here, unless score set somewhere else
        -- IF New.score IS NULL OR
           ((NEW.score = OLD.score) OR OLD.score IS NULL )
                rating = (NEW.rating_by_customer IS NULL ? 0 : NEW.rating_by_customer
                 #update score
                 New.score =  (rating + 1) * UNIX_TIMESTAMP(NOW());

        # always
        CALL manage_top_list(NEW.user_id,'content',NEW.score)



wp_linguist_content_chapter

    INSERT AFTER
        # get score and rating of its parent, select into parent_score and parent_rating
        # if parent_rating NULL then parent_rating = 0
        parent_score =  (rating + 1) * UNIX_TIMESTAMP(NOW());

        # UPDATE parent_score to parent
        UPDATE wp_linguist_content SET score = parent_score WHERE id = NEW.linguist_content_id


    UPDATE AFTER
        # same as this tables insert trigger above
        # get score and rating of its parent, select into parent_score and parent_rating
        # if parent_rating NULL then parent_rating = 0
        parent_score =  (rating + 1) * UNIX_TIMESTAMP(NOW());

        # UPDATE parent_score to parent
        UPDATE wp_linguist_content SET score = parent_score WHERE id = NEW.linguist_content_id

 */