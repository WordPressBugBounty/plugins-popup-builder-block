<?php

namespace PopupKitScopedDependencies\Wpmet\UtilityPackage\Notice;

\defined('ABSPATH') || exit;
use PopupKitScopedDependencies\Wpmet\UtilityPackage\Helper\Helper as UtilsHelper;
/**
 * Showing Notice
 * other stuffs
 * Class Notice
 * @package Wpmet\UtilityPackage
 */
class Notice
{
    /**
     * scripts version
     *
     * @var string
     */
    // protected $script_version = '2.1.1';
    /**
     * Unique ID to identify each notice
     *
     * @var string
     */
    protected $notice_id;
    /**
     * Plugin text-domain
     *
     * @var string
     */
    protected $text_domain;
    /**
     * Unique ID
     *
     * @var string
     */
    protected $unique_id;
    /**
     * Notice div container's class
     *
     * @var string
     */
    protected $class;
    /**
     * Single button's data
     *
     * @var array
     */
    protected $button;
    /**
     * Size class
     *
     * @var array
     */
    protected $size;
    /**
     * List of all buttons with it's config data
     *
     * @var array
     */
    protected $buttons;
    /**
     * Notice title
     *
     * @var string
     */
    protected $title;
    /**
     * Notice message
     *
     * @var string
     */
    protected $message;
    /**
     * Left logo
     *
     * @var string
     */
    protected $logo;
    /**
     * Container gutter
     *
     * @var string
     */
    protected $gutter;
    /**
     * Left logo style
     *
     * @var string
     */
    protected $logo_style;
    /**
     * Left logo style
     *
     * @var string
     */
    protected $dismissible;
    protected $expired_time;
    /**
     * html markup for notice
     *
     * @var string
     */
    protected $html;
    /**
     * get_version
     *
     * @return string
     */
    public function get_version()
    {
        // return $this->script_version;
        return UtilsHelper::get_pac_version();
    }
    /**
     * get_script_location
     *
     * @return string
     */
    public function get_script_location()
    {
        return __FILE__;
    }
    // config
    /**
     * Configures all setter variables
     *
     * @param  string $prefix
     * @return void
     */
    public function config($text_domain = '', $unique_id = '')
    {
        $this->text_domain = $text_domain;
        $this->unique_id = $unique_id;
        $this->notice_id = $text_domain . '-' . $unique_id;
        $this->dismissible = \false;
        // false, user, global
        $this->expired_time = 1;
        $this->html = '';
        $this->title = '';
        $this->message = '';
        $this->class = '';
        $this->gutter = \true;
        $this->logo = '';
        $this->logo_style = '';
        $this->size = array();
        $this->button = array(
            'default_class' => 'button',
            'class' => 'button-secondary ',
            // button-primary button-secondary button-small button-large button-link
            'text' => 'Button',
            'url' => '#',
            'icon' => '',
        );
        $this->buttons = array();
        return $this;
    }
    // setters begin
    /**
     * Adds classes to the container
     *
     * @param  string $classname
     * @return void
     */
    public function set_class($classname = '')
    {
        $this->class .= $classname;
        return $this;
    }
    public function set_type($type = '')
    {
        $this->class .= ' notice-' . $type;
        return $this;
    }
    public function set_button($button = array())
    {
        $button = \array_merge($this->button, $button);
        $this->buttons[] = $button;
        return $this;
    }
    public function set_id($id)
    {
        $this->notice_id = $id;
        return $this;
    }
    public function set_title($title = '')
    {
        $this->title .= $title;
        return $this;
    }
    public function set_message($message = '')
    {
        $this->message .= $message;
        return $this;
    }
    public function set_gutter($gutter = \true)
    {
        $this->gutter .= $gutter;
        $this->class .= $gutter === \true ? '' : ' no-gutter';
        return $this;
    }
    public function set_logo($logo = '', $logo_style = '')
    {
        $this->logo = $logo;
        $this->logo_style = $logo_style;
        return $this;
    }
    public function set_html($html = '')
    {
        $this->html .= $html;
        return $this;
    }
    // setters ends
    // group getter
    public function get_data()
    {
        return array('message' => $this->message, 'title' => $this->title, 'buttons' => $this->buttons, 'class' => $this->class, 'html' => $this->html);
    }
    public function call()
    {
        add_action('admin_notices', array($this, 'get_notice'));
    }
    public function get_notice()
    {
        // dismissible conditions
        if ('user' === $this->dismissible) {
            $expired = get_user_meta(get_current_user_id(), $this->notice_id, \true);
        } elseif ('global' === $this->dismissible) {
            $expired = get_transient($this->notice_id);
        } else {
            $expired = '';
        }
        global $oxaim_lib_notice_list;
        if (!isset($oxaim_lib_notice_list[$this->notice_id])) {
            $oxaim_lib_notice_list[$this->notice_id] = __FILE__;
            // is transient expired?
            if (\false === $expired || empty($expired)) {
                $this->generate_html();
            }
        }
    }
    public function set_dismiss($scope = 'global', $time = 3600 * 24 * 7)
    {
        $this->dismissible = $scope;
        $this->expired_time = $time;
        return $this;
    }
    public function generate_html()
    {
        ?>
	<div 
		id="<?php 
        echo esc_attr($this->notice_id);
        ?>" 
		class="notice wpmet-notice notice-<?php 
        echo esc_attr($this->notice_id . ' ' . $this->class);
        ?> <?php 
        echo \false === $this->dismissible ? '' : 'is-dismissible';
        ?>"

		expired_time="<?php 
        echo esc_attr($this->expired_time);
        ?>"
		dismissible="<?php 
        echo esc_attr($this->dismissible);
        ?>"
	>
		<?php 
        if (!empty($this->logo)) {
            ?>
			<img class="notice-logo" style="<?php 
            echo esc_attr($this->logo_style);
            ?>" src="<?php 
            echo \esc_url($this->logo);
            ?>" />
		<?php 
        }
        ?>

		<div class="notice-right-container <?php 
        echo empty($this->logo) ? 'notice-container-full-width' : '';
        ?>">

			<?php 
        if (empty($this->html)) {
            ?>
				<?php 
            echo empty($this->title) ? '' : \sprintf('<div class="notice-main-title notice-vert-space">%s</div>', \esc_html($this->title));
            ?>

				<div class="notice-message notice-vert-space">
				<?php 
            echo \wp_kses($this->message, UtilsHelper::get_kses_array());
            ?>
				</div>

				<?php 
            if (!empty($this->buttons)) {
                ?>
					<div class="button-container notice-vert-space">
						<?php 
                foreach ($this->buttons as $button) {
                    ?>
							<a id="<?php 
                    echo !isset($button['id']) ? '' : esc_attr($button['id']);
                    ?>" href="<?php 
                    echo \esc_url($button['url']);
                    ?>" class="wpmet-notice-button <?php 
                    echo esc_attr($button['class']);
                    ?>">
								<?php 
                    if (!empty($button['icon'])) {
                        ?>
									<i class="notice-icon <?php 
                        echo esc_attr($button['icon']);
                        ?>"></i>
								<?php 
                    }
                    ?>
								<?php 
                    echo \esc_html($button['text']);
                    ?>
							</a>
							&nbsp;
						<?php 
                }
                ?>
					</div>
				<?php 
            }
            ?>

			<?php 
        } else {
            ?>
				<?php 
            echo \wp_kses($this->html, UtilsHelper::get_kses_array());
            ?>
			<?php 
        }
        ?>

		</div>

		<?php 
        if (\false !== $this->dismissible) {
            ?>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text">x#test-console-04</span>
			</button>
		<?php 
        }
        ?>

		<div style="clear:both"></div>

	</div>
		<?php 
    }
    public static function init()
    {
        add_action('wp_ajax_wpmet-notices', array(__CLASS__, 'dismiss_ajax_call'));
        add_action('admin_head', array(__CLASS__, 'enqueue_scripts'));
    }
    public static function dismiss_ajax_call()
    {
        if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'wpmet-notices')) {
            return \false;
        }
        $notice_id = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        $dismissible = isset($_POST['dismissible']) ? sanitize_text_field(wp_unslash($_POST['dismissible'])) : '';
        $expired_time = isset($_POST['expired_time']) ? sanitize_text_field(wp_unslash($_POST['expired_time'])) : '';
        if (!empty($notice_id)) {
            if ('user' === $dismissible) {
                update_user_meta(get_current_user_id(), $notice_id, \true);
            } else {
                set_transient($notice_id, \true, $expired_time);
            }
            wp_send_json_success();
        }
        wp_send_json_error();
    }
    public static function enqueue_scripts()
    {
        echo "\n\t\t<script>\n\t\t\tjQuery(document).ready(function (\$) {\n\t\t\t\t\$( '.wpmet-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {\n\n\t\t\t\t\t_this \t\t        = \$( this ).parents('.wpmet-notice').eq(0);\n\t\t\t\t\tvar notice_id \t    = _this.attr( 'id' ) || '';\n\t\t\t\t\tvar expired_time \t= _this.attr( 'expired_time' ) || '';\n\t\t\t\t\tvar dismissible \t= _this.attr( 'dismissible' ) || '';\n\t\t\t\t\tvar x               = \$( this ).attr('class');\n\n\t\t\t\t\t// console.log({\n\t\t\t\t\t//     _this, x, notice_id, expired_time, dismissible\n\t\t\t\t\t// });\n\t\t\t\t\t// return;\n\n\t\t\t\t\t_this.hide();\n\n\t\t\t\t\t\$.ajax({\n\t\t\t\t\t\turl: ajaxurl,\n\t\t\t\t\t\ttype: 'POST',\n\t\t\t\t\t\tdata: {\n\t\t\t\t\t\t\taction \t        : 'wpmet-notices',\n\t\t\t\t\t\t\tnotice_id \t\t: notice_id,\n\t\t\t\t\t\t\tdismissible \t: dismissible,\n\t\t\t\t\t\t\texpired_time \t: expired_time,\n\t\t\t\t\t\t\tnonce \t\t\t: '" . esc_js(wp_create_nonce('wpmet-notices')) . "'\n\t\t\t\t\t\t},\n\t\t\t\t\t});\n\t\t\t\t});\n\t\t\t});\n\t\t</script>\n\t\t<style>\n\t\t\t.wpmet-notice{\n\t\t\t\tmargin-bottom: 15px;\n\t\t\t\tpadding: 0!important;\n\t\t\t\tdisplay: flex;\n\t\t\t\tflex-direction: row;\n\t\t\t\tjustify-content: flex-start;\n\t\t\t\talign-items: center;\n\t\t\t}\n\n\t\t\t.wpmet-notice .notice-right-container{\n\t\t\t\tmargin: .7rem .8rem .8rem;\n\t\t\t}\n\n\t\t\t.notice-container-full-width{\n\t\t\t\twidth:100%!important;\n\t\t\t}\n\t\t\t\n\t\t\t.wpmet-notice.no-gutter{\n\t\t\t\tpadding: 0!important;\n\t\t\t\tborder-width: 0!important;\n\t\t\t}\n\t\t\t.wpmet-notice.no-gutter .notice-right-container{\n\t\t\t\tpadding: 0!important;\n\t\t\t\tmargin: 0!important;\n\t\t\t}\n\n\t\t\t.notice-right-container .notice-vert-space{\n\t\t\t\tmargin-bottom: .8rem;\n\t\t\t}\n\n\t\t\t.notice-right-container .notice-vert-space:last-child,\n\t\t\t.notice-right-container .notice-vert-space:only-child{\n\t\t\t\tmargin-bottom: 0;\n\t\t\t}\n\n\t\t\t.wpmet-notice .notice-logo{\n\t\t\t\tpadding: 3px;\n\t\t\t\tmax-width: 110px;\n\t\t\t\tmax-height: 110px;\n\t\t\t}\n\t\t\t\n\t\t\t.wpmet-notice-button {\n\t\t\t\ttext-decoration:none;\n\t\t\t}\n\t\t\t\n\t\t\t.wpmet-notice-button > i{\n\t\t\t\tmargin-right: 3px;\n\t\t\t}\n\t\t\t\n\t\t\t.wpmet-notice-button .notice-icon{\n\t\t\t\tdisplay:inline-block;\n\t\t\t}\n\n\t\t\t.wpmet-notice-button .notice-icon:before{\n\t\t\t\tvertical-align: middle!important;\n\t\t\t\tmargin-top: -1px;\n\t\t\t}\n\n\t\t\t.wpmet-notice .notice-main-title{\n\t\t\t\tcolor: #1d2327;\n\t\t\t\tfont-size: 1.2rem;\n\t\t\t}\n\t\t\t\n\t\t</style>\n\t";
    }
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
        return self::$instance->config($text_domain, \is_null($unique_id) ? \uniqid() : $unique_id);
    }
}
