<?php
/**
 * Class FreelinguistUnitDisplayThis
 * used by @see FreelinguistUnitDisplay::make_single_unit to create it
 * and rendered by @see FreelinguistUnitDisplayHomepageTagRow::output_template()
 */
class FreelinguistUnitDisplaySingleUnit {
    /**
     * The twig template (as opposed to the html the twig template renders), false at init, otherwise string
     * @var bool|string
     */
    public $template = '';

    /*
     * The template vars as discussed , these vars are placed in this array,
     *  and are set in the constructor, after being gathered by the code calling it
     */
    public $template_vars = [];

    /*
     * The admin vars as discussed , these vars are placed in this array,
     *  and are set in the constructor, after being gathered by the code calling it
     */
    public $admin_vars = [];



    /**
     * FreelinguistUnitDisplayThis constructor.
     * @param string $template
     * @param array $vars
     * @param array $admin_vars
     */
    public function __construct($template, $vars,$admin_vars)
    {
        $this->template = $template;
        $this->template_vars = $vars;
        $this->admin_vars = $admin_vars;
    }

    /**
     * called by @see FreelinguistUnitDisplayHomepageTagRow::output_template()
     * @uses FreelinguistUnitDisplay::render_template() to do the calls to the twig library
     *
     *  * @uses FreelinguistUnitDisplay::$b_debug to decide if to render extra html
     *   based on the debugging, it may render extra <li> to show the template itself or the vars for the template
     *   or just pass in the admin vars if the level is 2 or greater
     */
    public function output_template() {
        try {
            $vars = $this->template_vars;
            if (FreelinguistUnitDisplay::$n_debug_level >= 2) {
                $vars = array_merge($this->template_vars,$this->admin_vars);
            }
            FreelinguistUnitDisplay::render_template($this->template,$vars);
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