# this is for initializing or refreshing the update_count on the interest tags , it can safety be run anytime but is
# only needed when adding the triggers in this directory

UPDATE wp_interest_tags itag
  INNER JOIN (
               SELECT
                 count(j.id) AS da_count,
                 j.tag_id,
                 t.tag_name
               FROM wp_tags_cache_job j
                 INNER JOIN wp_interest_tags t ON t.ID = j.tag_id
               GROUP BY j.tag_id
             ) tag_count ON tag_count.tag_id = itag.ID

SET itag.usage_count = tag_count.da_count
WHERE 1;