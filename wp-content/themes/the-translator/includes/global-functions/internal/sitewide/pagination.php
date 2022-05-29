<?php

/**
 * @param int $current_page - the current page showing (1 based)
 * @param int $total_pages - the total number of pages
 * @param string $url_template - the url to link to. Must have a %page% in the string to be replaced by the page number
 * @param string $location - top|bottom , if something else will default to bottom
 */
function freelinguist_print_pagination_bar($current_page, $total_pages, $url_template,$location)
{


    $page_wings = (int)get_option('fl_page_wings_job_search', 5);
    $page_starting_text = get_option('fl_pagination_starting_text', '<i class="fa fa-angle-double-left"></i>');
    $page_ending_text = get_option('fl_pagination_ending_text', '<i class="fa fa-angle-double-right"></i>');


    $pagination_location = 'freelinguist-pagination-at-bottom';
    if ($location === 'top') {
        $pagination_location = 'freelinguist-pagination-at-top';
    }
    //logic:
    //if $current_page > 1 then show a <<
    // show the last X positive numbers before $current_page
    // show the current page
    // show the next X number, up to total pages
    //  if there is 1 or more pages between the last page displayed and the total pages, then show a  >>

    ?>
    <div class="paginationdiv freelinguist-pagination <?= $pagination_location ?> enhanced-text">

        <ul>

            <li><a href="#">Page <?= $current_page ?> of <?= number_format($total_pages) ?></a></li>

            <?php if ($current_page > 2) {
                # print out the << (or first page)
                ?>
                <li class="endcap">
                    <a href="<?= str_replace('%page%', 1, $url_template) ?>">
                        <?= $page_starting_text ?>
                    </a>
                </li>
            <?php } ?>


            <?php
            //figure out starting page number to link to
            // get either $current_page - 5 or 1, whichever is greater
            $link_page_number = max(1, $current_page - $page_wings);
            # print out the ... if there is a gap in the numbers
            if ($link_page_number > 1) {
                $midpoint = floor(($current_page - $page_wings - 1) / 2);

                ?>
                <li><a href="<?= str_replace('%page%', $midpoint, $url_template) ?>">......</a></li>
            <?php } ?>


            <?php
            // print out links leading to page number
            for ($page_loop_number = $link_page_number; $page_loop_number < $current_page; $page_loop_number++) { ?>
                <li>
                    <a href="<?= str_replace('%page%', $page_loop_number, $url_template) ?>">
                        <?= number_format($page_loop_number) ?>
                    </a>
                </li>
            <?php } ?>



            <?php
            # print out the current page
            if ($current_page) { ?>
                <li class="current-page regular-text">
                    <a href="<?= str_replace('%page%', $current_page, $url_template) ?>">
                        <?= number_format($current_page) ?>
                    </a>
                </li>
            <?php } ?>


            <?php
            // print out links after the page number
            //figure out starting page number to link to
            // get either $current_page - 5 or 1, whichever is greater
            $max_link_page_number = min($total_pages, $current_page + $page_wings);
            for ($page_loop_number = $current_page + 1;
                 ($page_loop_number <= $total_pages) && ($page_loop_number <= $max_link_page_number);
                 $page_loop_number++) { ?>
                <li>
                    <a href="<?= str_replace('%page%', $page_loop_number, $url_template) ?>">
                        <?= number_format($page_loop_number) ?>
                    </a>
                </li>
            <?php } ?>

            <?php

            # print out the ... if there is a gap in the numbers
            if ($page_loop_number < $total_pages - 1) {
                $midpoint = floor(($total_pages - $page_loop_number - 1) / 2);
                ?>
                <li><a href="<?= str_replace('%page%', $midpoint, $url_template) ?>">......</a></li>
            <?php } ?>


            <?php if ($page_loop_number < $total_pages) {
                # print out the >> (or last page)
                ?>
                <li class="endcap">
                    <a href="<?= str_replace('%page%', $total_pages, $url_template) ?>">
                        <?= $page_ending_text ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
    <?php
}