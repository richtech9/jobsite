/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

USE `admin_prod_sitenear`;
/* Create Procedure in target  */

DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `createRandomusers`(IN cnt INteger)
  BEGIN
    declare _ret integer default 1;
    declare _prefix varchar(50);
    declare _year integer;
    DECLARE _modified_counter integer default 10000;
    declare _modified_id varchar(50);
    DECLARE _email VARCHAR(50);
    declare _index integer;
    declare t_err integer default 0;
    declare continue handler for sqlexception set t_err = 1;

    set _index = 0;
    loop_1: loop

      if _index > cnt then
        leave loop_1;
      end if;
      set _index = _index + 1;

      start transaction;
      select year(curdate()) into _year;
      select ifnull(option_value, 10000) from options where option_key = "user_modified_counter" into _modified_counter;
      set _modified_counter = _modified_counter + 1;
      set _modified_id = concat("22", _year, _modified_counter);
      set _email = concat("usr", _modified_counter, "@test.com");

      insert into users set user_modified_id=_modified_id, email=_email, username=_email, first_name="user", last_name="",
        city='', remember_token='', address='', vip_enroll_cancel_by=0, created_at=now();

      update options set option_value = _modified_counter where option_key = "user_modified_counter";

      if t_err = 1 then
        rollback;
        set _ret = -1;
      else
        commit;
      end if;
      ITERATE loop_1;
    END LOOP;
    select _ret;



  END$$
DELIMITER ;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;