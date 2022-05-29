

DELIMITER $$

CREATE  PROCEDURE manage_top_list(IN id_thing bigint unsigned, IN type_of_id VARCHAR(20), IN current_score bigint unsigned)
  find_a_function: BEGIN

    DECLARE msg VARCHAR(255);

    # do not do anything if empty data passed
    IF id_thing IS NULL OR current_score IS NULL OR current_score = 0 THEN
      LEAVE find_a_function;
    END IF;

    CASE type_of_id
      WHEN 'user' THEN
        CALL manage_top_list_for_users(id_thing,current_score);

      WHEN 'content' THEN
        CALL manage_top_list_for_content(id_thing,current_score);

      ELSE
        SET msg := CONCAT('manage_top_list type_of_id expected user|content but got ',type_of_id);
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = msg;
   END CASE ;


END$$
DELIMITER ;
-- code-notes version 0.1