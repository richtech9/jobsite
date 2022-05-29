//not included in js files seen by browser, but helps to type objects sent back from server

/**
 * remove_all_files ajax response
 * @typedef {object} RemoveAllFilesAjaxResponse
 * @property {number[]} removed_attachment_ids
 */

/**
 * @typedef {object} devscript_getsiteurl
 * @property {string} getsiteurl
 */

/**
 * @typedef {object} AjaxHelperObject
 * @property {string} ajaxurl
 * @property {string} themeurl
 */

/**
 * @typedef {object} DaAjaxObjectResponse
 * @property {string} url
 * @property {string} msg
 * @property {bool} success
 * @property  {string} do_refresh_message
 */

/**
 * @typedef {object} FreelinguistResetChatResponse
 * @property {string} status error|success|do_no_action
 * @property {string} msg
 * @property {string} chat_account_login_name
 * @property  {string} chat_account_login_password
 */

// noinspection JSValidateTypes
/**
 * @typedef {object} AjaxContestCancelResponse
 * @property {number} status
 * @property {string} message
 *
 */

/**
 * @typedef {object} FreelinguistBasicAjaxResponse
 * @property {boolean} status
 * @property {string} message
 * @property {string} [form_key]
 *
 */





/**
 * @typedef {object} FreelinguistGetChargeListLine
 * @property {number} amount
 * @property {string} charge_name
 * @property {string} description
 * @property {string} amount_formatted
 *
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistJobStatusResponse
 * @property {?boolean} rejected
 * @property {?string} redirect_to
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistAddContentFileResponse
 * @property {?numeric} content_file_id
 * @property {?string} public_name
 * @property {?string} new_name_with_time
 */


/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistDiscussionResponse
 * @property {?boolean} is_login
 * @property {?string} context
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistAmountWithFeeResponse
 * @property {numeric} amount
 * @property {numeric} processing_fee
 * @property {numeric} total
 */



/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistLinkResponse
 * @property {?string} url
 */


/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistCreatePaymentIntent
 * @property {?string} idempotency_key
 * @property {?string} publishable_key
 * @property {?string} client_secret
 * @property {?string} return_url
 */


/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistGetChargeListResponse
 * @property {FreelinguistGetChargeListLine[]} charges
 * @property {number} total
 * @property {number} wallet_balance
 * @property {number} post_balance
 * @property {string} total_formatted
 * @property {string} wallet_balance_formatted
 * @property {string} post_balance_formatted
 * @property {string} html_general_description
 */



/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistProposalFileDeleteResponse
 * @property {number} status
 * @property {string} message
 * @property {number} deleted_proposal_id
 * @property {number[]} deleted_file_ids
 * @property {number} error_proposal_id
 * @property {number[]} error_file_ids
 * @property {string[]} error_file_messages
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistProposalSaveResponse
 * @property {string} proposal_link
 */

/**
 * @typedef {object} FreelinguistProposalSummary
 * @property {number} proposal_id
 * @property {number} contest_id
 * @property {string} contest_title
 * @property {boolean} awarded
 * @property {number} rejected_timestamp
 * @property {number} freelancer
 * @property {number} rating_by_customer
 * @property {number} rating_by_freelancer
 * @property {string} status
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistProposalList
 * @property {FreelinguistProposalSummary[]} proposals
 */

/**
 * @typedef {object} FreelinguistProposalUploadData
 * @property {boolean} status
 * @property {string} message
 * @property {string} filename
 * @property {?number} file_id
 * @property {?number} proposal_id
 *
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistProposalUploadResponse
 * @property {FreelinguistProposalUploadData[]} data
 */


/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistContentBuyResponse
 * @property {string} contentreciept
 * @property {string[]} receipts
 * @property  {string} content_link
 */




/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistWalletResponse
 * @property {number} wallet_amount
 */


/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistContentBidsResponse
 * @property {number} bid_floor
 *
 */


/**
 * @typedef {object} adminAjaxFlags
 * @property {boolean} b_ignore_milestone_cancel_dialog
 * @property {boolean} b_ignore_proposal_cancel_dialog
 * @property {boolean} b_ignore_content_cancel_dialog
 */

/**
 * @typedef {object} adminAjaxObject
 * @property {string} url
 * @property {string} nonce
 * @property {Object.<string,string>} form_keys
 * @property {adminAjaxFlags} flags
 * @property {numeric} logged_in_user_id
 */
//code-notes the adminAjax is defined in the php. Here we give it structure for type checking
/**
 * @type {adminAjaxObject} adminAjax
 */

//code-notes for the society ajax
/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistGenerateReferralResponse
 * @property {number} user_id
 * @property {string} referral_code
 */

//code-notes for chat rooms
/*
 $response = [
                'avatar' => $avatar,
                'room_id' => $room_id,
                'isBlocked' => false,
                'nickname' => $next_username ,
                'project_title' => $room_title,
                'username' => static::get_xmpp_username($next_user_id),
            ];
            wp_send_json(['status' => 'success', 'data' => $response]);
 */

/**
 * @typedef  {object} FreelinguistCreateChatroomData
 * @property {number} room_pk_id
 * @property {bool} isBlocked
 * @property {string} avatar
 * @property {string} room_string_identifier
 * @property {string} nickname
 * @property {string} project_title
 * @property {string} username
 */

/**
 * @typedef {FreelinguistBasicAjaxResponse} FreelinguistCreateChatroomResponse
 * @property {?FreelinguistCreateChatroomData} data
 */
