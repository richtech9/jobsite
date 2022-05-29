<?php

/**
 * Class FreelinguistTags
 * A small class used for constants when working with the wp_tags_cache_job table,
 *  which uses number flags to designate the type of tag.
 * I added this so that the code is not peppered with the hard to maintain literal numbers of 1-4
 */
class FreelinguistTags {
    const PROJECT_TAG_TYPE = 1;
    const CONTENT_TAG_TYPE = 2;
    const CONTEST_TAG_TYPE = 3;
    const USER_TAG_TYPE = 4;

    const UNKNOWN_TAG_TYPE = -100;
}