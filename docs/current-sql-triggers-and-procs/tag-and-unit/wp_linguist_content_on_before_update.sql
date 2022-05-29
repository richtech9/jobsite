CREATE TRIGGER trigger_before_update_linguist_content
  BEFORE UPDATE
  ON wp_linguist_content
  FOR EACH ROW
  BEGIN
    DECLARE rating INT;

    IF NEW.rating_by_customer IS NULL THEN SET rating := 0;
    ELSE SET rating := NEW.rating_by_customer;
    END IF ;

    SET NEW.score :=  (rating + 1) * UNIX_TIMESTAMP(NOW());

  END
-- code-notes version 0.1