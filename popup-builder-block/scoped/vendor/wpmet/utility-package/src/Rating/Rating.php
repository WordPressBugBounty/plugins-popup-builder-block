<?php

namespace PopupKitScopedDependencies\Wpmet\UtilityPackage\Rating;

\defined('ABSPATH') || exit;
use DateTime;
use PopupKitScopedDependencies\Wpmet\UtilityPackage\Notice\Notice as LibsNotice;
use PopupKitScopedDependencies\Wpmet\UtilityPackage\Helper\Helper as UtilsHelper;
/**
 * Asking client for rating and
 * other stuffs
 * Class Rating
 * @package Wpmet\UtilityPackage
 */
class Rating
{
    private $plugin_name;
    private $priority = 10;
    private $days;
    private $rating_url;
    private $support_url;
    private $version;
    private $condition_status = \true;
    private $text_domain;
    private $plugin_logo;
    private $plugin_screens;
    private $duplication = \false;
    private $never_show_triggered = \false;
    private $rating_show_interval = 30;
    /**
     * scripts version
     *
     * @var string
     */
    // protected $script_version = '2.0.0';
    private static $instance;
    /**
     * Method: instance -> Return Notice module class instance
     *
     * @param string|null $text_domain
     * @param string|null $unique_id
     * @return mixed
     */
    public static function instance($text_domain = null, $unique_id = null)
    {
        if ($text_domain == null) {
            return \false;
        }
        self::$instance = new self();
        self::$instance->config($text_domain, \is_null($unique_id) ? \uniqid() : $unique_id);
        return self::$instance;
    }
    /**
     * Set Text domain
     * 
     * @param string $text_domain
     * @param string $unique_id
     */
    public function config($text_domain, $unique_id)
    {
        $this->text_domain = $text_domain;
    }
    /**
     * Get vesrion of $this
     * 
     * @return \Wpmet\Rating\Rating
     */
    public function get_version()
    {
        // return $this->script_version;
        return UtilsHelper::get_pac_version();
    }
    /**
     * @return $this file location for debugging 🐛 purpose
     */
    public function get_script_location()
    {
        return __FILE__;
    }
    /**
     * @param
     */
    public function set_plugin($plugin_name, $plugin_url)
    {
        $this->plugin_name = $plugin_name;
        $this->rating_url = $plugin_url;
        return $this;
    }
    /**
     * @param
     */
    public function set_priority($priority)
    {
        $this->priority = $priority;
        return $this;
    }
    public function set_first_appear_day($days = 7)
    {
        $this->days = $days;
        return $this;
    }
    public function set_rating_url($url)
    {
        $this->rating_url = $url;
        return $this;
    }
    public function set_support_url($url)
    {
        $this->support_url = $url;
        return $this;
    }
    public function set_plugin_logo($logo_url)
    {
        $this->plugin_logo = $logo_url;
        return $this;
    }
    public function set_allowed_screens($screen)
    {
        $this->plugin_screens[] = $screen;
        return $this;
    }
    public function set_condition($result)
    {
        switch (\gettype($result)) {
            case 'boolean':
                $this->condition_status = $result;
                break;
            case 'object':
                $this->condition_status = $result();
                break;
            default:
                $this->condition_status = \false;
        }
        return $this;
    }
    public static function init()
    {
        add_action('wp_ajax_wpmet_rating_never_show_message', array(__CLASS__, 'never_show_message'));
        add_action('wp_ajax_wpmet_rating_ask_me_later_message', array(__CLASS__, 'ask_me_later_message'));
    }
    protected function is_current_screen_allowed($current_screen_id)
    {
        if (\in_array($current_screen_id, \array_merge($this->plugin_screens, array('dashboard', 'plugins')))) {
            return \true;
        }
        return \false;
    }
    /**
     * ------------------------------------------
     *      🚀 Rating class execution point 
     * ------------------------------------------
     */
    public function call()
    {
        $this->init();
        add_action('admin_head', array($this, 'fire'), $this->priority);
    }
    /**
     * -------------------------------------------
     *      🔥 fire the rating functionality
     * -------------------------------------------
     */
    public function fire()
    {
        if (current_user_can('update_plugins')) {
            $current_screen = get_current_screen();
            if (!$this->is_current_screen_allowed($current_screen->id)) {
                return;
            }
            if ($this->condition_status === \false) {
                return;
            }
            add_action('admin_footer', array($this, 'scripts'), 9999);
            if ($this->action_on_fire()) {
                if (!$this->is_installation_date_exists()) {
                    $this->set_installation_date();
                }
                if (get_option($this->text_domain . '_never_show') == 'yes') {
                    return;
                }
                if (get_option($this->text_domain . '_ask_me_later') == 'yes') {
                    $this->days = $this->rating_show_interval;
                    $this->duplication = \true;
                    $this->never_show_triggered = \true;
                    if ($this->get_remaining_days() >= $this->days) {
                        $this->duplication = \false;
                    }
                }
                $this->display_message_box();
            }
        }
    }
    private function action_on_fire()
    {
        return \true;
    }
    public function set_installation_date()
    {
        add_option($this->text_domain . '_install_date', \date('Y-m-d h:i:s'));
    }
    public function is_installation_date_exists()
    {
        return get_option($this->text_domain . '_install_date') == \false ? \false : \true;
    }
    public function get_installation_date()
    {
        return get_option($this->text_domain . '_install_date');
    }
    public function set_first_action_date()
    {
        add_option($this->text_domain . '_first_action_Date', \date('Y-m-d h:i:s'));
        add_option($this->text_domain . '_first_action', 'yes');
    }
    public function get_days($from_date, $to_date)
    {
        return \round(($to_date->format('U') - $from_date->format('U')) / (60 * 60 * 24));
    }
    public function is_first_use($in_days)
    {
        $install_date = get_option($this->text_domain . '_install_date');
        $display_date = \date('Y-m-d h:i:s');
        $datetime1 = new DateTime($install_date);
        $datetime2 = new DateTime($display_date);
        $diff_interval = $this->get_days($datetime1, $datetime2);
        if (\abs($diff_interval) >= $in_days && get_option($this->text_domain . '_first_action_Date') == 'yes') {
            // action implementation here
        }
    }
    /**
     * ---------------------------------------------
     *  Change the status of Rating notification
     *  not to show the message again
     * ---------------------------------------------
     */
    public static function never_show_message()
    {
        if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpmet_rating')) {
            //return false;
        }
        $plugin_name = isset($_POST['plugin_name']) ? sanitize_key($_POST['plugin_name']) : '';
        add_option($plugin_name . '_never_show', 'yes');
    }
    /**
     * set rating show interval if user set ask me later 
     */
    public function rating_show_interval(int $rating_show_interval = 30)
    {
        $this->rating_show_interval = $rating_show_interval;
        return $this;
    }
    public function get_remaining_days()
    {
        $install_date = get_option($this->text_domain . '_install_date');
        $display_date = \date('Y-m-d h:i:s');
        $datetime1 = new DateTime($install_date);
        $datetime2 = new DateTime($display_date);
        $diff_interval = $this->get_days($datetime1, $datetime2);
        return \abs($diff_interval);
    }
    /**
     *----------------------------------
     *  Ask me later functionality
     *----------------------------------
     */
    public function display_message_box()
    {
        if (!$this->duplication) {
            global $wpmet_libs_execution_container;
            if (isset($wpmet_libs_execution_container['rating'])) {
                return;
            }
        }
        $wpmet_libs_execution_container['rating'] = __FILE__;
        $install_date = get_option($this->text_domain . '_install_date');
        $display_date = \date('Y-m-d h:i:s');
        $datetime1 = new DateTime($install_date);
        $datetime2 = new DateTime($display_date);
        $diff_interval = $this->get_days($datetime1, $datetime2);
        if (\abs($diff_interval) >= $this->days) {
            $not_good_enough_btn_id = $this->never_show_triggered ? '_btn_never_show' : '_btn_not_good';
            $message = "Hello! Seems like you have used {$this->plugin_name} to build this website — Thanks a lot! <br>\n\t\t\t\t\t\tCould you please do us a <b>big favor</b> and give it a <b>5-star</b> rating on WordPress? \n\t\t\t\t\t\tThis would boost our motivation and help other users make a comfortable decision while choosing the {$this->plugin_name}";
            LibsNotice::instance($this->text_domain, '_plugin_rating_msg_used_in_day')->set_message($message)->set_logo($this->plugin_logo, 'max-height: 100px !important')->set_button(array('url' => $this->rating_url, 'text' => 'Ok, you deserved it', 'class' => 'button-primary', 'id' => $this->text_domain . '_btn_deserved'))->set_button(array('url' => get_current_screen()->id == 'toplevel_page_getgenie' ? '#write-for-me' : '#', 'text' => 'I already did', 'class' => 'button-default', 'id' => $this->text_domain . '_btn_already_did', 'icon' => 'dashicons-before dashicons-smiley'))->set_button(array('url' => $this->support_url, 'text' => 'I need support', 'class' => 'button-default', 'id' => '#', 'icon' => 'dashicons-before dashicons-sos'))->set_button(['url' => '#', 'text' => 'Never ask again', 'class' => 'button-default', 'id' => $this->text_domain . '_btn_never_show', 'icon' => 'dashicons-before dashicons-welcome-comments'])->set_button(array('url' => get_current_screen()->id == 'toplevel_page_getgenie' ? '#write-for-me' : '#', 'text' => 'No, not good enough', 'class' => 'button-default', 'id' => $this->text_domain . $not_good_enough_btn_id, 'icon' => 'dashicons-before dashicons-thumbs-down'))->call();
        }
    }
    /**
     *---------------------------------------------------------
     *  When user will click @notGoodEnough button
     *  Then it will fire this function to change the status
     *  for next asking time
     *---------------------------------------------------------
     */
    public static function ask_me_later_message()
    {
        if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpmet_rating')) {
            return \false;
        }
        $plugin_name = isset($_POST['plugin_name']) ? sanitize_key($_POST['plugin_name']) : '';
        if (get_option($plugin_name . '_ask_me_later') == \false) {
            add_option($plugin_name . '_ask_me_later', 'yes');
        } else {
            add_option($plugin_name . '_never_show', 'yes');
        }
    }
    /**
     *--------------------------------------
     *  Get current version of the plugin
     *--------------------------------------
     */
    public function get_current_version()
    {
        return $this->version;
    }
    /**
     *-------------------------------------------
     *     Get previous version of the plugin
     *     that have been stored in database
     *-------------------------------------------
     */
    public function get_previous_version()
    {
        return get_option($this->text_domain . '_version');
    }
    /**
     *----------------------------------------
     *     Set current version of the plugin
     *----------------------------------------
     */
    public function set_version($version)
    {
        if (!get_option($this->text_domain . '_version')) {
            add_option($this->text_domain . '_version');
        } else {
            update_option($this->text_domain . '_version', $version);
        }
    }
    /**
     *
     * JS Ajax script for updating
     * rating status from users
     *
     */
    public function scripts()
    {
        echo "\n\t\t\t<script>\n\t\t\tjQuery(document).ready(function (\$) {\n\t\t\t\t\n\t\t\t\t\$( '#" . esc_js($this->text_domain) . "_btn_already_did' ).on( 'click', function() {\n\n\t\t\t\t\t\$.ajax({\n\t\t\t\t\t\turl: ajaxurl,\n\t\t\t\t\t\ttype: 'POST',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\taction \t: 'wpmet_rating_never_show_message',\n\t\t\t\t\t\t\tplugin_name : '" . esc_js($this->text_domain) . "',\n\t\t\t\t\t\t\tnonce : '" . esc_js(wp_create_nonce('wpmet_rating')) . "'\n\n\t\t\t\t\t\t},\n\t\t\t\t\t\tsuccess:function(response){\n\t\t\t\t\t\t\t\$('#" . esc_js($this->text_domain) . "-_plugin_rating_msg_used_in_day').remove();\n\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\n\t\t\t\t});\n\n\t\t\t\t\$('#" . esc_js($this->text_domain) . "_btn_deserved').click(function(){\n\t\t\t\t\t\$.ajax({\n\t\t\t\t\t\turl: ajaxurl,\n\t\t\t\t\t\ttype: 'POST',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\taction \t: 'wpmet_rating_never_show_message',\n\t\t\t\t\t\t\tplugin_name : '" . esc_js($this->text_domain) . "',\n\t\t\t\t\t\t\tnonce : '" . esc_js(wp_create_nonce('wpmet_rating')) . "'\n\t\t\t\t\t\t},\n\t\t\t\t\t\tsuccess:function(response){\n\t\t\t\t\t\t\t\$('#" . esc_js($this->text_domain) . "-_plugin_rating_msg_used_in_day').remove();\n\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\t\t\t\t});\n\n\t\t\t\t\$('#" . esc_js($this->text_domain) . "_btn_not_good').click(function(){\n\t\t\t\t\t\$.ajax({\n\t\t\t\t\t\turl: ajaxurl,\n\t\t\t\t\t\ttype: 'POST',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\taction \t: 'wpmet_rating_ask_me_later_message',\n\t\t\t\t\t\t\tplugin_name : '" . esc_js($this->text_domain) . "',\n\t\t\t\t\t\t\tnonce : '" . esc_js(wp_create_nonce('wpmet_rating')) . "'\n\t\t\t\t\t\t},\n\t\t\t\t\t\tsuccess:function(response){\n\t\t\t\t\t\t\t\$('#" . esc_js($this->text_domain) . "-_plugin_rating_msg_used_in_day').remove();\n\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\t\t\t\t});\n\t\t\t\t\n\t\t\t\t\$('#" . esc_js($this->text_domain) . "_btn_never_show').click(function(){\n\t\t\t\t\t\$.ajax({\n\t\t\t\t\t\turl: ajaxurl,\n\t\t\t\t\t\ttype: 'POST',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\taction \t: 'wpmet_rating_never_show_message',\n\t\t\t\t\t\t\tplugin_name : '" . esc_js($this->text_domain) . "',\n\t\t\t\t\t\t\tnonce : '" . esc_js(wp_create_nonce('wpmet_rating')) . "'\n\t\t\t\t\t\t},\n\t\t\t\t\t\tsuccess:function(response){\n\t\t\t\t\t\t\t\$('#" . esc_js($this->text_domain) . "-_plugin_rating_msg_used_in_day').remove();\n\n\t\t\t\t\t\t}\n\t\t\t\t\t});\n\t\t\t\t});\n\n\t\t\t});\n\t\t\t</script>\n\t";
    }
}
