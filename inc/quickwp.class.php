<?php
namespace QuickWP;
class QuickWP
{
	protected static $instance = NULL;
	protected $action;
	protected $option_name;
	protected $heading;
    protected $file_string;
	protected $page_id = NULL;
	protected $msg = '';
	protected $msg_text = '';
    protected $plugins = array();
    protected $out = array();

	public function __construct($action,$option_name,$heading)
	{
        $this->action = $action;
        $this->option_name = $option_name;
        $this->heading = $heading;
        if(!session_id())
            session_start();
	}

    public static function get_instance($action,$option_name,$heading)
    {
        NULL === self::$instance and self::$instance = new self($action,$option_name,$heading);
        return self::$instance;
    }

    public function register()
    {        
    	add_action( 'admin_enqueue_scripts', array( $this, 'register_quickwp_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_quickwp_scripts' ) );       
        add_action( 'admin_post_'.$this->action, array ( $this, 'admin_post_quickwp' ) );
    }

    public function register_quickwp_styles()
	{
		wp_register_style('QuickWP', plugins_url('css/style.css',QUICKWP_FILE));
		wp_enqueue_style('QuickWP');
	}

	public function register_quickwp_scripts()
	{
		wp_register_script('QuickWP', plugins_url('js/main.js',QUICKWP_FILE),array('jquery'),'0.0.1',true);
		wp_enqueue_script('QuickWP');
        wp_localize_script('QuickWP', 'qwp_custom', array('plugin_url' => admin_url(),'quickwp_nonce' => wp_create_nonce()));
	}

    public function remove_session()
    {
        unset($_SESSION['quickwp_plugins']);
    }

    public function parse_message()
    {
        if ( ! isset ( $_GET['msg'] ) )
            return;

        $text = FALSE;

        if ( 'error' === $_GET['msg'] )
            $this->msg_text = 'Error!';

        else if ( 'not-uploaded' === $_GET['msg'] )
            $this->msg_text = 'Not uploaded!';        

        if ( $this->msg_text )
            add_action( 'admin_notices', array ( $this, 'render_msg' ) );
    }

    public function render_msg()
    {
        echo '<div class="' . esc_attr( $_GET['msg'] ) . '"><p>'
            . $this->msg_text . '</p></div>';
    }

    public function render_upload_page()
    {
        if(isset($_GET["finish"]) && wp_verify_nonce($_GET["quickwp_nonce"],'quickwp_nonce'))
        {
            if(!session_id())
                session_start();
            QuickWP\QuickWP::remove_session();
            wp_safe_redirect(admin_url()."plugins.php");
            exit();
        }
        $option = esc_attr( stripslashes( get_option( $this->option_name ) ) );
    	$redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        if(!isset($_GET["upload"]))
        {
        ?>
        <div class="wrap quickwp">
	        <h1><?=$this->heading?><span>Upload a QuickWP file to quickly install your plugins</span></h1>
            <p class="text">Please choose a QuickWP file and press the big red button. In case you don't have a QuickWP file yet you can create one using the <a href="<?=admin_url()?>admin.php?page=create-file">creator tool</a> or download an existing collection of plugins <a href="<?=admin_url()?>admin.php?page=browse-files">from us or other users</a>. Note that QuickWP will try to activate your plugins however in some cases you will need to manually activate them.</p>
	        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" enctype="multipart/form-data">
	            <input type="hidden" name="action" value="<?=$this->action?>">
	            <?php wp_nonce_field( $this->action, $this->option_name . '_nonce', FALSE ); ?>
	            <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
	            <p><?php __('Please choose a QuickWP generated file from your hard disk.'); ?></p>	                       
	            <input type="file" size="50" accept="text/plain" name="<?php echo $this->option_name; ?>" id="<?php echo $this->option_name; ?>">
	            <label for="<?php echo $this->option_name; ?>">Choose QuickWP file</label>
	            <?php submit_button(__('Start the magic')); ?>
	        </form>
            <small>Please note: We do not take any responsibility and we are not liable for any damage caused through use of our plugin.</small>
        </div>
        <?php
    	}
    }

    public function render_file_create_page()
    {
        if(isset($_GET["download"]) && isset($_GET["quickwp_nonce"]) && wp_verify_nonce($_GET['quickwp_nonce'], 'download_file'))
        {
            $file = QuickWP\Plugin_Create::download_file();
            header("Content-type: text/plain");
            header("Content-Disposition: attachment; filename=quickwp_".uniqid().".txt");
            echo $file;
            exit();
        }
        $option = esc_attr( stripslashes( get_option( $this->option_name ) ) );
        $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        ?>
        <div class="wrap quickwp create">
            <h1><?php echo $GLOBALS['title']; ?><span>Download your QuickWP file and use it for your next WordPress installs</span></h1>
            <p class="text">Click on the download button to download a QuickWP file with all currently installed plugins. You can then upload that file to another WordPress install and automatically install the plugins.</p>
            <p class="text">The following plugins will be added to the QuickWP file:</p>
            <ul class="list">
            <?php
            $plugin_names = Plugin_Create::get_plugin_names();
            if(count($plugin_names) > 0)
            {
                foreach($plugin_names as $ps)
                {
                    ?>
                    <li><span class="dashicons dashicons-arrow-right"></span><?=$ps?></li>
                    <?php
                }
                ?>
                <a href="<?=wp_nonce_url(admin_url('admin.php?page=create-file&download'),'download_file','quickwp_nonce')?>" class="quickwp-btn margin-top-20"><span class="dashicons dashicons-download" style="font-size:30px;line-height:40px;margin-right:10px;"></span> Download file</a>
                <?php
            }
            else echo 'No plugins installed.';
            ?>
            </ul>          
        </div>
        <?php    
    }

    public function render_browse_page()
    {
        $option = esc_attr( stripslashes( get_option( $this->option_name ) ) );
        $redirect = urlencode( remove_query_arg( 'msg', $_SERVER['REQUEST_URI'] ) );
        ?>
        <div class="wrap quickwp create">
            <h1><?php echo $GLOBALS['title']; ?><span>Choose from our compilation of QuickWP files and get user's best choices for WordPress plugins.</span></h1>
            <iframe src="http://www.lingulo.com/quickwp-accordion-page" width="100%" height="100%" style="position: relative; top: -50px; height: 100vh;"></iframe>             
        </div>
        <?php 
    }

    public function admin_post_quickwp()
    {
        if(!wp_verify_nonce($_POST[$this->option_name.'_nonce'],$this->action))
            die(__('Invalid nonce.').var_export($_POST,true));

        if ($_FILES[$this->option_name]['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES[$this->option_name]['tmp_name']))
        {
            $this->out = file_get_contents($_FILES[$this->option_name]['tmp_name']);          
        }
        if($this->out !== NULL && !empty($this->out))
        {
            $this->out = Plugin_Include::extract_plugins($this->out);
            $this->out = json_encode($this->out);

            $_SESSION['quickwp_plugins'] = $this->out;
            $url = add_query_arg(array('msg' => 'uploaded', 'upload' => 'true', 'plugins' => urlencode($this->out)), urldecode( $_POST['_wp_http_referer'] ) );

            // if ( isset ( $_POST[ $this->option_name ] ) )
            // {
            //     update_option( $this->option_name, $_POST[ $this->option_name ] );
            //     $msg = 'updated';
            // }
            // else
            // {
            //     delete_option( $this->option_name );
            //     $msg = 'deleted';
            // }
        }     

        wp_safe_redirect( get_home_url().'/wp-admin/admin.php?page=quickwp' );
        exit;
    }    
}