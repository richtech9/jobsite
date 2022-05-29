# Changes to make on db
# unique key on wp_project_tags
CREATE UNIQUE INDEX wp_project_tags_post_id_type_uindex ON wp_project_tags (post_id, type);

# wp_proposals has bigint unsigned for post and user fk (already done)

# wp_fl_milestones allows null at post_modified and rejected_at
ALTER TABLE wp_fl_milestones MODIFY updated_at datetime DEFAULT current_timestamp();
ALTER TABLE wp_fl_milestones MODIFY rejected_at datetime;
ALTER TABLE wp_fl_milestones MODIFY post_modified datetime;

#indexes to add
CREATE INDEX meta_value_index ON wp_postmeta (meta_value(50));
CREATE INDEX meta_key_value_index ON wp_postmeta (meta_key(50), meta_value(50));
CREATE INDEX post_key_value_index ON wp_postmeta (post_id,meta_key(50), meta_value(50));
CREATE INDEX post_key_index ON wp_postmeta (post_id,meta_key(50));

CREATE INDEX meta_value_index ON wp_usermeta (meta_value(50));
CREATE INDEX meta_key_value_index ON wp_usermeta (meta_key(50), meta_value(50));
CREATE INDEX user_key_value_index ON wp_usermeta (user_id,meta_key(50), meta_value(50));
CREATE INDEX user_key_index ON wp_usermeta (user_id,meta_key(50));

CREATE INDEX wp_fl_job_project_id_index ON wp_fl_job (project_id);
CREATE INDEX wp_fl_job_author_index ON wp_fl_job (author);

CREATE INDEX wp_fl_milestones_job_id_index ON wp_fl_milestones (job_id);

CREATE INDEX comment_id_meta_key_index ON wp_commentmeta (comment_id, meta_key(50));