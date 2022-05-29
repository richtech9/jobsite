CREATE TRIGGER trigger_before_delete_postmeta_for_txn
  BEFORE DELETE
  ON wp_postmeta
  FOR EACH ROW
  BEGIN

    -- code-notes version 3 March-11-2021 Added _withdraw_cancel_message meta key
    -- code-notes version 2 March-11-2021 Added meta withdraw_approved_by
    -- code-notes version 1 March-5-2021

    DECLARE transaction_lookup_id INT; -- for wp_post_data_lookup
    DECLARE da_post_id_itself BIGINT UNSIGNED;


    SELECT k.id INTO transaction_lookup_id FROM wp_transaction_lookup k WHERE k.post_id = OLD.post_id;

    SELECT p.ID INTO da_post_id_itself
    FROM wp_posts p
    WHERE p.ID = OLD.post_id AND p.post_type = 'wallet';



    #     related_to_post_id         and      related_txn
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_transactionRelatedTo' THEN
      UPDATE wp_transaction_lookup k SET k.related_post_id = null WHERE  k.id = transaction_lookup_id;
      UPDATE wp_transaction_lookup k SET k.related_txn = null WHERE k.id = transaction_lookup_id;
    END IF;

    #     numeric_modified_id
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = 'numeric_modified_id' THEN
      UPDATE wp_transaction_lookup k SET k.numeric_modified_id = 0 WHERE k.id = transaction_lookup_id;
    END IF;

    #     transaction_type
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_transactionType' THEN
      UPDATE wp_transaction_lookup k SET k.transaction_type = 0 WHERE  k.id = transaction_lookup_id;
    END IF;

    #     payment_type
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_payment_type' THEN
      UPDATE wp_transaction_lookup k SET k.payment_type = 0 WHERE  k.id = transaction_lookup_id;
    END IF;

    #     withdraw_status
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_transactionWithdrawStatus' THEN
        UPDATE wp_transaction_lookup k SET k.withdraw_status = 0 WHERE  k.id = transaction_lookup_id;
    END IF;

    #     request_payment_notify
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = 'request_payment_notify' THEN
      UPDATE wp_transaction_lookup k SET k.request_payment_notify = 0 WHERE  k.id = transaction_lookup_id;
    END IF;

    #   transaction_amount
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_transactionAmount' THEN
      UPDATE wp_transaction_lookup k SET k.transaction_amount = 0 WHERE k.id = transaction_lookup_id;
    END IF;

    #   transaction_reason
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_transactionReason' THEN
      UPDATE wp_transaction_lookup k SET k.transaction_reason = NULL WHERE k.id = transaction_lookup_id;
    END IF;

    #   withdrawal_message
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = 'withdrawal_message' THEN
      UPDATE wp_transaction_lookup k SET k.withdrawal_message = NULL WHERE k.id = transaction_lookup_id;
    END IF;

    #   withdraw_cancel_message
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_withdraw_cancel_message' THEN
      UPDATE wp_transaction_lookup k SET k.withdraw_cancel_message = NULL WHERE k.id = transaction_lookup_id;
    END IF;


    #   txn
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = '_modified_transaction_id' THEN
      UPDATE wp_transaction_lookup k SET k.txn = NULL WHERE k.id = transaction_lookup_id;
    END IF;

    #   withdraw_approved_by
    IF transaction_lookup_id IS NOT NULL AND OLD.meta_key = 'withdraw_approved_by' THEN
      UPDATE wp_transaction_lookup k SET k.withdraw_approved_by = NULL WHERE k.id = transaction_lookup_id;
    END IF;

  END

