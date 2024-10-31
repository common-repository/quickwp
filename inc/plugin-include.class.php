<?php
namespace QuickWP;
abstract class Plugin_Include
{
	private $plugins = array();
	private $file_string;

	public function get_plugin_api($slug)
    {
        static $api = array();

        if(!isset($api[$slug]))
        {
            if(!function_exists('plugins_api'))
            {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api('plugin_information',array('slug'=>$slug,'fields'=>array('sections'=>false)));

            $api[$slug] = false;
            if(is_wp_error($response))
            {
                unset($_SESSION["quickwp_plugins"]);
                wp_die(esc_html('An error occured. Please make sure the QuickWP file contains only correct plugin slugs.'));
            }
            else
            {
                $api[$slug] = $response;
            }
        }
        return $api[$slug];
    }

    public function extract_plugins($file_string)
    {
    	$out = array();
    	$file_string = preg_replace('/\s/', '', $file_string);
    	if(preg_match('#\[plugins\](.*?)\[\/plugins\]#', $file_string, $plugins))
		{
			if(preg_match_all('#\[slug=(.*?)\]#', $plugins[1], $result))
			{
				foreach($result[1] as $r)
				{
					array_push($out, array('slug' => $r, 'required' => true));					
				}
				return $out;
			}
		}
		return false;
    }
}