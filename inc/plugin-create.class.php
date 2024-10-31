<?php
namespace QuickWP;
abstract class Plugin_Create
{
	private $plugins = array();
	private $file_string;

	function get_plugin_slugs()
    {
        if ( ! function_exists( 'get_plugins' ) )
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_array = \get_plugins();

        if ( empty( $plugin_array ) )
            return false;

        $slugs = array();

        foreach($plugin_array as $plugin_slug=>$values)
        {
            $slugs[] = basename(
                $plugin_slug,
                '.php'
            );
        }
        return $slugs;
    }

    function get_plugin_names()
    {
        if ( ! function_exists( 'get_plugins' ) )
        {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_array = \get_plugins();

        if ( empty( $plugin_array ) )
            return false;

        $names = array();

        foreach($plugin_array as $plugin)
        {
            if($plugin["Name"] != 'QuickWP')
            {
                $names[] = $plugin["Name"];
            }
        }
        return $names;
    }

    function download_file()
    {
        $plugin_slugs = Plugin_Create::get_plugin_slugs();
        $file = '[plugins]';
        if(count($plugin_slugs) > 0)
        {
            foreach($plugin_slugs as $ps)
            {
                if($ps != 'quickwp')
                {
                    $file.='[slug='.$ps.']';
                }
            }
        }
        $file.='[/plugins]';
        return $file;
    }
}