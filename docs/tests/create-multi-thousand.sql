# Makes a thousand entries each loop

DELIMITER $$
CREATE  PROCEDURE `create_thousand_entries_each_loop`(IN number_of_loops_to_do int unsigned)
  BEGIN
    DECLARE msg VARCHAR(255);
    DECLARE  number_of_loops int;

    IF number_of_loops_to_do IS NULL THEN

      SET msg := CONCAT('create_thousand_entries_each_loop=> number_of_loops_to_do needs to a positive number ');
      SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = msg;

    END IF;

    SET number_of_loops := 0;

    main_loop: WHILE (number_of_loops < number_of_loops_to_do ) DO
      SET number_of_loops := number_of_loops + 1;
      CALL create_random_data(1000,null);
    END WHILE;


  END$$
DELIMITER ;