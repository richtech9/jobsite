CREATE TRIGGER trigger_after_update_postmeta_for_txn
  AFTER UPDATE
  ON wp_postmeta
  FOR EACH ROW
  BEGIN

    -- # code-notes version 5 March-11-2021 Added _withdraw_cancel_message meta key
    -- # code-notes version 4 March-11-2021 Added withdraw_approved_by meta key, and alipay option for payment type
    -- # code-notes version 3 March-10-2021 Added more values for withdraw_status (completed and canceled)
    -- # code-notes version 2 March-9-2021 Added new transaction type FREE_credits_used
    -- # code-notes version 1 March-5-2021

    DECLARE transaction_lookup_id INT; -- for wp_post_data_lookup
    DECLARE da_post_id_itself BIGINT UNSIGNED;
    -- for each of the meta keys, update the correct number for the value, the post id row may already exist thanks to the post triggers
    DECLARE val_related_post_id BIGINT UNSIGNED; -- _transactionRelatedTo
    DECLARE val_withdraw_approved_by BIGINT UNSIGNED; -- withdraw_approved_by
    DECLARE val_transaction_type TINYINT; -- _transactionType
    DECLARE val_payment_type TINYINT; -- _payment_type
    DECLARE val_withdraw_status TINYINT; -- _transactionWithdrawStatus
    DECLARE val_request_payment_notify TINYINT; -- request_payment_notify
    DECLARE val_numeric_modified_id BIGINT UNSIGNED; -- numeric_modified_id
    DECLARE val_transaction_amount DECIMAL(10,2); -- _transactionAmount
    DECLARE val_txn VARCHAR(30); -- _modified_transaction_id
    DECLARE val_related_txn VARCHAR(30); -- _modified_transaction_id of related_to_post_id post
    DECLARE val_transaction_reason TEXT; --  _transactionReason
    DECLARE val_withdrawal_message TEXT; -- withdrawal_message
    DECLARE val_withdraw_cancel_message TEXT; -- _withdraw_cancel_message


    DECLARE number_test INT;
    DECLARE text_test TEXT;


    SET val_related_post_id := 0;
    SET val_transaction_type := 0;
    SET val_payment_type := 0;
    SET val_numeric_modified_id := 0;
    SET val_withdraw_status := 0;
    SET val_request_payment_notify := 0;
    SET val_transaction_amount := 0;


    -- see if need to create the post_data_lookup row, we change the post_id if it updates, in the wp_posts trigger
    SELECT k.id INTO transaction_lookup_id FROM wp_transaction_lookup k WHERE k.post_id = NEW.post_id;

    SELECT p.ID INTO da_post_id_itself
    FROM wp_posts p
    WHERE p.ID = NEW.post_id AND p.post_type = 'wallet';

    IF da_post_id_itself IS NOT NULL AND transaction_lookup_id IS NULL THEN -- insert new row with author id, its ok if its null

      INSERT INTO wp_transaction_lookup(post_id) VALUES (da_post_id_itself);
      SET transaction_lookup_id := last_insert_id();

    END IF;


    #   withdraw_approved_by          set to null if empty string
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = 'withdraw_approved_by' THEN
      IF NEW.meta_value = '' THEN SET val_withdraw_approved_by := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_withdraw_approved_by := NULL;
      ELSE

        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_withdraw_approved_by  := CAST(ROUND(NEW.meta_value) as UNSIGNED);
        ELSE
          SET val_withdraw_approved_by := NULL;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'withdraw_approved_by',CONCAT('value of withdraw_approved is not numeric: ',NEW.meta_value));
        END IF ;

      END IF;

      UPDATE wp_transaction_lookup k SET k.withdraw_approved_by = val_withdraw_approved_by WHERE k.id = transaction_lookup_id;
    END IF;


    #   related_to_post_id                 set to null if empty string
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_transactionRelatedTo' THEN
      IF NEW.meta_value = '' THEN SET val_related_post_id := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_related_post_id := NULL;
      ELSE

        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_related_post_id  := CAST(ROUND(NEW.meta_value) as UNSIGNED);
        ELSE
          SET val_related_post_id := NULL;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'related_post_id',CONCAT('value of related_post_id is not numeric: ',NEW.meta_value));
        END IF ;

      END IF;

      UPDATE wp_transaction_lookup k SET k.related_post_id = val_related_post_id WHERE k.id = transaction_lookup_id;


      IF val_related_post_id IS NOT NULL THEN
        # related_txn varchar(30)  (_modified_transaction_id of related_to_post_id )

        SET text_test := NULL;
        SELECT meta.meta_value INTO text_test
        FROM wp_postmeta meta
        WHERE meta.post_id = val_related_post_id and meta.meta_key = '_modified_transaction_id';

        IF text_test = '' THEN SET val_related_txn := NULL; END IF ;

        SELECT char_length(text_test) > 30 INTO number_test ;
        IF number_test = 1  THEN
          SET val_related_txn := NULL;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'related_txn',CONCAT('value of related_txn overflowed: ',text_test));
        ELSE
          SET val_related_txn := text_test;
        END IF ;

        UPDATE wp_transaction_lookup k SET k.related_txn = val_related_txn WHERE k.id = transaction_lookup_id;
      ELSE
        UPDATE wp_transaction_lookup k SET k.related_txn = NULL WHERE k.id = transaction_lookup_id;
      END IF;


    END IF;



    #   numeric_modified_id bigint unsigned (numeric_modified_id)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = 'numeric_modified_id' THEN
      IF NEW.meta_value = '' THEN SET val_numeric_modified_id := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_numeric_modified_id := NULL;
      ELSE

        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_numeric_modified_id  := CAST(ROUND(NEW.meta_value) as UNSIGNED);
        ELSE
          SET val_numeric_modified_id := -100;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'numeric_modified_id',CONCAT('value of numeric_modified_id is not numeric: ',NEW.meta_value));
        END IF ;

      END IF;

      UPDATE wp_transaction_lookup k SET k.numeric_modified_id = val_numeric_modified_id WHERE k.id = transaction_lookup_id;
    END IF;



    #     transaction_type          TINYINT  (meta is _transactionType) none=0|processing_fee=1|refill=2|refund=3|undo_processing_fee=4|withdraw=5|FREE_credits=6|FREE_credits_refund=7)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_transactionType' THEN

      IF NEW.meta_value = 'processing_fee' THEN SET val_transaction_type := 1;
      ELSEIF NEW.meta_value = 'refill' THEN SET val_transaction_type := 2;
      ELSEIF NEW.meta_value = 'refund' THEN SET val_transaction_type := 3;
      ELSEIF NEW.meta_value = 'undo_processing_fee' THEN SET val_transaction_type := 4;
      ELSEIF NEW.meta_value = 'withdraw' THEN SET val_transaction_type := 5;
      ELSEIF NEW.meta_value = 'FREE_credits' THEN SET val_transaction_type := 6;
      ELSEIF NEW.meta_value = 'FREE_credits_refund' THEN SET val_transaction_type := 7;
      ELSEIF NEW.meta_value = 'FREE_credits_used' THEN SET val_transaction_type := 8;
      ELSEIF NEW.meta_value = '' THEN SET val_transaction_type := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_transaction_type := 0;
      ELSE
        SET val_transaction_type := -100;
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'transaction_type',CONCAT('value of transaction_type is not known: ',NEW.meta_value));
      END IF;

      UPDATE wp_transaction_lookup k SET k.transaction_type = val_transaction_type WHERE k.id = transaction_lookup_id;
    END IF;




    #     payment_type          TINYINT  (_payment_type) none=0|paypal=1|stripe=2
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_payment_type' THEN

      IF NEW.meta_value = 'paypal' THEN SET val_payment_type := 1;
      ELSEIF NEW.meta_value = 'stripe' THEN SET val_payment_type := 2;
      ELSEIF NEW.meta_value = 'alipay' THEN SET val_payment_type := 3;
      ELSEIF NEW.meta_value = '' THEN SET val_payment_type := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_payment_type := 0;
      ELSE
        SET val_payment_type := -100;
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'payment_type',CONCAT('value of payment_type is not known: ',NEW.meta_value));
      END IF;

      UPDATE wp_transaction_lookup k SET k.payment_type = val_payment_type WHERE k.id = transaction_lookup_id;
    END IF;


    #     withdraw_status tinyint (_transactionWithdrawStatus) none=0|pending=1
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_transactionWithdrawStatus' THEN

      IF NEW.meta_value = 'pending' THEN SET val_withdraw_status := 1;
      ELSEIF NEW.meta_value = 'canceled' THEN SET val_withdraw_status := 2;
      ELSEIF NEW.meta_value = 'completed' THEN SET val_withdraw_status := 3;
      ELSEIF NEW.meta_value = '' THEN SET val_withdraw_status := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_withdraw_status := 0;
      ELSE
        SET val_withdraw_status := -100;
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'withdraw_status',CONCAT('value of withdraw_status is not known: ',NEW.meta_value));
      END IF;

      UPDATE wp_transaction_lookup k SET k.withdraw_status = val_withdraw_status WHERE k.id = transaction_lookup_id;
    END IF;



    #     request_payment_notify tinyint  (request_payment_notify) none=0|paypal=1|stripe=2|alipay=3
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = 'request_payment_notify' THEN

      IF NEW.meta_value = 'paypal' THEN SET val_request_payment_notify := 1;
      ELSEIF NEW.meta_value = 'stripe' THEN SET val_request_payment_notify := 2;
      ELSEIF NEW.meta_value = 'alipay' THEN SET val_request_payment_notify := 3;
      ELSEIF NEW.meta_value = '' THEN SET val_request_payment_notify := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_request_payment_notify := 0;
      ELSE
        SET val_request_payment_notify := -100;
        INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
        VALUES (transaction_lookup_id,'request_payment_notify',CONCAT('value of request_payment_notify is not known: ',NEW.meta_value));
      END IF;

      UPDATE wp_transaction_lookup k SET k.request_payment_notify = val_request_payment_notify WHERE k.id = transaction_lookup_id;
    END IF;


    # transaction_amount decimal(10,2) (_transactionAmount)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_transactionAmount' THEN
      IF NEW.meta_value = '' THEN SET val_transaction_amount := 0;
      ELSEIF NEW.meta_value IS NULL THEN SET val_transaction_amount := 0;
      ELSE
        # test to see if valid number
        SELECT NEW.meta_value REGEXP '^[+-.0-9]+$'INTO number_test ;
        IF number_test = 1 THEN
          SET val_transaction_amount := CAST(NEW.meta_value as decimal(10,2));
        ELSE
          SET val_transaction_amount := -100;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'val_transaction_amount',CONCAT('value of transaction_amount is non numeric: ',NEW.meta_value));
        END IF ;
      END IF;

      UPDATE wp_transaction_lookup k SET k.transaction_amount = val_transaction_amount WHERE k.id = transaction_lookup_id;
    END IF;


    #   transaction_reason text (_transactionReason)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_transactionReason' THEN
      IF NEW.meta_value = '' THEN SET val_transaction_reason := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_transaction_reason := NULL;
      ELSE
        SET val_transaction_reason := NEW.meta_value ;
      END IF;

      UPDATE wp_transaction_lookup k SET k.transaction_reason = val_transaction_reason WHERE k.id = transaction_lookup_id;
    END IF;


    # withdrawal_message text (withdrawal_message)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = 'withdrawal_message' THEN
      IF NEW.meta_value = '' THEN SET val_withdrawal_message := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_withdrawal_message := NULL;
      ELSE
        SET val_withdrawal_message := NEW.meta_value ;
      END IF;

      UPDATE wp_transaction_lookup k SET k.withdrawal_message = val_withdrawal_message WHERE k.id = transaction_lookup_id;
    END IF;

    # withdraw_cancel_message text (_withdraw_cancel_message)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_withdraw_cancel_message' THEN
      IF NEW.meta_value = '' THEN SET val_withdraw_cancel_message := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_withdraw_cancel_message := NULL;
      ELSE
        SET val_withdraw_cancel_message := NEW.meta_value ;
      END IF;

      UPDATE wp_transaction_lookup k SET k.withdraw_cancel_message = val_withdraw_cancel_message WHERE k.id = transaction_lookup_id;
    END IF;


    # txn varchar(30) (_modified_transaction_id)
    IF transaction_lookup_id IS NOT NULL AND NEW.meta_key = '_modified_transaction_id' THEN
      IF NEW.meta_value = '' THEN SET val_txn := NULL;
      ELSEIF NEW.meta_value IS NULL THEN SET val_txn := NULL;
      ELSE

        SELECT char_length(NEW.meta_value) > 30 INTO number_test ;
        IF number_test = 1  THEN
          SET val_txn := NULL;
          INSERT INTO wp_transaction_lookup_errors(transaction_lookup_id, column_of_error,error_msg)
          VALUES (transaction_lookup_id,'txn',CONCAT('value of txn overflowed: ',NEW.meta_value));
        ELSE
          SET val_txn := NEW.meta_value ;
        END IF ;

      END IF;

      UPDATE wp_transaction_lookup k SET k.txn = val_txn WHERE k.id = transaction_lookup_id;
    END IF;




  END

