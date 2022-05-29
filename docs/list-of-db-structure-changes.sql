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
# noinspection SqlResolve
DROP INDEX idx_author_type_approved_id ON wp_comments;

DROP INDEX comment_approved_date_gmt ON wp_comments;
CREATE INDEX comment_approved_date_gmt ON wp_comments (comment_approved(1), comment_date_gmt);

DROP INDEX idx_author_type_approved ON wp_comments;
CREATE INDEX idx_author_type_approved ON wp_comments (comment_author(10), comment_type(10), comment_approved(1));

DROP INDEX idx_user_id_comment_type_comment_approved ON wp_comments;
CREATE INDEX idx_user_id_comment_type_comment_approved ON wp_comments (user_id, comment_type(10), comment_approved(1));


ALTER TABLE wp_fl_post_data_lookup ADD job_title text NULL;
ALTER TABLE wp_fl_post_data_lookup ADD job_description mediumtext NULL;

ALTER TABLE wp_interest_tags ADD usage_count int DEFAULT 0 NOT NULL;

ALTER TABLE wp_fl_user_data_lookup MODIFY last_update timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE wp_linguist_content MODIFY updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE wp_linguist_content_chapter MODIFY updated_at datetime NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP;

CREATE FULLTEXT INDEX idx_fulltext_html_generated ON wp_homepage_interest_per_id(html_generated);
CREATE FULLTEXT INDEX idx_fulltext_html_generated ON wp_display_unit_user_content(html_generated);

ALTER TABLE wp_fl_user_data_lookup ADD user_hourly_rate int DEFAULT 0 NOT NULL;


ALTER TABLE wp_proposals ADD proposal_description text NULL;

ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_wp_proposals_id_fk
FOREIGN KEY (proposal_id) REFERENCES wp_proposals (id) ON UPDATE CASCADE;


ALTER TABLE wp_fl_transaction MODIFY user_id bigint unsigned;
ALTER TABLE wp_fl_transaction MODIFY user_id_added_by bigint unsigned;
ALTER TABLE wp_fl_transaction MODIFY project_id bigint unsigned NOT NULL DEFAULT 0;
ALTER TABLE wp_fl_transaction MODIFY job_id bigint NOT NULL DEFAULT 0;
ALTER TABLE wp_fl_transaction MODIFY milestone_id bigint NOT NULL DEFAULT 0;

CREATE INDEX wp_fl_transaction_project_id_index ON wp_fl_transaction (project_id);
CREATE INDEX wp_fl_transaction_user_id_index ON wp_fl_transaction (user_id);
CREATE INDEX wp_fl_transaction_job_id_index ON wp_fl_transaction (job_id);
ALTER TABLE wp_fl_transaction MODIFY type varchar(42);
CREATE INDEX wp_fl_transaction_type_index ON wp_fl_transaction (type);
CREATE INDEX wp_fl_transaction_user_type_project_index ON wp_fl_transaction (user_id, type, project_id);
-- index on user_id,type,project_id

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_wp_users_ID_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

SET SQL_MODE='ALLOW_INVALID_DATES';
CREATE INDEX `idx_post_title` ON wp_posts (post_title(20));

-- old datetime default is '0000-00-00 00:00:00'
ALTER TABLE wp_posts MODIFY COLUMN post_date DATETIME NOT
NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wp_posts MODIFY COLUMN post_date_gmt DATETIME NOT
NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wp_posts MODIFY COLUMN post_modified DATETIME NOT
NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wp_posts MODIFY COLUMN post_modified_gmt DATETIME NOT
NULL DEFAULT CURRENT_TIMESTAMP;



ALTER TABLE wp_fl_user_data_lookup ADD jobs_worked_completed int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD jobs_worked int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD projects_created int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD projects_hiring int DEFAULT 0 NULL;


ALTER TABLE wp_fl_user_data_lookup ADD contests_awarding int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD contests_created int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD contests_entered int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD contests_won int DEFAULT 0 NULL;

ALTER TABLE wp_fl_user_data_lookup ADD content_purchased int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD content_sold int DEFAULT 0 NULL;
ALTER TABLE wp_fl_user_data_lookup ADD content_created int DEFAULT 0 NULL;


ALTER TABLE wp_fl_milestones MODIFY project_id bigint unsigned NOT NULL;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT fk_wp_fl_milestones_project_has_post_id
FOREIGN KEY (project_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones MODIFY author bigint unsigned NOT NULL;
ALTER TABLE wp_fl_milestones MODIFY linguist_id bigint unsigned NOT NULL;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT fk_wp_fl_milestones_freelancer_has_user_id
FOREIGN KEY (linguist_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT fk_wp_fl_milestones_author_has_user_id
FOREIGN KEY (author) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_transaction ADD content_id int DEFAULT NULL;

ALTER TABLE wp_homepage_interest
  ADD CONSTRAINT wp_homepage_interest_has_interest_tags_fk
FOREIGN KEY (tag_id) REFERENCES wp_interest_tags (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_linguist_content MODIFY purchased_at datetime DEFAULT NULL;

ALTER TABLE wp_linguist_content ADD requested_completion_at datetime DEFAULT NULL ;

ALTER TABLE wp_proposals MODIFY job_id bigint  DEFAULT NULL COMMENT 'single job id';
ALTER TABLE wp_proposals MODIFY customer bigint unsigned DEFAULT NULL;
ALTER TABLE wp_proposals MODIFY post_id bigint(20) unsigned DEFAULT NULL COMMENT 'Project_id';

ALTER TABLE wp_proposals
  ADD CONSTRAINT wp_proposals_post_id_has_wp_posts_fk
FOREIGN KEY (post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_proposals
  ADD CONSTRAINT wp_proposals_customer_has_wp_users_id_fk
FOREIGN KEY (customer) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_job MODIFY bid_id bigint(20) unsigned NOT NULL;

ALTER TABLE wp_fl_job
  ADD CONSTRAINT wp_fl_job_has_bid_as_comment_fk
FOREIGN KEY (bid_id) REFERENCES wp_comments (comment_ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_job MODIFY project_id bigint(20) unsigned NOT NULL;

ALTER TABLE wp_fl_job
  ADD CONSTRAINT wp_fl_job_project_has_post_id_fk
FOREIGN KEY (project_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones MODIFY post_date datetime;
ALTER TABLE wp_fl_milestones MODIFY completed_at datetime DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT wp_fl_milestones_wp_fl_job_ID_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones MODIFY bid_id bigint(20) unsigned NOT NULL;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT wp_fl_milestones_has_comment_bid_fk
FOREIGN KEY (bid_id) REFERENCES wp_comments (comment_ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_discussion MODIFY post_by bigint(20) unsigned NOT NULL;
ALTER TABLE wp_fl_discussion MODIFY post_to bigint(20) unsigned DEFAULT NULL;
ALTER TABLE wp_fl_discussion MODIFY post_id bigint(20) UNSIGNED DEFAULT NULL ;
ALTER TABLE wp_fl_discussion MODIFY job_id bigint(20) DEFAULT NULL;
ALTER TABLE wp_fl_discussion MODIFY content_id int(11) DEFAULT NULL;
ALTER TABLE wp_fl_discussion ALTER COLUMN job_id SET DEFAULT NULL ;
ALTER TABLE wp_fl_discussion MODIFY time datetime  DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE wp_fl_discussion MODIFY parent_comment bigint DEFAULT NULL;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_post_by_has_user_fk
FOREIGN KEY (post_by) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_post_to_has_user_fk
FOREIGN KEY (post_to) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_post_id_has_post_fk
FOREIGN KEY (post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_job_id_has_job_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_has_content_fk
FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_discussion
  ADD CONSTRAINT wp_fl_discussion_has_parent_self_fk
FOREIGN KEY (parent_comment) REFERENCES wp_fl_discussion (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_transaction MODIFY time datetime  DEFAULT CURRENT_TIMESTAMP;

CREATE INDEX wp_fl_discussion_post_id_post_to_index ON wp_fl_discussion (post_id, post_to);

CREATE INDEX wp_fl_discussion_post_id_post_by_post_to_index ON wp_fl_discussion (post_id, post_by, post_to);

CREATE INDEX wp_fl_discussion_content_id_post_by_post_to_index ON wp_fl_discussion (content_id, post_by, post_to);

CREATE INDEX wp_fl_discussion_time_index ON wp_fl_discussion (time DESC);

# to allow content to be created before the cover image file is processed
ALTER TABLE wp_linguist_content MODIFY content_cover_image varchar(255) DEFAULT NULL;


ALTER TABLE wp_files MODIFY last_downloaded_time datetime DEFAULT NULL;
ALTER TABLE wp_files MODIFY by_user BIGINT UNSIGNED NOT NULL;

ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_user_fk
FOREIGN KEY (by_user) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_files MODIFY job_id int(11) DEFAULT NULL COMMENT 'single job id';
UPDATE wp_files set job_id = NULL WHERE job_id = 0;
ALTER TABLE wp_files MODIFY job_id bigint DEFAULT NULL COMMENT 'single job id';

ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_job_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_content_files ADD last_downloaded_time datetime DEFAULT NULL ;

ALTER TABLE wp_files MODIFY post_id bigint unsigned DEFAULT '0' COMMENT 'Project_id';
ALTER TABLE wp_files ALTER COLUMN post_id SET DEFAULT NULL;
ALTER TABLE wp_files MODIFY temp_id bigint unsigned;

--  block delete because should not delete posts without removing files first
ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_post_fk
FOREIGN KEY (post_id) REFERENCES wp_posts (ID) ON UPDATE CASCADE;

ALTER TABLE wp_content_files MODIFY user_id bigint unsigned DEFAULT null;


ALTER TABLE wp_content_files
  ADD CONSTRAINT wp_content_files_has_user_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


CREATE TABLE wp_fl_red_dots
(
  id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  is_red_dot tinyint DEFAULT 0 NOT NULL COMMENT '0 for no, 1 for yes, -1 for read, default 0 , never null',
  is_future_action tinyint DEFAULT 0 NOT NULL COMMENT '0 for no, 1 for yes, -1 for done , default 0 , never null',
  event_user_id_role tinyint DEFAULT 0 NOT NULL COMMENT 'to not cross events when logged in under other role , default 0, 1 for freelancer, 2 for customer',
  event_user_id bigint unsigned NOT NULL COMMENT 'this is for whom the event is for, never null',
  contest_id bigint unsigned DEFAULT NULL COMMENT 'if about a contest, put it here, else null',
  project_id bigint unsigned DEFAULT NULL COMMENT 'if about a project, put it here, else null',
  content_id int DEFAULT NULL COMMENT 'if about a content, put it here, else null',
  milestone_id bigint DEFAULT NULL COMMENT 'if about a milestone, put it here, else null',
  job_id bigint DEFAULT NULL COMMENT 'if about a project job , put it here, else null',
  proposal_id int DEFAULT NULL COMMENT 'if about a proposal, put it here, else null',
  other_user_id bigint unsigned DEFAULT null COMMENT 'if another user ,not tracked in the other ids, else null',
  discussion_id bigint DEFAULT NULL COMMENT 'if about a discussion, put it here, else null',
  event_timestamp datetime NOT NULL COMMENT 'when did this event happen ? never null',
  future_timestamp datetime DEFAULT NULL COMMENT 'if this is a future job, put the time here, else null',
  event_name varchar(20) NOT NULL COMMENT 'event names should be small, clear and unambiguous'
) ENGINE=InnoDB CHARACTER SET utf8mb4;

CREATE INDEX wp_fl_red_dots_is_red_dot_index ON wp_fl_red_dots (is_red_dot);

CREATE INDEX wp_fl_red_dots_is_future_action_index ON wp_fl_red_dots (is_future_action);


CREATE INDEX wp_fl_red_dots_event_timestamp_index ON wp_fl_red_dots (event_timestamp);

CREATE INDEX wp_fl_red_dots_future_timestamp_index ON wp_fl_red_dots (future_timestamp);

CREATE INDEX wp_fl_red_dots_event_name_index ON wp_fl_red_dots (event_name);

CREATE INDEX wp_fl_red_dots_event_user_id_role_index ON wp_fl_red_dots (event_user_id_role);




ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_contest_post_fk
FOREIGN KEY (contest_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_project_post_fk
FOREIGN KEY (project_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_content_fk
FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_milestone_fk
FOREIGN KEY (milestone_id) REFERENCES wp_fl_milestones (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_job_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_proposal_fk
FOREIGN KEY (proposal_id) REFERENCES wp_proposals (id) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_discussion_fk
FOREIGN KEY (discussion_id) REFERENCES wp_fl_discussion (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_event_user_fk
FOREIGN KEY (event_user_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_red_dots
  ADD CONSTRAINT wp_fl_red_dots_has_other_user_fk
FOREIGN KEY (other_user_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_milestones MODIFY rejection_requested tinyint NOT NULL DEFAULT 0;
ALTER TABLE wp_fl_milestones MODIFY completion_requested tinyint NOT NULL DEFAULT 0;

ALTER TABLE wp_linguist_content MODIFY rejection_requested tinyint NOT NULL DEFAULT 0;
ALTER TABLE wp_linguist_content MODIFY freezed tinyint NOT NULL DEFAULT 0;

ALTER TABLE wp_proposals MODIFY rejection_requested tinyint NOT NULL DEFAULT 0;

ALTER TABLE wp_linguist_content MODIFY purchased_by bigint(20) unsigned DEFAULT NULL ;
#needs
update wp_linguist_content SET purchased_by = NULL where purchased_by = 0;
#then
ALTER TABLE wp_linguist_content
  ADD CONSTRAINT wp_linguist_content_purchased_by_has_user_fk
FOREIGN KEY (purchased_by) REFERENCES wp_users (ID);



ALTER TABLE wp_fl_red_dots MODIFY event_timestamp datetime  DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE wp_fl_chat_logs
(
  id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  user_id bigint unsigned NOT NULL,
  is_being_sent_to_page tinyint DEFAULT 0 NOT NULL,
  chat_user_text_id varchar(60) DEFAULT NULL,
  chat_room_text_id varchar(60) DEFAULT NULL,
  xml_source varchar(20) DEFAULT NULL,
  xml_text longtext DEFAULT null
);


ALTER TABLE wp_linguist_content ADD purchase_amount double DEFAULT NULL NULL;
ALTER TABLE wp_linguist_content
  MODIFY COLUMN purchase_amount double DEFAULT NULL AFTER created_at;

ALTER TABLE wp_fl_red_dots ADD future_action_done_at datetime DEFAULT null NULL COMMENT 'If this future action was completed, then this is the time it was completed at';
ALTER TABLE wp_fl_red_dots
  MODIFY COLUMN event_name varchar(30) NOT NULL COMMENT 'event names should be small, clear and unambiguous' AFTER future_action_done_at;


ALTER TABLE wp_fl_user_data_lookup ADD total_user_balance float DEFAULT 0.0 NOT NULL;
ALTER TABLE wp_fl_user_data_lookup
  MODIFY COLUMN total_user_balance float NOT NULL DEFAULT 0.0 AFTER last_login_time;

-- code-notes  after syncing table need to update all the text values of the balance without changing them to the new column above will be populated for all users:
update wp_usermeta SET meta_value = CONCAT(meta_value,'') WHERE meta_key = 'total_user_balance' ;

ALTER TABLE wp_fl_transaction MODIFY amount decimal(15,2);

ALTER TABLE wp_message_history MODIFY milestone_id bigint DEFAULT NULL ;
ALTER TABLE wp_message_history MODIFY proposal_id int(11) DEFAULT NULL ;
ALTER TABLE wp_message_history MODIFY content_id int(11) DEFAULT NULL ;
ALTER TABLE wp_message_history MODIFY customer bigint unsigned DEFAULT NULL ;
ALTER TABLE wp_message_history MODIFY freelancer bigint unsigned DEFAULT NULL ;
ALTER TABLE wp_message_history MODIFY added_by bigint unsigned DEFAULT null ;
-- code-notes  need to run the following
update wp_message_history set milestone_id = NULL where milestone_id = 0;
update wp_message_history set proposal_id = NULL where proposal_id = 0;
update wp_message_history set content_id = NULL where content_id = 0;
update wp_message_history set customer = NULL where customer = 0;
update wp_message_history set freelancer = NULL where freelancer = 0;
update wp_message_history set added_by = NULL where added_by = 0;


DELETE h
FROM wp_message_history h
  INNER JOIN (
               select h.id as history_id
               FROM  wp_message_history h
                 LEFT JOIN wp_proposals p ON p.id = h.proposal_id
                 LEFT JOIN wp_fl_milestones m ON m.ID = h.milestone_id
                 LEFT JOIN wp_linguist_content c ON c.id = h.content_id
                 LEFT JOIN wp_users u ON u.ID = h.added_by
               WHERE
                 (h.proposal_id IS NOT NULL AND p.id IS NULL) OR
                 (h.milestone_id IS NOT NULL AND m.ID IS NULL) OR
                 (h.content_id IS NOT NULL AND c.id IS NULL) OR
                 (h.added_by IS NOT NULL AND u.ID is null)
             )  bb ON bb.history_id = h.id
;

ALTER TABLE wp_message_history ENGINE = InnoDB;

ALTER TABLE wp_message_history
  ADD CONSTRAINT wp_message_history_wp_fl_milestones_ID_fk
FOREIGN KEY (milestone_id) REFERENCES wp_fl_milestones (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_message_history
  ADD CONSTRAINT wp_message_history_wp_proposals_id_fk
FOREIGN KEY (proposal_id) REFERENCES wp_proposals (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_message_history
  ADD CONSTRAINT wp_message_history_wp_linguist_content_id_fk
FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_message_history
  ADD CONSTRAINT wp_message_history_added_has_user_fk
FOREIGN KEY (added_by) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE INDEX idx_post_flag_val ON wp_fl_post_user_lookup (post_id, lookup_flag, lookup_val); -- to quickly look up if proposal as awarded



ALTER TABLE wp_payment_history MODIFY user_id bigint unsigned;
CREATE INDEX wp_payment_history_user_id_index ON wp_payment_history (user_id);

ALTER TABLE wp_payment_history
  ADD CONSTRAINT wp_payment_history_wp_users_ID_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


CREATE TABLE wp_fl_broadcast_messages
(
  id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
  sender_user_id bigint unsigned NOT NULL,
  created_at timestamp DEFAULT current_timestamp NOT NULL,
  broadcast_message mediumtext
);
ALTER TABLE wp_fl_broadcast_messages ENGINE = InnoDB;
CREATE INDEX wp_fl_broadcast_messages_sender_user_id_index ON wp_fl_broadcast_messages (sender_user_id);

ALTER TABLE wp_fl_broadcast_messages
  ADD CONSTRAINT wp_fl_broadcast_messages_wp_users_ID_fk
FOREIGN KEY (sender_user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_payment_history MODIFY refill_by bigint unsigned DEFAULT NULL;
UPDATE wp_payment_history SET refill_by = NULL where refill_by = 0;
-- code-notes  might have to run this on target db if rows filled in
ALTER TABLE wp_payment_history MODIFY refill_by bigint unsigned DEFAULT NULL;
UPDATE wp_payment_history SET refill_by = NULL where refill_by = 0;

ALTER TABLE wp_payment_history
  ADD CONSTRAINT wp_payment_history_refill_by_has_user_fk
FOREIGN KEY (refill_by) REFERENCES wp_users (ID);


ALTER TABLE wp_dispute_cases MODIFY linguist_id bigint unsigned DEFAULT NULL;
ALTER TABLE wp_dispute_cases MODIFY posted_by bigint(20) unsigned DEFAULT NULL;
ALTER TABLE wp_dispute_cases MODIFY content_id int(11) DEFAULT NULL;
ALTER TABLE wp_dispute_cases MODIFY milestone_id bigint DEFAULT NULL;
ALTER TABLE wp_dispute_cases MODIFY customer_id bigint unsigned DEFAULT null;
ALTER TABLE wp_dispute_cases MODIFY contestId bigint unsigned DEFAULT NULL;

alter table wp_payment_history drop column item_id;

alter table wp_payment_history drop column order_type;
ALTER TABLE wp_payment_history MODIFY created_time datetime  DEFAULT CURRENT_TIMESTAMP NOT NULL;


ALTER TABLE wp_payment_history ADD transaction_post_id bigint unsigned DEFAULT NULL NULL;
ALTER TABLE wp_payment_history
  MODIFY COLUMN user_id bigint(20) unsigned AFTER id,
  MODIFY COLUMN refill_by bigint(20) unsigned AFTER user_id,
  MODIFY COLUMN transaction_post_id bigint unsigned DEFAULT NULL AFTER refill_by,
  MODIFY COLUMN created_time datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER transaction_post_id,
  MODIFY COLUMN txn_id varchar(50) NOT NULL AFTER payment_amount;

ALTER TABLE wp_payment_history
  ADD CONSTRAINT wp_payment_history_wp_posts_ID_fk
FOREIGN KEY (transaction_post_id) REFERENCES wp_posts (ID);

CREATE INDEX wp_payment_history_txn_id_index ON wp_payment_history (txn_id);

ALTER TABLE wp_payment_history MODIFY item_name varchar(180);



create table wp_payment_history_ipn
(
  id int auto_increment
    primary key,
  payment_id int not null,
  other_transaction_id bigint unsigned DEFAULT NULL,
  amount decimal(10,2)  null,
  create_date datetime default CURRENT_TIMESTAMP not null,
  payment_date datetime null,
  country char(2) null,
  currency char(3) null,
  fl_payment_status varchar(10) null,
  txn_id varchar(50) not null,
  txn_type varchar(50)  null,
  payment_status varchar(50) null,
  payment_method varchar(50) null,
  item_name varchar(50) null,
  item_number varchar(50) null,
  receiver_email varchar(50) null,
  payer_email varchar(150) null,
  first_name varchar(150) null,
  last_name varchar(150) null,
  original_data json null,


  constraint wp_payment_history_ipn_wp_payment_history_id_fk
  foreign key (payment_id) references wp_payment_history (id)
)
  engine=InnoDB charset=utf8mb4
;

create index wp_payment_history_ipn_wp_payment_history_id_fk
  on wp_payment_history_ipn (payment_id)
;

ALTER TABLE wp_payment_history_ipn
  ADD CONSTRAINT wp_payment_history_ipn_wp_posts_ID_fk
FOREIGN KEY (other_transaction_id) REFERENCES wp_posts (ID);

ALTER TABLE wp_payment_history ADD currency char(3) DEFAULT 'USD' NULL;
ALTER TABLE wp_payment_history
  MODIFY COLUMN currency char(3) DEFAULT 'USD' AFTER payment_amount;

ALTER TABLE wp_payment_history ADD processing_fee_included decimal(10,2) DEFAULT NULL NULL;
ALTER TABLE wp_payment_history
  MODIFY COLUMN processing_fee_included decimal(10,2) DEFAULT NULL AFTER payment_amount;

ALTER TABLE wp_payment_history_ipn ADD error_msg text DEFAULT  NULL;

ALTER TABLE wp_payment_history_ipn MODIFY original_data mediumtext;

CREATE INDEX wp_payment_history_ipn_txn_id_index ON wp_payment_history_ipn (txn_id);


ALTER TABLE wp_payment_history_ipn ADD is_duplicate tinyint DEFAULT 0 NOT NULL;
ALTER TABLE wp_payment_history_ipn
  MODIFY COLUMN is_duplicate tinyint NOT NULL DEFAULT 0 AFTER payment_date;


alter table wp_linguist_content drop column content_pdf_file;

# code-notes if a user, project, job is deleted, without removing files first (ie outside the code) do not remove their files, they still exist in the db and disk
ALTER TABLE wp_content_files DROP FOREIGN KEY wp_content_files_has_user_fk;
ALTER TABLE wp_content_files
  ADD CONSTRAINT wp_content_files_has_user_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE wp_files MODIFY by_user bigint(20) unsigned;

ALTER TABLE wp_files DROP FOREIGN KEY wp_files_has_user_fk;
ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_user_fk
FOREIGN KEY (by_user) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_files DROP FOREIGN KEY wp_files_wp_proposals_id_fk;
ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_wp_proposals_id_fk
FOREIGN KEY (proposal_id) REFERENCES wp_proposals (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_files DROP FOREIGN KEY wp_files_has_job_fk;
ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_job_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_files DROP FOREIGN KEY wp_files_has_post_fk;
ALTER TABLE wp_files
  ADD CONSTRAINT wp_files_has_post_fk
FOREIGN KEY (post_id) REFERENCES wp_posts (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_linguist_content DROP FOREIGN KEY wp_linguist_content_purchased_by_has_user_fk;
ALTER TABLE wp_linguist_content
  ADD CONSTRAINT wp_linguist_content_purchased_by_has_user_fk
FOREIGN KEY (purchased_by) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_payment_history DROP FOREIGN KEY wp_payment_history_refill_by_has_user_fk;
ALTER TABLE wp_payment_history
  ADD CONSTRAINT wp_payment_history_refill_by_has_user_fk
FOREIGN KEY (refill_by) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_payment_history DROP FOREIGN KEY wp_payment_history_wp_posts_ID_fk;
ALTER TABLE wp_payment_history
  ADD CONSTRAINT wp_payment_history_wp_posts_ID_fk
FOREIGN KEY (transaction_post_id) REFERENCES wp_posts (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_payment_history_ipn DROP FOREIGN KEY wp_payment_history_ipn_wp_posts_ID_fk;
ALTER TABLE wp_payment_history_ipn
  ADD CONSTRAINT wp_payment_history_ipn_wp_posts_ID_fk
FOREIGN KEY (other_transaction_id) REFERENCES wp_posts (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_linguist_content DROP FOREIGN KEY wp_linguist_content_wp_users_ID_fk;
ALTER TABLE wp_linguist_content MODIFY user_id bigint(20) unsigned;
ALTER TABLE wp_linguist_content
  ADD CONSTRAINT wp_linguist_content_wp_users_ID_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_fl_chat_rooms MODIFY freelancer_id BIGINT unsigned DEFAULT NULL;
ALTER TABLE wp_fl_chat_rooms MODIFY employer_id BIGINT UNSIGNED DEFAULT NULL;

ALTER TABLE wp_fl_chat_rooms ENGINE = InnoDB;

ALTER TABLE wp_fl_chat_rooms
  ADD CONSTRAINT wp_fl_chat_rooms_wp_users_ID_fk
FOREIGN KEY (freelancer_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_chat_rooms
  ADD CONSTRAINT wp_fl_chat_rooms_employer_has_user_fk
FOREIGN KEY (employer_id) REFERENCES wp_users (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_user_added_has_user_fk
FOREIGN KEY (user_id_added_by) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_fl_transaction ALTER COLUMN user_id SET DEFAULT NULL;
ALTER TABLE wp_fl_transaction ALTER COLUMN user_id_added_by SET DEFAULT NULL;

ALTER TABLE wp_fl_transaction MODIFY project_id bigint(20) unsigned DEFAULT NULL;
ALTER TABLE wp_fl_transaction MODIFY job_id bigint(20) DEFAULT NULL ;
ALTER TABLE wp_fl_transaction MODIFY milestone_id bigint(20) DEFAULT NULL ;

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_wp_fl_milestones_ID_fk
FOREIGN KEY (milestone_id) REFERENCES wp_fl_milestones (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_project_id_has_post_fk
FOREIGN KEY (project_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_job_id_has_post_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE wp_proposals MODIFY by_user bigint(20) unsigned;

ALTER TABLE wp_proposals DROP FOREIGN KEY wp_proposals_by_user_has_wp_users_ID_fk;
ALTER TABLE wp_proposals
  ADD CONSTRAINT wp_proposals_by_user_has_wp_users_ID_fk
FOREIGN KEY (by_user) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_proposals DROP FOREIGN KEY wp_proposals_customer_has_wp_users_id_fk;
ALTER TABLE wp_proposals
  ADD CONSTRAINT wp_proposals_customer_has_wp_users_id_fk
FOREIGN KEY (customer) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_files
  MODIFY COLUMN type int(11) NOT NULL DEFAULT '1' COMMENT '1:translate_file, 2: Translated_file, 3: Profile Pic, 4: resume,5 job personal file' AFTER job_id;


ALTER TABLE wp_linguist_content DROP FOREIGN KEY wp_linguist_content_has_own_parent_id_fk;
ALTER TABLE wp_linguist_content
  ADD CONSTRAINT wp_linguist_content_has_own_parent_id_fk
FOREIGN KEY (parent_content_id) REFERENCES wp_linguist_content (id) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_fl_milestones MODIFY linguist_id bigint(20) unsigned;
ALTER TABLE wp_fl_milestones MODIFY bid_id bigint(20) unsigned;
ALTER TABLE wp_fl_milestones MODIFY author bigint(20) unsigned;

ALTER TABLE wp_fl_milestones DROP FOREIGN KEY fk_wp_fl_milestones_freelancer_has_user_id;
ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT fk_wp_fl_milestones_freelancer_has_user_id
FOREIGN KEY (linguist_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_fl_milestones DROP FOREIGN KEY wp_fl_milestones_has_comment_bid_fk;
ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT wp_fl_milestones_has_comment_bid_fk
FOREIGN KEY (bid_id) REFERENCES wp_comments (comment_ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones DROP FOREIGN KEY wp_fl_milestones_wp_fl_job_ID_fk;
ALTER TABLE wp_fl_milestones MODIFY job_id bigint(20);
ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT wp_fl_milestones_wp_fl_job_ID_fk
FOREIGN KEY (job_id) REFERENCES wp_fl_job (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_fl_milestones
  ADD CONSTRAINT wp_fl_milestones_author_has_user_fk
FOREIGN KEY (author) REFERENCES wp_users (ID) ON DELETE SET NULL;

ALTER TABLE wp_linguist_content_chapter MODIFY user_id bigint unsigned DEFAULT NULL;

ALTER TABLE wp_linguist_content_chapter
  ADD CONSTRAINT wp_linguist_content_chapter_has_user_id_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL;

ALTER TABLE wp_linguist_content_chapter
  MODIFY COLUMN created_at datetime NOT NULL AFTER linguist_content_id,
  MODIFY COLUMN updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER created_at,
  MODIFY COLUMN content_visible varchar(100) NOT NULL AFTER updated_at;

ALTER TABLE wp_linguist_content_chapter MODIFY content_visible varchar(5) DEFAULT NULL;

ALTER TABLE wp_dispute_cases MODIFY mediator_id bigint unsigned DEFAULT NULL COMMENT 'Mediator ID who handling this case';

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_linguist_has_user_fk
FOREIGN KEY (linguist_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_customer_has_user_fk
FOREIGN KEY (customer_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_mediator_has_user_fk
FOREIGN KEY (mediator_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_contest_has_post_fk
FOREIGN KEY (contestId) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases MODIFY proposal_id int(11) DEFAULT NULL;
ALTER TABLE wp_dispute_cases ALTER COLUMN posted_by SET DEFAULT NULL;
ALTER TABLE wp_dispute_cases ALTER COLUMN content_id SET DEFAULT NULL;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_milestone_has_fk
FOREIGN KEY (milestone_id) REFERENCES wp_fl_milestones (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_content_has_fk
FOREIGN KEY (content_id) REFERENCES wp_linguist_content (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_posted_by_has_user_fk
FOREIGN KEY (posted_by) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_dispute_cases
  ADD CONSTRAINT wp_dispute_cases_transaction_has_fk
FOREIGN KEY (transaction_id) REFERENCES wp_fl_transaction (ID) ON DELETE SET NULL ON UPDATE CASCADE;


# for making the transactions work with refill and admin page

ALTER TABLE wp_fl_transaction ADD transaction_post_id BIGINT unsigned DEFAULT NULL NULL;
ALTER TABLE wp_fl_transaction
  MODIFY COLUMN transaction_post_id BIGINT unsigned DEFAULT NULL AFTER content_id;

CREATE INDEX wp_fl_transaction_transaction_post_id_index ON wp_fl_transaction (transaction_post_id);

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_wp_posts_ID_fk
FOREIGN KEY (transaction_post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE INDEX wp_fl_transaction_txn_id_index ON wp_fl_transaction (txn_id);
CREATE INDEX wp_fl_transaction_amount_index ON wp_fl_transaction (amount);
CREATE INDEX wp_fl_transaction_time_index ON wp_fl_transaction (time);

# code-notes add proposal link to transactions March 5,2021
ALTER TABLE wp_fl_transaction ADD proposal_id int DEFAULT NULL NULL;
ALTER TABLE wp_fl_transaction
  MODIFY COLUMN proposal_id int DEFAULT NULL AFTER milestone_id;

CREATE INDEX wp_fl_transaction_proposal_id_index ON wp_fl_transaction (proposal_id);

ALTER TABLE wp_fl_transaction
  ADD CONSTRAINT wp_fl_transaction_proposals_has_fk
FOREIGN KEY (proposal_id) REFERENCES wp_proposals (id) ON DELETE CASCADE ON UPDATE CASCADE;

# code-notes transaction lookup table
ALTER TABLE wp_transaction_lookup ENGINE = InnoDB;
CREATE INDEX wp_transaction_lookup_post_id_index ON wp_transaction_lookup (post_id);

ALTER TABLE wp_transaction_lookup
  ADD CONSTRAINT wp_transaction_lookup_post_id_has_fk
FOREIGN KEY (post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_transaction_lookup
  ADD CONSTRAINT wp_transaction_lookup_related_post_id_has_fk
FOREIGN KEY (related_post_id) REFERENCES wp_posts (ID) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE wp_transaction_lookup MODIFY transaction_type tinyint NOT NULL DEFAULT '0' COMMENT '(meta is _transactionType) none=0|processing_fee=1|refill=2|refund=3|undo_processing_fee=4|withdraw=5|FREE_credits=6|FREE_credits_refund=7';
ALTER TABLE wp_transaction_lookup MODIFY payment_type tinyint NOT NULL DEFAULT '0' COMMENT '(meta is _payment_type) none=0|paypal=1|stripe=2';
ALTER TABLE wp_transaction_lookup MODIFY withdraw_status tinyint NOT NULL DEFAULT '0' COMMENT '(meta is _transactionWithdrawStatus) none=0|pending=1';
ALTER TABLE wp_transaction_lookup MODIFY request_payment_notify tinyint NOT NULL DEFAULT '0' COMMENT '(meta is request_payment_notify) none=0|paypal=1|stripe=2|alipay=3';

ALTER TABLE wp_transaction_lookup CHANGE txn_id txn varchar(30) COMMENT 'meta is _modified_transaction_id';
ALTER TABLE wp_transaction_lookup CHANGE related_txn_id related_txn varchar(30) COMMENT 'meta is _modified_transaction_id of related_post_id';
ALTER TABLE wp_transaction_lookup MODIFY numeric_modified_id bigint unsigned NOT NULL DEFAULT 0 COMMENT 'meta is numeric_modified_id';

DROP INDEX wp_transaction_lookup_post_id_index ON wp_transaction_lookup;
CREATE UNIQUE INDEX wp_transaction_lookup_post_id_udx ON wp_transaction_lookup (post_id);

create table wp_transaction_lookup_errors
(
  id int auto_increment
    primary key,
  transaction_lookup_id int not null,
  error_time timestamp default CURRENT_TIMESTAMP not null,
  severity int default '5' not null,
  column_of_error varchar(30) not null,
  error_msg text null,
  constraint unique_id_pk
  unique (id),
  constraint transaction_lookup_errors_has_lookup_fk
  foreign key (transaction_lookup_id) references wp_transaction_lookup (id)
    on update cascade on delete cascade
)
  engine=InnoDB
;

ALTER TABLE wp_transaction_lookup ADD user_id bigint unsigned DEFAULT NULL NULL;
ALTER TABLE wp_transaction_lookup
  MODIFY COLUMN user_id bigint unsigned DEFAULT NULL AFTER post_id,
  MODIFY COLUMN numeric_modified_id bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'meta is numeric_modified_id' AFTER related_post_id;

ALTER TABLE wp_transaction_lookup
  ADD CONSTRAINT wp_transaction_lookup_use_has_fk
FOREIGN KEY (user_id) REFERENCES wp_users (ID) ON DELETE SET NULL ON UPDATE CASCADE;

# code-notes the below fill in the transaction lookup table (after hanging post ids in the meta keys were removed)
UPDATE wp_postmeta mark
  INNER JOIN (
               SELECT p.ID , m.meta_key, m.meta_id
               FROM wp_posts p
                 LEFT JOIN   wp_postmeta m ON m.post_id = p.ID
               WHERE p.post_type = 'wallet' AND m.meta_value IS NOT NULL
               ORDER BY p.ID,m.meta_key) as dat_stuff ON dat_stuff.meta_id = mark.meta_id
SET mark.meta_value = CONCAT(mark.meta_value,'');

select p.post_author , p.ID, u.ID as checked_user_id from wp_posts p
  LEFT JOIN wp_users u ON u.ID = p.post_author
WHERE post_type = 'wallet';


update wp_posts p SET p.post_author = p.post_author WHERE p.post_type = 'wallet';
update wp_posts set post_date = post_date WHERE post_type = 'wallet'; -- run after the section below is completed
# code-notes end update sql

#code-notes more transaction lookup
CREATE INDEX wp_transaction_lookup_user_id_modified_at_index ON wp_transaction_lookup (user_id, modified_at DESC);

ALTER TABLE wp_transaction_lookup ADD post_status tinyint DEFAULT 0 NOT NULL;
ALTER TABLE wp_transaction_lookup
  MODIFY COLUMN post_status tinyint NOT NULL DEFAULT 0 AFTER request_payment_notify;

ALTER TABLE wp_transaction_lookup ADD post_created_at datetime DEFAULT NULL NULL;
ALTER TABLE wp_transaction_lookup
  MODIFY COLUMN post_created_at datetime DEFAULT NULL AFTER transaction_amount;

DROP INDEX wp_transaction_lookup_user_id_modified_at_index ON wp_transaction_lookup;
CREATE INDEX wp_transaction_lookup_user_id_post_created_at_index ON wp_transaction_lookup (user_id, post_created_at DESC);

#code-nodes speed up admin backend by a fraction of a second
CREATE INDEX comment_approved_idx ON wp_comments (comment_approved);

CREATE INDEX post_created_at_asx_idx ON wp_transaction_lookup (post_created_at);

#code-notes more transaction lookup
CREATE FULLTEXT INDEX withdraw_fulltext_idx ON wp_transaction_lookup(withdrawal_message);
ALTER TABLE wp_transaction_lookup ADD withdraw_approved_by bigint unsigned DEFAULT null  NULL;
ALTER TABLE wp_transaction_lookup
  MODIFY COLUMN withdraw_approved_by bigint unsigned DEFAULT null  AFTER related_post_id,
  MODIFY COLUMN user_id bigint(20) unsigned AFTER related_post_id;

ALTER TABLE wp_transaction_lookup
  ADD CONSTRAINT wp_transaction_lookup_withdraw_approved_by_has_user_fk
FOREIGN KEY (withdraw_approved_by) REFERENCES wp_users (ID);


ALTER TABLE wp_transaction_lookup ADD withdraw_cancel_message text DEFAULT NULL NULL;

# code-notes support for references and referrals
ALTER TABLE wp_fl_user_data_lookup ADD wp_fl_user_data_lookup_reference_code_index varchar(10) DEFAULT NULL NULL;
CREATE UNIQUE INDEX lookup_reference_code_udx ON wp_fl_user_data_lookup (reference_code);

#code-notes chat rooms changes

ALTER TABLE wp_fl_chat_rooms ADD is_active tinyint DEFAULT 1 NOT NULL;
ALTER TABLE wp_fl_chat_rooms
  MODIFY COLUMN room_title varchar(200) AFTER is_active,
  MODIFY COLUMN room_id varchar(50) AFTER is_active,
  MODIFY COLUMN employer_id bigint(20) unsigned AFTER project_type,
  MODIFY COLUMN freelancer_id bigint(20) unsigned AFTER project_type,
  MODIFY COLUMN is_active tinyint NOT NULL DEFAULT 1 AFTER project_type;


ALTER TABLE wp_fl_job ADD chat_room_id int unsigned DEFAULT NULL  NULL;
ALTER TABLE wp_fl_job
  MODIFY COLUMN chat_room_id int unsigned DEFAULT NULL  AFTER bid_id,
  MODIFY COLUMN rating_by_freelancer int(11) AFTER amount,
  MODIFY COLUMN rating_by_customer int(11) AFTER amount,
  MODIFY COLUMN updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER rating_by_freelancer,
  MODIFY COLUMN meta longtext NOT NULL AFTER job_status;

ALTER TABLE wp_fl_job
  ADD CONSTRAINT wp_fl_job_has_chat_fk
FOREIGN KEY (chat_room_id) REFERENCES wp_fl_chat_rooms (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_fl_job
  MODIFY COLUMN content longtext NOT NULL AFTER job_status,
  MODIFY COLUMN title text NOT NULL AFTER job_status;

ALTER TABLE wp_proposals ADD chat_room_id int unsigned DEFAULT NULL  NULL;
ALTER TABLE wp_proposals
  MODIFY COLUMN customer bigint(20) unsigned AFTER by_user,
  MODIFY COLUMN chat_room_id int(10) unsigned AFTER customer,
  MODIFY COLUMN revision_text text NOT NULL AFTER comments_by_freelancer,
  MODIFY COLUMN mediator_id int(11) NOT NULL AFTER customer,
  MODIFY COLUMN rating_by_customer int(11) AFTER updated_at,
  MODIFY COLUMN rating_by_freelancer int(11) AFTER rating_by_customer,
  MODIFY COLUMN rejection_accepted int(11) NOT NULL AFTER rating_by_freelancer,
  MODIFY COLUMN rating int(11) AFTER updated_at,
  MODIFY COLUMN rejection_txt text NOT NULL AFTER rejection_requested;


ALTER TABLE wp_proposals
  ADD CONSTRAINT wp_proposals_has_chat_rooms_fk
FOREIGN KEY (chat_room_id) REFERENCES wp_fl_chat_rooms (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE wp_linguist_content MODIFY content_cover_image text;
ALTER TABLE wp_linguist_content ADD chat_room_id int unsigned DEFAULT NULL NULL;
ALTER TABLE wp_linguist_content
  MODIFY COLUMN chat_room_id int unsigned DEFAULT NULL AFTER user_id,
  MODIFY COLUMN score bigint(20) unsigned NOT NULL DEFAULT '0' AFTER chat_room_id,
  MODIFY COLUMN purchased_by bigint(20) unsigned AFTER score,
  MODIFY COLUMN content_summary text AFTER requested_completion_at,
  MODIFY COLUMN content_title text AFTER requested_completion_at,
  MODIFY COLUMN content_type blob AFTER content_summary,
  MODIFY COLUMN files text COMMENT 'comma seprated' AFTER content_summary,
  MODIFY COLUMN description_image text NOT NULL AFTER content_summary,
  MODIFY COLUMN offersBy text NOT NULL AFTER content_type,
  MODIFY COLUMN rejection_txt text AFTER offersBy,
  MODIFY COLUMN revision_text text NOT NULL AFTER offersBy,
  MODIFY COLUMN content_cover_image text AFTER rating_by_freelancer,
  MODIFY COLUMN purchase_amount double AFTER max_to_be_sold,
  MODIFY COLUMN show_content int(1) NOT NULL DEFAULT '1' AFTER content_amount,
  MODIFY COLUMN requested_completion_at datetime AFTER freezed,
  MODIFY COLUMN status enum('pending', 'completed', 'request_revision', 'cancelled', 'rejected', 'hire_mediator', 'request_completion', 'request_rejection') NOT NULL DEFAULT 'pending' AFTER usage_type,
  MODIFY COLUMN purchased_at datetime AFTER freezed;

ALTER TABLE wp_linguist_content
  ADD CONSTRAINT wp_linguist_content_has_chat_fk
FOREIGN KEY (chat_room_id) REFERENCES wp_fl_chat_rooms (id) ON DELETE SET NULL ON UPDATE CASCADE;



# code-notes test data needs to be counted, and found, quickly

ALTER TABLE wp_fl_user_data_lookup ADD is_test_data tinyint DEFAULT 0 NOT NULL;
ALTER TABLE wp_fl_user_data_lookup
  MODIFY COLUMN is_test_data tinyint NOT NULL DEFAULT 0 AFTER has_user_image;


ALTER TABLE wp_fl_post_data_lookup ADD is_test_data tinyint DEFAULT 0 NOT NULL;
ALTER TABLE wp_fl_post_data_lookup
  MODIFY COLUMN is_test_data tinyint NOT NULL DEFAULT 0 AFTER is_cancellation_approved;


CREATE INDEX idx_post_id_is_test_data ON wp_fl_post_data_lookup (post_id, is_test_data);
CREATE INDEX idx_user_id_is_test_data ON wp_fl_user_data_lookup (user_id, is_test_data);

#code-notes ,  used the below to fill in the new columns, which are filled via the updated triggers for the lookups
UPDATE wp_postmeta mark
SET mark.meta_value = CONCAT(mark.meta_value,'')
WHERE mark.meta_key = 'create_batch';

UPDATE wp_usermeta mark
SET mark.meta_value = CONCAT(mark.meta_value,'')
WHERE mark.meta_key = 'create_batch';

#code-notes the content chapters need to organize. add integer and add compound index for fast sorting

ALTER TABLE wp_linguist_content_chapter ADD page_number int DEFAULT 0 NOT NULL;
ALTER TABLE wp_linguist_content_chapter
  MODIFY COLUMN page_number int NOT NULL DEFAULT 0 AFTER linguist_content_id;

CREATE INDEX idx_content_page_number ON wp_linguist_content_chapter (linguist_content_id, page_number);

ALTER TABLE wp_linguist_content_chapter MODIFY content mediumtext NOT NULL;
ALTER TABLE wp_linguist_content_chapter CHANGE content content_html mediumtext NOT NULL;
ALTER TABLE wp_linguist_content_chapter ADD content_bb_code mediumtext DEFAULT null NULL;
ALTER TABLE wp_linguist_content_chapter MODIFY content_html mediumtext DEFAULT NULL;

#code-notes update the new column with the old column
update wp_linguist_content_chapter set content_bb_code = content_html;






