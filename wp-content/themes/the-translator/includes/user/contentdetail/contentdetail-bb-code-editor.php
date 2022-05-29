<?php

/*
* current-php-code 2021-March-20
* input-sanitized :
* current-wp-template:  generates the edit box for a chapter in the linguist content edit page
*/

/**
 * @usage

 *
 *  set_query_var( 'content_chapter_id', $content_chapter_id );
 *  set_query_var( 'content_chapter_words', $content_chapter_words );
 *  get_template_part('includes/user/contentdetail/contentdetail', 'bb-code-editor');
 *
 * @needs wp_linguist_content_chapter info : id, content_bb_code
 */

use Ramsey\Uuid\Uuid;


if (isset($content_chapter_id)) {
    $content_chapter_id = (int)$content_chapter_id;
} else {
    $content_chapter_id = 0;
}

if (!isset($content_chapter_words)) {
    $content_chapter_words = '';
}

$uuid_object = Uuid::uuid4();
$text_id_guid = $uuid_object->toString();
$text_area_html_id = "chapter-content-$content_chapter_id-$text_id_guid";
?>
<script>
    jQuery(function($){ //should execute immediately after load happens if late loading
        let textarea_jq = $('textarea#<?=$text_area_html_id?>');
        let textarea = textarea_jq[0];
        let hidden_input = textarea_jq.closest('div.fl-chapter-editor').find('input.fl-chapter-input');
        let content_chapter_id = <?= $content_chapter_id?>;

        sceditor.create(textarea, {
            format: 'bbcode',
            //style:  '<?= get_template_directory_uri() . '/js/sceditor/minified/themes/default.min.css'?>',
            style:  '<?= get_template_directory_uri() . '/js/sceditor/content-chapter-theme.css'?>',
            toolbarExclude: 'email,unlink,youtube,date,time,ltr,rtl,source,emoticon',//,cut,copy,paste,pastetext,maximize,
            emoticonsRoot: '<?= get_template_directory_uri() . '/js/sceditor/'?>',
            emoticonsEnabled: false,
            bbcodeTrim: false,
            height:400,

        });
        let da_editor = textarea._sceditor;
        da_editor.bind('valuechange  keyup blur paste pasteraw',function(/* e */) {
            let words = da_editor.val();
            hidden_input.val( words);
            //console.debug('bb editor for words: ' + content_chapter_id, words);
        },false,false);
    })
</script>



<div class="fl-chapter-editor">

    <input type="hidden"  name="sub_content[]" class="fl-chapter-input" value="<?=$content_chapter_words?>">

    <textarea
        title="Chapter Editor"

        autocomplete="off"
        data-chapter_id="<?= $content_chapter_id ?>"
        id="<?= $text_area_html_id ?>"
        rows="20"
        class=""
    ><?= strip_tags($content_chapter_words); ?></textarea>
</div>

<!--
:D
[table][tr][td]Where are the table lines?[/td]
[td]Need to make them appear, perhaps[/td]
[/tr]
[tr][td]??[/td]
[td]hello[/td]
[/tr]
[/table]

[s]testing [/s][color=#2ecc40][size=7][b]A-BATCH[/b][/size][/color]
-->



