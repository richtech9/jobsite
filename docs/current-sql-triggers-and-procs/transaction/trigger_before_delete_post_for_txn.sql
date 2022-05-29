# code-notes version 1
CREATE TRIGGER trigger_before_delete_post_for_txn
  BEFORE DELETE
  ON wp_posts
  FOR EACH ROW
  BEGIN
    DECLARE post_meta_id BIGINT UNSIGNED;

    IF OLD.post_type = 'wallet' THEN

      SET post_meta_id := NULL;

      SELECT meta.meta_id INTO post_meta_id
      FROM wp_postmeta meta WHERE meta.meta_key = '_transactionRelatedTo' AND cast(meta.meta_value as unsigned) = OLD.ID;

      IF post_meta_id IS NOT NULL THEN -- insert new row with author id, its ok if its null

        DELETE FROM wp_postmeta WHERE meta_id = post_meta_id;

      END IF;

    END IF;
  END

