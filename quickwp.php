<?php
/*
Plugin Name: QuickWP
Plugin URI: http://www.lingulo.com/wp-plugins/quickwp-quickly-install-favorite-wordpress-plugins
Description: QuickWP is a plugin to quickly install a collection of plugins on a WordPress install
Version: 0.0.1
Author: Christoph Anastasiades
Author URI: https://www.canmedia.rocks
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Direct access not allowed.' );

if (!function_exists('add_filter'))
{
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define('QUICKWP_VERSION', '0.0.1');
define('QUICKWP_FILE',__FILE__);
define('QUICKWP_PATH',plugin_dir_path(QUICKWP_FILE));
define('QUICKWP_UPLOAD_DIR',plugin_dir_path(QUICKWP_FILE).'uploads/');
define('QUICKWP_KB', 1024);
define('QUICKWP_MB', 1048576);
define('QUICKWP_MAX_FILE_SIZE', 1*QUICKWP_MB);

require_once(QUICKWP_PATH.'/inc/class-tgm-plugin-activation.php');
require_once(QUICKWP_PATH.'/inc/quickwp.class.php');
require_once(QUICKWP_PATH.'/inc/plugin-include.class.php');
require_once(QUICKWP_PATH.'/inc/plugin-create.class.php');

add_action( 'tgmpa_register', 'quickwp_register_required_plugins',1);
add_action( 'admin_init', array (new QuickWP\QuickWP('plugin_install','plugin_install','Quick Plugin Install'), 'register' ) );
add_action( 'admin_menu', 'quickwp_menu' );

function quickwp_menu()
{
	add_menu_page(
        'QuickWP',
        'QuickWP',
        'manage_options',
        'quickwp',
        array( QuickWP\QuickWP::get_instance('plugin_install','plugin_install','Quick Plugin Install') , 'render_upload_page'),'dashicons-clock'
    );
    add_submenu_page(
        'quickwp',
        'Creating a QuickWP file',
        'Create file',
        'manage_options',
        'create-file',
        array( QuickWP\QuickWP::get_instance('plugin_install','plugin_install','Quick Plugins Install') , 'render_file_create_page')
    );
    add_submenu_page(
        'quickwp',
        'Browse QuickWP files',
        'Browse QuickWP files',
        'manage_options',
        'browse-files',
        array( QuickWP\QuickWP::get_instance('plugin_install','plugin_install','Quick Plugins Install') , 'render_browse_page')
    );
}

function quickwp_register_required_plugins()
{	
	$plugins = stripslashes($_SESSION["quickwp_plugins"]);
	$plugins = json_decode($plugins,true);
	$i = -1;
	if(count($plugins) > 0)
	{
		foreach($plugins as $plugin)
		{
			$i++;
			$pluginAPI = QuickWP\Plugin_Include::get_plugin_api($plugin["slug"]);
			$plugins[$i]["name"] = $pluginAPI->name;
		}
	}
	else $plugins = array();
	$config = array(
		'id'           => 'quickwp',
		'menu'         => 'quickwp-install-plugins',
		'parent_slug'  => 'admin.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => false,
		'dismiss_msg'  => '',
		'is_automatic' => true,

		
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'quickwp' ),
			'menu_title'                      => __( 'Install Plugins', 'quickwp' ),
			'installing'                      => __( 'Installing Plugin: %s', 'quickwp' ),
			'updating'                        => __( 'Updating Plugin: %s', 'quickwp' ),
			'oops'                            => __( 'Something went wrong with the plugin API.', 'quickwp' ),
			'notice_can_install_required'     => _n_noop(
				'QuickWP would like to install the following plugin: %1$s.',
				'QuickWP would like to install the following plugins: %1$s.',
				'quickwp'
			),
			'notice_can_install_recommended'  => _n_noop(
				'QuickWP recommends the following plugin: %1$s.',
				'QuickWP recommends the following plugins: %1$s.',
				'quickwp'
			),
			'notice_ask_to_update'            => _n_noop(
				'The following plugin needs to be updated to its latest version to ensure maximum compatibility: %1$s.',
				'The following plugins need to be updated to their latest version to ensure maximum compatibility: %1$s.',
				'quickwp'
			),
			'notice_ask_to_update_maybe'      => _n_noop(
				'There is an update available for: %1$s.',
				'There are updates available for the following plugins: %1$s.',
				'quickwp'
			),
			'notice_can_activate_required'    => _n_noop(
				'The following required plugin is currently inactive: %1$s.',
				'The following required plugins are currently inactive: %1$s.',
				'quickwp'
			),
			'notice_can_activate_recommended' => _n_noop(
				'The following recommended plugin is currently inactive: %1$s.',
				'The following recommended plugins are currently inactive: %1$s.',
				'quickwp'
			),
			'install_link'                    => _n_noop(
				'Begin installing plugin',
				'Begin installing plugins',
				'quickwp'
			),
			'update_link' 					  => _n_noop(
				'Begin updating plugin',
				'Begin updating plugins',
				'quickwp'
			),
			'activate_link'                   => _n_noop(
				'Begin activating plugin',
				'Begin activating plugins',
				'quickwp'
			),
			'return'                          => __( 'Return to Required Plugins Installer', 'quickwp' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'quickwp' ),
			'activated_successfully'          => __( 'The following plugin was activated successfully:', 'quickwp' ),
			'plugin_already_active'           => __( 'No action taken. Plugin %1$s was already active.', 'quickwp' ),
			'plugin_needs_higher_version'     => __( 'Plugin not activated. A higher version of %s is needed for this theme. Please update the plugin.', 'quickwp' ),
			'complete'                        => __( 'All plugins installed and activated successfully. %1$s', 'quickwp' ),
			'dismiss'                         => __( 'Dismiss this notice', 'quickwp' ),
			'notice_cannot_install_activate'  => __( 'There are one or more required or recommended plugins to install, update or activate.', 'quickwp' ),
			'contact_admin'                   => __( 'Please contact the administrator of this site for help.', 'quickwp' ),

			'nag_type'                        => '',
		),
		
	);
	tgmpa( $plugins, $config );
}