<?php
/*
Plugin Name: Import data from import.io
Plugin URI:  https://github.com/wpan008/import-io-wordpress-plugin
Description: Import data from import.io using connectors defined.
Version:     0.1
Author:      Wei Pan
Author URI:  http://https://github.com/wpan008
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: import-io-data
*/
require_once(plugin_dir_path(__FILE__).'functions.php');

if ( is_admin() ) {
    // We are in admin mode
    
     
    function import_io_install() {

    }

    function import_io_deactivation(){
        wp_clear_scheduled_hook('import_io_run_event');
    }

    register_activation_hook( __FILE__, 'import_io_install' );

    register_deactivation_hook( __FILE__, 'import_io_deactivation' );
    
    function add_import_io_options_menu() {
        add_options_page(
                'import.io Options',
                'import.io',
                'manage_options',
                'import_io_options_menu',
                'import_io_options_page'
            );
        add_management_page(
                'import.io Runner', 
                'import.io', 
                'manage_options', 'import_io_tool_runner', 'import_io_tools_page');
    } 

    add_action('admin_menu', 'add_import_io_options_menu');
    
    /**
    * Register the settings
    */
    function import_io_settings() {
        add_settings_section('import_io_options', 'import io settings', 'import_io_settings_section_html', 'import_io_options_menu');
        add_settings_field('import_io_setting_user_guid', 
            __('User Guid','import_io'), 'import_io_settings_user_guid_html', 'import_io_options_menu', 'import_io_options');
        add_settings_field('import_io_setting_api_key', 
            __('API Key','import_io'), 'import_io_setting_api_key_html', 'import_io_options_menu', 'import_io_options');
        register_setting('import_io_options', 'import_io_setting_user_guid');
        register_setting('import_io_options', 'import_io_setting_api_key');
    }
    
    function import_io_settings_section_html(){
        
    }
    
    function import_io_settings_user_guid_html(){
        $setting = esc_attr( get_option( 'import_io_setting_user_guid' ) );
        echo "<input type='text' class='regular-text code' name='import_io_setting_user_guid' value='$setting' />";
    }
    
    function import_io_setting_api_key_html(){
        $setting = esc_attr( get_option( 'import_io_setting_api_key' ) );
        echo "<textarea type='text' class='regular-text code' name='import_io_setting_api_key' rows='10' cols='50'>$setting</textarea>";
    }
    
    add_action( 'admin_init', 'import_io_settings' );
    
    function import_io_options_page(){
        if ( ! isset( $_REQUEST['settings-updated'] ) )
            $_REQUEST['settings-updated'] = false; ?> 
    
        <div class="wrap">           
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>		   
            <div id="poststuff">
                <div id="post-body">
                        <div id="post-body-content">
                            <form method="post" action="options.php">
                                <?php settings_fields( 'import_io_options' ); ?>
                                <?php do_settings_sections( 'import_io_options_menu' ); ?>
                                <?php submit_button(); ?>
                            </form>
                        </div> <!-- end post-body-content -->
                </div> <!-- end post-body -->
            </div> <!-- end poststuff -->
        </div>
<?php 
    }

    function import_io_tools_page(){
        $defaultImportIoSitesOptions = array();
        $defaultImportIoSitesOptions[0] = array(
                    'import_io_site_name' => '华尔街见闻',
                    'import_io_site_urls' => 'http://wallstreetcn.com/',
                    'import_io_site_connector_guid' => 'a278ee6a-a1e5-4ec7-aca3-dcf7083e0427',
                    'import_io_page_connector_guid' => '814f1e47-de40-48f2-bbd5-aa9843370091',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => NULL,
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,新闻,经济,财经新闻"
        );
        $defaultImportIoSitesOptions[1] = array(
                    'import_io_site_name' => '新浪财经早报',
                    'import_io_site_urls' => 'http://finance.sina.com.cn/focus/xlcjzwb2014/',
                    'import_io_site_connector_guid' => '783cfec3-c788-41b7-bedd-ed37623edc95',
                    'import_io_page_connector_guid' => '6e99052a-873c-4815-b0a4-3778fda9f8c9',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => NULL,
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,新闻,早报,财经新闻"
        );
        $defaultImportIoSitesOptions[2] = array(
                    'import_io_site_name' => '21世纪经济报道',
                    'import_io_site_urls' => 'http://m.21jingji.com/',
                    'import_io_site_connector_guid' => 'b9926326-720d-4ec4-9794-574e1121f30f',
                    'import_io_page_connector_guid' => '5c98aadb-9002-420e-ab5b-5703ae9ac96a',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => NULL,
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "经济,新闻,经济新闻"
        );
        $defaultImportIoSitesOptions[3] = array(
                    'import_io_site_name' => '路透中国 国际财经',
                    'import_io_site_urls' => 'http://cn.reuters.com/news/internationalbusiness',
                    'import_io_site_connector_guid' => '00742d51-a259-40c5-b17b-359c7bdf62d6',
                    'import_io_page_connector_guid' => '7833b95a-71a8-419b-8801-95f64d479963',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => '?sp=true',
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,国际,国际财经"
        );
        $defaultImportIoSitesOptions[4] = array(
                    'import_io_site_name' => '路透中国 中国财经',
                    'import_io_site_urls' => 'http://cn.reuters.com/news/china',
                    'import_io_site_connector_guid' => '00742d51-a259-40c5-b17b-359c7bdf62d6',
                    'import_io_page_connector_guid' => '7833b95a-71a8-419b-8801-95f64d479963',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => '?sp=true',
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,中国财经"
        );
        $defaultImportIoSitesOptions[5] = array(
                    'import_io_site_name' => '路透中国 深度分析',
                    'import_io_site_urls' => 'http://cn.reuters.com/news/analyses',
                    'import_io_site_connector_guid' => '00742d51-a259-40c5-b17b-359c7bdf62d6',
                    'import_io_page_connector_guid' => '7833b95a-71a8-419b-8801-95f64d479963',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => '?sp=true',
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "分析,深度分析"
        );
        $defaultImportIoSitesOptions[6] = array(
                    'import_io_site_name' => '路透中国 财经视点',
                    'import_io_site_urls' => 'http://cn.reuters.com/news/opinions',
                    'import_io_site_connector_guid' => '00742d51-a259-40c5-b17b-359c7bdf62d6',
                    'import_io_page_connector_guid' => '7833b95a-71a8-419b-8801-95f64d479963',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => '?sp=true',
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,视点,财经视点"
        );
        $defaultImportIoSitesOptions[7] = array(
                    'import_io_site_name' => 'FT中文网',
                    'import_io_site_urls' => 'http://www.ftchinese.com/',
                    'import_io_site_connector_guid' => '5763a062-a46d-4c20-8b18-1031b5995a33',
                    'import_io_page_connector_guid' => '5ee4d0e9-01f7-4a07-9960-764c8999832c',
                    'import_io_author_id' => 2,
                    'import_io_site_full_page_args' => '?full=y',
                    'import_io_categories' => array(1,7),
                    'import_io_tags' => "财经,新闻,财经新闻"
        );
        
        $sitesImportIoOptions = get_option('import_io_sites', $defaultImportIoSitesOptions);
        
        if(isset($_POST['submit'])){            
            if(array_key_exists('import_io_site_name', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_site_name', $_POST['import_io_site_name'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_site_connector_guid', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_site_connector_guid', $_POST['import_io_site_connector_guid'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_site_urls', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_site_urls', $_POST['import_io_site_urls'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_page_connector_guid', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_page_connector_guid', $_POST['import_io_page_connector_guid'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_site_full_page_args', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_site_full_page_args', $_POST['import_io_site_full_page_args'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_author_id', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_author_id', $_POST['import_io_author_id'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_categories', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_categories', $_POST['import_io_categories'], $sitesImportIoOptions);    
            }
            if(array_key_exists('import_io_tags', $_POST)){
                $sitesImportIoOptions = import_io_update_array('import_io_tags', $_POST['import_io_tags'], $sitesImportIoOptions);    
            }
            update_option('import_io_sites', $sitesImportIoOptions);
            if($_POST['submit'] == __('Schedule', 'import_io')){
                if(!wp_next_scheduled('import_io_run_event')){
                    error_log("Cannot find import_io_run_event schedule it now");
                    wp_schedule_event(time(), 'hourly', 'import_io_run_event');	
                }else{
                    error_log("Event already scheduled before");
                }
            }else if($_POST['submit'] == __('Run', 'import_io')){
                import_io_runner();
            }
        }
        
        
        
?>
        <div class="wrap">           
            <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>		   
            <div id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <form method="post" action="">
<?php                        
        foreach($sitesImportIoOptions as $sitesImportIoOption){
            build_import_io_site($sitesImportIoOption);
        }
?>                        
                            <?php submit_button(__('Schedule', 'import_io')); ?>
                            <?php submit_button(__('Run', 'import_io')); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>	
<?php
       
        if(isset($_POST['submit'])){
            
        }
    }
    
    function import_io_update_array($key, $sourceArray, $targetArray){
        for ($i = 0; $i < sizeof($targetArray); $i++) {
            if(!is_null($targetArray[$i])){
                $targetArray[$i][$key] = $sourceArray[$i];
            }
        }
        return $targetArray;
    }
    
    function build_import_io_site($site){
?>		
        <table class="form-table">
                       
            <tr valign="top">
                <th scope="row"><?php _e('Site name', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_site_name[]" value="<?php esc_attr_e($site['import_io_site_name']); ?>" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Site Connector Guid', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_site_connector_guid[]" value="<?php esc_attr_e($site['import_io_site_connector_guid']); ?>" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Site Url', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_site_urls[]" value="<?php esc_attr_e($site['import_io_site_urls']); ?>" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Page Connector Guid', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_page_connector_guid[]" value="<?php esc_attr_e($site['import_io_page_connector_guid']); ?>" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Full page args', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_site_full_page_args[]" value="<?php esc_attr_e($site['import_io_site_full_page_args']); ?>" />
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Author', 'import_io'); ?></th>
                <td>
                    <?php wp_dropdown_users(array(
                    'name' => 'import_io_author_id[]',
                    'who' => 'author',
                    'selected' => $site['import_io_author_id']
                    )) ?>
                </td>
            </tr>
            
            <tr valign="top">
                <th scope="row"><?php _e('Categories', 'import_io'); ?></th>
                <td>
                    <?php wp_category_checklist(0, 0, $site['import_io_categories']); ?>
                </td>
            </tr>	
            
            <tr valign="top">
                <th scope="row"><?php _e('Tags', 'import_io'); ?></th>
                <td>
                    <input type="text" class="regular-text code" name="import_io_tags[]" value="<?php esc_attr_e($site['import_io_tags']); ?>" />
                </td>
            </tr>
        </table>			
<?php	
	}    
}

?>