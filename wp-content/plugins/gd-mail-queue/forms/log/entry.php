<?php

function _gdmaq_entry_preview_render_emails($emails, $rel) {
    $out = array();

    foreach ($emails as $email) {
        $out[] = '<span class="gdmaq-single-email gdmaq-email-'.$rel.'">'.$email->email.'</span>';
    }

    return join(", ", $out);
}

$_html_iframe = '#';
$_has_html = $_has_plain = false;
$_has_attachments = count($email->attachments) > 0;

if (isset($email->extras->ContentType)) {
    if ($email->extras->ContentType == 'multipart/alternative' || $email->extras->ContentType == 'text/html') {
        $_has_html = true;
        $_html_iframe = admin_url("admin-ajax.php?action=gdmaq_log_entry_html&id=".$email->id."&_ajax_nonce=".wp_create_nonce("gdrts-log-html-".$email->id));
    }
}

if (!$_has_html || $_has_html && !empty($email->plain)) {
    $_has_plain = true;
}

$timestamp = gdmaq()->datetime->timestamp_gmt_to_local(strtotime($email->logged));

?>

<div class="d4p-ctrl-tabs gdmaq-entry-log-tabs">
    <div role="tablist" aria-label="<?php esc_attr_e("Email Log Information", "gd-mail-queue"); ?>">
        <button role="tab" aria-selected="true" aria-controls="gdmaq-entry-basic-tab" id="gdmaq-entry-basic">
            <?php _e("Basic", "gd-mail-queue"); ?>
        </button>
        <button role="tab" aria-selected="false" aria-controls="gdmaq-entry-extra-tab" id="gdmaq-entry-extra">
            <?php _e("Extra", "gd-mail-queue"); ?>
        </button>
        <?php if ($_has_plain) { ?>
        <button role="tab" aria-selected="false" aria-controls="gdmaq-entry-plain-tab" id="gdmaq-entry-plain">
            <?php _e("Plain", "gd-mail-queue"); ?>
        </button>
        <?php } ?>
        <?php if ($_has_html) { ?>
        <button role="tab" aria-selected="false" aria-controls="gdmaq-entry-html-tab" id="gdmaq-entry-html">
            <?php _e("HTML", "gd-mail-queue"); ?>
        </button>
        <?php } ?>
        <?php if ($_has_attachments) { ?>
        <button role="tab" aria-selected="false" aria-controls="gdmaq-entry-attachments-tab" id="gdmaq-entry-attachments">
            <?php _e("Attachments", "gd-mail-queue"); ?>
        </button>
        <?php } ?>
        <?php if (isset($email->mailer->Mailer) && $email->mailer->Mailer == 'smtp') { ?>
            <button role="tab" aria-selected="false" aria-controls="gdmaq-entry-smtp-tab" id="gdmaq-entry-smtp">
                <?php _e("SMTP", "gd-mail-queue"); ?>
            </button>
        <?php } ?>
    </div>
    <div class="d4p-ctrl-tabs-content">
        <div tabindex="0" role="tabpanel" id="gdmaq-entry-basic-tab" aria-labelledby="gdmaq-entry-basic">
            <div class="gdmaq-email-element">
                <h3><?php _e("Subject", "gd-mail-queue"); ?>:</h3>
                <span><?php echo $email->subject; ?></span>
            </div>
            <div class="gdmaq-email-element">
                <h3><?php _e("Logged", "gd-mail-queue"); ?>:</h3>
                <span><?php echo date('Y-m-d', $timestamp).' @ '.date('H:i:s', $timestamp); ?></span>
            </div>
            <?php if ($email->type != 'mail' && isset(gdmaq_mailer()->detection()->supported_types[$email->type])) { ?>
                <div class="gdmaq-email-element">
                    <h3><?php _e("Source", "gd-mail-queue"); ?>:</h3>
                    <span><?php echo gdmaq_mailer()->detection()->supported_types[$email->type]; ?></span>
                </div>
            <?php } ?>
            <div class="gdmaq-email-element">
                <h3><?php _e("To", "gd-mail-queue"); ?>:</h3>
                <span><?php echo _gdmaq_entry_preview_render_emails($email->emails['to'], 'to'); ?></span>
                <?php if (!empty($email->emails['cc'])) { ?>
                    <h4><?php _e("CC", "gd-mail-queue"); ?>:</h4>
                    <span><?php echo _gdmaq_entry_preview_render_emails($email->emails['cc'], 'cc'); ?></span>
                <?php } ?>
                <?php if (!empty($email->emails['bcc'])) { ?>
                    <h4><?php _e("BCC", "gd-mail-queue"); ?>:</h4>
                    <span><?php echo _gdmaq_entry_preview_render_emails($email->emails['bcc'], 'bcc'); ?></span>
                <?php } ?>
            </div>
            <div class="gdmaq-email-element">
                <h3><?php _e("From", "gd-mail-queue"); ?>:</h3>
                <span><?php echo _gdmaq_entry_preview_render_emails($email->emails['from'], 'from'); ?></span>
                <?php if (!empty($email->emails['reply_to'])) { ?>
                    <h4><?php _e("Reply To", "gd-mail-queue"); ?>:</h4>
                    <span><?php echo _gdmaq_entry_preview_render_emails($email->emails['reply_to'], 'reply_to'); ?></span>
                <?php } ?>
            </div>
        </div>
        <div tabindex="0" role="tabpanel" id="gdmaq-entry-extra-tab" aria-labelledby="gdmaq-entry-extra" hidden>
            <?php foreach ($email->extras as $key => $value) { ?>
                <div class="gdmaq-email-element">
                    <h3><?php echo $key; ?>:</h3>
                    <span><?php echo $value; ?></span>
                </div>
            <?php } ?>
            <?php if (!empty($email->headers)) { ?>
                <div class="gdmaq-email-element">
                    <h3><?php _e("Extra Headers", "gd-mail-queue"); ?>:</h3>
                    <?php foreach ($email->headers as $header) { ?>
                        <span><?php echo $header[0]; ?>: <?php echo $header[1]; ?></span><br/>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php if ($_has_plain) { ?>
        <div tabindex="0" role="tabpanel" id="gdmaq-entry-plain-tab" aria-labelledby="gdmaq-entry-plain" hidden>
            <pre><?php echo $email->plain; ?></pre>
        </div>
        <?php } ?>
        <?php if ($_has_html) { ?>
        <div tabindex="0" role="tabpanel" id="gdmaq-entry-html-tab" aria-labelledby="gdmaq-entry-html" hidden>
            <iframe src="<?php echo $_html_iframe; ?>" height="350" width="648" sandbox></iframe>
        </div>
        <?php } ?>
        <?php if ($_has_attachments) { ?>
        <div tabindex="0" role="tabpanel" id="gdmaq-entry-attachments-tab" aria-labelledby="gdmaq-entry-attachments" hidden="">
            <div class="gdmaq-email-element">
                <h3><?php _e("Total files", "gd-mail-queue"); ?>:</h3>
                <span><?php echo count($email->attachments); ?></span>
            </div>
            <div class="gdmaq-email-element">
                <h3><?php _e("List of files", "gd-mail-queue"); ?>:</h3>
                <?php foreach ($email->attachments as $file) { ?>
                    <h4><?php echo $file[1]; ?></h4>
                    <span><?php echo $file[4]; ?></span><br/>
                    <span><?php echo $file[0]; ?></span>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <?php if (isset($email->mailer->Mailer) && $email->mailer->Mailer == 'smtp') { ?>
            <div tabindex="0" role="tabpanel" id="gdmaq-entry-smtp-tab" aria-labelledby="gdmaq-entry-smtp" hidden>
                <?php foreach ($email->mailer as $key => $value) { ?>
                <div class="gdmaq-email-element">
                    <h3><?php echo $key; ?>:</h3>
                    <span><?php echo $value; ?></span>
                </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
