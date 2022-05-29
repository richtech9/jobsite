<?php

/*
 * -- reset a file to how it was in another commit
 git checkout c5f567~1 -- file1/to/restore file2/to/restore
 git checkout bcd1f1a~1   -- wp-content/themes/the-translator/assets/libs/strophe.js
 */


/*
 * --undo a commit but keep changes
 * git reset "HEAD^"
 */


/**
 * look at past changes
 * git log -S "request_completion"  --pretty=format:"%h %ad- %s [%an]" wp-content/themes/the-translator/includes/user/single-job/single-job-customer.php
 * git log -S "_transactionWithdrawStatus"  --pretty=format:"%h %ad- %s [%an]" wp-content/themes/the-translator/includes/admin-init/admin-withdrawal-request-table.php
 *  _transactionWithdrawStatus  wp-content/themes/the-translator/includes/admin-init/admin-page-withdrawal.php
 * admin-withdrawal-request-table.php
 */

/*
 * one line logs
 * git log --pretty=oneline
 */

/*
 * If master is the only branch and is begin origin master then
 * git reset --hard origin/master
 */

/*
 * When switching branches to very early version that still track php storm build/editor files, and the php storm updates, then git will start to track them
 * git update-index --assume-unchanged  .idea/php.xml
 * wp-content/endurance-page-cache/
 */