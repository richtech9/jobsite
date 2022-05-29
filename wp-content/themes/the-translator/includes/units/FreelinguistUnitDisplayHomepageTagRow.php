<?php

/**
 * Class FreelinguistUnitDisplayHomepageTagRow
 * Renders a tag area , on the homepage when @see FreelinguistUnitDisplayHomepageTagRow::output_template() is called
 *
 * Collects units to display inside of that area, by having an array of FreelinguistUnitDisplaySingleUnit objects
 *
 * and stores the settings for the tag area twig variables used:

'is_new_style'=> boolean, if set then this tag area has its look defined by the wp_homepage_interest_per_id settings
'is_title_hidden' => boolean, if set then does not display the name of the tag
'tag_id' => the tag id, the wp_interest_tags.ID
'background_color' => css background color for the row, tag sections can have alternating colors
'tag_name' => the name of the tag, used in the title of the section
'show_add_tag' => whether to show the add tag control, for logged in users, to set their prefernces for display
'is_user_pref' => if this tag is shown because the user added the tag to be seen on the homepage
'page_number' => the page number of tags, this section is in
'page_total' => the total number of pages of tag sections,
'li_units' => the already rendered html for all the tag units in this section
 *
 *
 */
class FreelinguistUnitDisplayHomepageTagRow {

    /**
     * The twig template (as opposed to the html the twig template renders), false at init, otherwise string
     * @var bool|string
     */
    public $template = '';

    /*
     * The template vars as discussed above, these vars are placed in this array,
     *  and are gathered from the other object settings below
     */
    public $template_vars = [];

    /**
     * The individual units to render in the tag section
     * @var FreelinguistUnitDisplaySingleUnit[] $units
     */
    public $units = []; // concat of output_template => li_units

    public $page_number = 0; // => page_number

    public $page_total = 0; // => page_total

    public $is_user_pref = 0; // => is_user_pref

    public $show_add_tag = 0; // => show_add_tag  (always false for now)

    public $tag_name = ''; // => tag_name

    public $background_color = ''; // => background_color

    public $tag_id = 0 ; // => tag_id

    public $is_title_hidden = 0 ; // ==> is_title_hidden

    public $is_new_style = 0 ; // ==> is_new_style

    /**
     * FreelinguistUnitDisplayHomepageTagRow constructor.
     * Loads in the template string, throw an error if it cannot find it
     */
    public function __construct()
    {
        $template_path =
            ABSPATH . '/wp-content/themes/the-translator/includes/units/twig-templates/homepage_tag_section.twig';

        $this->template = file_get_contents($template_path);

        if ($this->template === false) {
            throw new RuntimeException("FreelinguistUnitDisplayHomepageTagRow construction:: ".
                "Error reading file $template_path");
        }
    }


    /**
     * Called by the @see FreelinguistUnitDisplay::generate_html()
     * this will output the html for a tag area on the homepage, with each of its units displayed inside
     *
     * Logic:
     *  First calls all the units it has stored, and has them generate html, it stores this html in a string
     *
     *  Then it initializes the twig variables used by the tag area surrounding the units, which also include the html just made for all the units,
     *    and renders that whole area
     *
     *  It prints all the html to standard output, but normal usage of this is to convert that output to a string variable,
     *    and return the string back to the web page via javascript ajax
     *
     * @uses FreelinguistUnitDisplay::$b_debug to decide if to render extra html
     *   based on the debugging, it may render extra <li> to show the template itself or the vars for the template
     *
     * @uses FreelinguistUnitDisplay::render_template() to do the calls to the twig library
     *
     */
    public function output_template() {

        $da_unit_html = [];
        foreach ($this->units as $unit) {
            ob_start();
            $unit->output_template();
            $da_unit_html[]  = ob_get_clean();
        }
        $li_units = implode("\n\n",$da_unit_html);
        $this->template_vars = [
            'is_new_style'=> $this->is_new_style ,
            'is_title_hidden' => $this->is_title_hidden ,
            'tag_id' => $this->tag_id ,
            'background_color' => $this->background_color ,
            'tag_name' => $this->tag_name ,
            'show_add_tag' => $this->show_add_tag ,
            'is_user_pref' => $this->is_user_pref ,
            'page_number' => $this->page_number ,
            'page_total' => $this->page_total ,
            'li_units' => $li_units
        ];
        try {
            FreelinguistUnitDisplay::render_template($this->template,$this->template_vars);
            if (FreelinguistUnitDisplay::$b_debug) {
                if (FreelinguistUnitDisplay::$n_debug_level >= 3) {
                    print "<li><pre>". htmlentities($this->template) ."</pre></li>";
                }
                if (FreelinguistUnitDisplay::$n_debug_level >= 4) {
                    print "<li><pre>". print_r($this->template_vars,true) ."</pre></li>";
                }
            }
        } catch (\Twig\Error\Error $e) {
            if (FreelinguistUnitDisplay::$b_debug) {
                print "<li><span class='error'>Twig Error: ".  $e->getMessage()."</span></li>";
                if (FreelinguistUnitDisplay::$n_debug_level >= 2) {
                    print "<li><pre>". htmlentities($this->template) ."</pre></li>";
                }
                if (FreelinguistUnitDisplay::$n_debug_level >= 4) {
                    print "<li><pre>". print_r($this->template_vars.true) ."</pre></li>";
                }
            }

        } catch (Exception $e) {
            if (FreelinguistUnitDisplay::$b_debug) {
                print "<li><span class='error'>General Error: " . $e->getMessage() . "</span></li>";
            }
        }

    }
}