<?php

/*
  Plugin Name: WP-Super-Polls
  Plugin URI: http://top-wpp.com/
  Description: Create Amazing Polls and show the results or analyze the polls
  Version: 1.0
  Author: Sabirul Mostofa
  Author URI: http://top-wpp.com/
 */

//include 'featured-post-widget.php';
$wpSuperPolls = new wpSuperPolls();

class wpSuperPolls {

    const ip_ranges_table_suffix = 'polls_ip_ranges';
    const db_filename = 'ip2country.db';

    public $table_que = '';
    public $table_opt = '';
    public $table_ans = '';
    public $image_dir = '';
    public $meta_box = array();

    function __construct() {
        global $wpdb;
        //$this->set_meta();
        $this->table_que = $wpdb->prefix . 'super_polls_ques';
        $this->table_opt = $wpdb->prefix . 'super_polls_opts';
        $this->table_ans = $wpdb->prefix . 'super_polls_ans';
        $this->image_dir = plugins_url('/', __FILE__) . 'images/';
        add_action('init', array($this, 'add_custom_poll'));
        add_action('save_post', array($this, 'save_custom_poll'));
        add_action('before_delete_post', array($this, 'delete_poll_action'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
        add_action('add_meta_boxes', array($this, 'polls_meta_boxes'));
        add_action('wp_print_styles', array($this, 'front_css'));
        add_action('admin_menu', array($this, 'CreateMenu'), 50);
        add_filter('post_updated_messages', array($this, 'polls_updated_messages'));
        // add_action('wp_rental_cron', array($this, 'start_cron'));
        add_action('wp_ajax_option_remove', array($this, 'ajax_remove_option'));
        register_activation_hook(__FILE__, array($this, 'create_table'));
        // register_activation_hook(__FILE__, array($this, 'init_cron'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation_tasks'));
    }

    function CreateMenu() {
        add_submenu_page('options-general.php', 'Polls Settings', 'Polls Settings', 'activate_plugins', 'wpSuperPolls', array($this, 'OptionsPage'));
    }

    function OptionsPage() {
        include 'options-page.php';
    }

    //adding post type
    function add_custom_poll() {
        $labels = array(
            'name' => _x('Polls', 'post type general name'),
            'singular_name' => _x('Poll', 'post type singular name'),
            'add_new' => _x('Add New', 'poll'),
            'add_new_item' => __('Add New Poll'),
            'edit_item' => __('Edit Poll'),
            'new_item' => __('New Poll'),
            'all_items' => __('All Poll'),
            'view_item' => __('View Poll'),
            'search_items' => __('Search Polls'),
            'not_found' => __('No polls found'),
            'not_found_in_trash' => __('No polls found in the Trash'),
            'parent_item_colon' => '',
            'menu_name' => 'Polls'
        );
        $args = array(
            'labels' => $labels,
            'description' => 'All Polls',
            'public' => true,
            'menu_position' => 20,
            'supports' => array('title'),
            'has_archive' => false
        );
        register_post_type('wppolls', $args);
    }

    function save_custom_poll($post_id) {
        global $wpdb;

        //save post hook is called also in a new post/poll creation

        if (!array_key_exists('poll_question', $_POST))
            return;

        //insert question or update
        if ($this->id_in_db('parent_id', $post_id, $this->table_que))
            $wpdb->update(
                    $this->table_que, array(
                'parent_id' => $post_id,
                'question' => $_POST['poll_question'],
                'type' => 'poll',
                    ), array('parent_id' => $post_id)
                    , array(
                '%d',
                '%s',
                '%s'
                    )
            );
        else
            $wpdb->insert(
                    $this->table_que, array(
                'parent_id' => $post_id,
                'question' => $_POST['poll_question'],
                'type' => 'poll',
                    )
                    , array(
                '%d',
                '%s',
                '%s'
                    )
            );


        //insert options or update
        $key_ar = array_keys($_POST);
        $active_opts = array();
        foreach ($key_ar as $key) {
            if (stripos($key, 'poll_option') !== false) {
                $option_id = (int) preg_replace("/[^0-9]/", "", $key);
                $active_opts[] = $option_id;

                //if exists update or insert
                var_dump($option_id);


                if ($this->vars_in_db('ques_id', $post_id, 'opt_id', $option_id, $this->table_opt)) {
                    $wpdb->update(
                            $this->table_opt, array(
                        'ques_id' => $post_id,
                        'opt_id' => $option_id,
                        'description' => $_POST[$key],
                            ), array(
                        'ques_id' => $post_id,
                        'opt_id' => $option_id
                            )
                            , array(
                        '%d',
                        '%d',
                        '%s'
                            )
                    );
                } else {
                    //exit('trying');
                    $wpdb->insert(
                            $this->table_opt, array(
                        'ques_id' => $post_id,
                        'opt_id' => $option_id,
                        'description' => $_POST[$key],
                            )
                            , array(
                        '%d',
                        '%d',
                        '%s'
                            )
                    );
                }
            }
        }//endforaeach
        // exit;
        //remove the old ones

        $db_opts = $this->get_active_options($post_id); 
        var_dump($db_opts);
        $cur_opts = array();
        foreach ($active_opts as $value) {
            $cur_opts[] = (int) $value;
        }
        $to_delete = array_diff($db_opts, $cur_opts);
        foreach ($to_delete as $opt_id) {
            $this->delete_poll_option($post_id, $opt_id);
        }
    }

    function delete_poll_action($postid) {
        global $post_type, $wpdb;
        if ($post_type != 'wppolls')
            return;


        //delete answers
        $wpdb->query("");

        //question
        $wpdb->delete(
                $this->table_que, array('parent_id' => $postid)
        );

        //options
        $wpdb->delete(
                $this->table_opt, array('ques_id' => $postid)
        );
    }

    function polls_updated_messages($messages) {
        global $post, $post_ID;

        $messages['wppolls'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(__('Poll updated. <a href="%s">View Poll</a>', 'your_text_domain'), esc_url(get_permalink($post_ID))),
            2 => __('Custom field updated.', 'your_text_domain'),
            3 => __('Custom field deleted.', 'your_text_domain'),
            4 => __('Poll updated.', 'your_text_domain'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Poll restored to revision from %s', 'your_text_domain'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => sprintf(__('Poll published. <a href="%s">View Poll</a>', 'your_text_domain'), esc_url(get_permalink($post_ID))),
            7 => __('Poll saved.', 'your_text_domain'),
            8 => sprintf(__('Poll submitted. <a target="_blank" href="%s">Preview Poll</a>', 'your_text_domain'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
            9 => sprintf(__('Poll scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Poll</a>', 'your_text_domain'),
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
            10 => sprintf(__('Poll draft updated. <a target="_blank" href="%s">Preview Poll</a>', 'your_text_domain'), esc_url(add_query_arg('preview', 'true', get_permalink($post_ID)))),
        );

        return $messages;
    }

    //meta box
    function polls_meta_boxes() {

        global $_wp_post_type_features;
        //ive defined my other metaboxes first with higher priority
        add_meta_box(
                $id = 'add_poll_meta_box', $title = __('Question'), $callback = array(&$this, 'render_poll_metabox'), $post_type = 'wppolls', $context = 'normal', $priority = 'high'
        );
        //add_meta_box(
        //      $id = 'page_description_meta_box', $title = __('Answers'), $callback = array(&$this, 'render_description_metabox'), $post_type = 'wppolls', $context = 'normal', $priority = 'high'
        // );
        //check for the required post type page or post or <custom post type(here article)  
        /*
          if (isset($_wp_post_type_features['wppolls']['editor']) && $_wp_post_type_features['post']['editor']) {
          unset($_wp_post_type_features['wppolls']['editor']);
          add_meta_box(
          'wsp_content',
          __('Content'),
          array(&$this,'content_editor_meta_box'),
          'article', 'normal', 'core'
          );
          }
          if (isset($_wp_post_type_features['page']['editor']) && $_wp_post_type_features['page']['editor']) {
          unset($_wp_post_type_features['page']['editor']);
          add_meta_box(
          'wsp_content',
          __('Content'),
          array(&$this,'content_editor_meta_box'),
          'page', 'normal', 'low'
          );
          }
         * 
         */
    }

    //metaboxes
    function render_poll_metabox() {

        include 'poll_metabox.php';
    }

    //
    function render_heading_metabox($post) {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'wppolls_nonce_posts');

        // The actual fields for data entry
        // Use get_post_meta to retrieve an existing value from the database and use the value for the form
        $value = get_post_meta($post->ID, '_my_meta_value_key', true);
        echo '<label for="myplugin_new_field">';
        _e("Description for this field", 'myplugin_textdomain');
        echo '</label> ';
        echo '<input type="text" id="myplugin_new_field" name="myplugin_new_field" value="' . esc_attr($value) . '" size="25" />';
    }

    function render_description_metabox($post) {
        // Use nonce for verification
        wp_nonce_field(plugin_basename(__FILE__), 'wppolls_nonce_posts');

        // The actual fields for data entry
        // Use get_post_meta to retrieve an existing value from the database and use the value for the form
        $value = get_post_meta($post->ID, '_my_meta_value_key', true);
        echo '<button id="add_new_ques">Add new Question</button>
 <textarea class="theEditor"></textarea>      
';
    }

    //custom editor
    function content_editor_meta_box($post) {
        $settings = array(
            #media_buttons
            #(boolean) (optional) Whether to display media insert/upload buttons
            #Default: true
            'media_buttons' => true,
            #textarea_name
            #(string) (optional) The name assigned to the generated textarea and passed parameter when the form is submitted. (may include [] to pass data as array)
            #Default: $editor_id
            'textarea_name' => 'content',
            #textarea_rows
            #(integer) (optional) The number of rows to display for the textarea
            #Default: get_option('default_post_edit_rows', 10)
            #tabindex
            #(integer) (optional) The tabindex value used for the form field
            #Default: None
            'tabindex' => '4'

                #editor_css
                #(string) (optional) Additional CSS styling applied for both visual and HTML editors buttons, needs to #include <style> tags, can use "scoped"
                #Default: None
                #editor_class
                #(string) (optional) Any extra CSS Classes to append to the Editor textarea
                #Default:
                #teeny
                #(boolean) (optional) Whether to output the minimal editor configuration used in PressThis
                #Default: false
                #dfw
                #(boolean) (optional) Whether to replace the default fullscreen editor with DFW (needs specific DOM elements #and css)
                #Default: false
                #tinymce
                #(array) (optional) Load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
                #Default: true
                #quicktags
                #(array) (optional) Load Quicktags, can be used to pass settings directly to Quicktags using an array()
                #Default: true
        );
        wp_editor($post->post_content, 'content');
    }

    function admin_scripts() {
        global $typenow, $post;
        if ($typenow == "wppolls") {

            // wp_enqueue_script('wppl_bootstrap_script', plugins_url('/', __FILE__) . 'bootstrap/js/bootstrap.min.js');
            //wp_register_style('wppl_bootstrap_css', plugins_url('/', __FILE__) . 'bootstrap/css/bootstrap.min.css', false, '1.0.0');
            //wp_enqueue_style('wppl_bootstrap_css');


            wp_enqueue_script('wppl_admin_script', plugins_url('/', __FILE__) . 'js/script_admin.js');
            wp_register_style('wppl_admin_css', plugins_url('/', __FILE__) . 'css/style_admin.css', false, '1.0.0');
            wp_enqueue_style('wppl_admin_css');

            //set javascript vars only if post varrs are available
            if(isset($post)){
            $params = array(
                'options_to_show' => $this->options_to_show($post->ID),
                'post_id' => $post->ID
            );
            wp_localize_script('wppl_admin_script', 'PollsAdminVars', $params);
            }
        }
    }

    function front_scripts() {
        global $post;
        if (is_page() || is_single()) {
            wp_enqueue_script('jquery');
            if (!(is_admin())) {
                // wp_enqueue_script('wpvr_boxy_script', plugins_url('/' , __FILE__).'js/boxy/src/javascripts/jquery.boxy.js');
                wp_enqueue_script('wpp_front_script', plugins_url('/', __FILE__) . 'js/script_front.js');
            }
        }
    }

    function front_css() {
        if (!(is_admin())):
            wp_enqueue_style('wpvr_front_css', plugins_url('/', __FILE__) . 'css/style_front.css');
        endif;
    }

    function not_in_table($city) {
        global $wpdb;
        $var = $wpdb->get_var("select city_url from $this->table where city_name='$city'");
        if ($var == null)
            return true;
    }

    function create_table() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_que  (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,		
		`parent_id` bigint(20) unsigned NOT NULL,		
		`question` text  NOT NULL,
                `is_active` TINYINT(1) default 1,
                `type` varchar(6) not null,	
		 PRIMARY KEY (`id`)			 	
		)";

        $sql1 = "CREATE TABLE IF NOT EXISTS $this->table_opt  (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,		
		`ques_id` bigint(20) unsigned NOT NULL,		
		`opt_id` int(3) unsigned NOT NULL,		
		`description` text  NOT NULL,		
		 PRIMARY KEY (`id`)		 	
		)";

        $sql2 = "CREATE TABLE IF NOT EXISTS $this->table_ans  (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,		
		`ques_id` bigint(20) unsigned NOT NULL,
		`option_id` bigint(20) unsigned NOT NULL,
                `ip` varchar(16) not null default '',
                `is_user` TINYINT(1) default 0,
                `browser` varchar(60) default '',
                `os` varchar(40) default '',
                `unique_val` varchar(40) not null,
		 PRIMARY KEY (`id`)		 	
		)";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
        dbDelta($sql1);
        dbDelta($sql2);
    }

// end of create_table
    //create table for geolocation
    protected function update_db() {


        global $wpdb;
        $ip_ranges_table_name = $wpdb->prefix . self::ip_ranges_table_suffix;
        $countries_table_name = $wpdb->prefix . self::countries_table_suffix;
        $wpdb->query('DROP TABLE IF EXISTS ' . $ip_ranges_table_name . ';');
        $wpdb->query('DROP TABLE IF EXISTS ' . $countries_table_name . ';');

        $sql_countries = 'CREATE TABLE ' . $countries_table_name . ' (
        cid INT(4) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code CHAR(2) NOT NULL,
        name VARCHAR(150) NOT NULL,
        latitude FLOAT NOT NULL,
        longitude FLOAT NOT NULL) ENGINE=MyISAM DEFAULT CHARACTER SET utf8, COLLATE utf8_general_ci;';

        $sql_ip_ranges = 'CREATE TABLE ' . $ip_ranges_table_name . ' (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fromip INT(10) UNSIGNED NOT NULL,
        toip INT(10) UNSIGNED NOT NULL,
        cid INT(4) UNSIGNED NOT NULL,
        INDEX (fromip ASC, toip ASC, cid ASC)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8, COLLATE utf8_general_ci;';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_countries);
        dbDelta($sql_ip_ranges);

        require_once(dirname(__FILE__) . '/iso-3166-2.php');

        $sql = '';
        foreach ($country_data as $code => $code_data) {
            $sql .= '(' . $code_data['cid'] . ', "' . $code . '", "' . $code_data['name'] . '", ' . $code_data['latitude'] . ', ' . $code_data['longitude'] . '), ';
        }
        $wpdb->query('INSERT INTO ' . $countries_table_name . ' (cid, code, name, latitude, longitude) VALUES ' . substr($sql, 0, -2));

        $limit_no_insert = 1000;
        $counter = 0;
        $sql = '';
        if (($input = fopen($this->db_file, 'r')) !== false) {
            while (($file_data = fgetcsv($input, 1000, ' ')) !== false) {
                if (isset($country_data[$file_data[2]])) {
                    $counter++;
                    $sql .= '(' . $file_data[0] . ', ' . $file_data[1] . ', ' . $country_data[$file_data[2]]['cid'] . '), ';

                    if ($counter == $limit_no_insert) {
                        $wpdb->query('INSERT INTO ' . $ip_ranges_table_name . ' (fromip, toip, cid) VALUES ' . substr($sql, 0, -2));
                        $counter = 0;
                        $sql = '';
                    }
                }
            }
            $wpdb->query('INSERT INTO ' . $ip_ranges_table_name . ' (fromip, toip, cid) VALUES ' . substr($sql, 0, -2));
            fclose($input);
        } else {
            throw new Exception(__('Couldn\'t read Quick Flag database file from local file system. Please check permissions.', self::slug), 6);
        }
    }

    function not_inserted_before($id, $city) {
        global $wpdb;
        $in = $wpdb->get_var("select post_id from $this->table_data where cg_id=$id and city_id=$city");
        if ($in == null)
            return true;
    }

    function deactivation_tasks() {

        wp_clear_scheduled_hook('wp_rental_cron');
    }

    //options in db
    function options_to_show($post_id) {
        global $typenow, $wpdb;
        //exit($pagenow);
        if ($typenow != 'wppolls')
            return 1;
        return $this->get_active_options($post_id);
    }

    //database functions
    function get_question_num($poll_id) {
        global $wpdb;
    }

    function id_in_db($key, $val, $table) {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
                                "
		SELECT `id`
		FROM $table
		WHERE $key=%d
	", $val));
    }

    function var_in_db($key, $table, $where = array(), $type = '%d') {
        global $wpdb;
        $whr = key($where);

        return $wpdb->get_var($wpdb->prepare(
                                "
		SELECT $key
		FROM $table
		WHERe $whr = $type
	", $where[$whr]));
    }

    function vars_in_db($key, $val, $key1, $val1, $table) {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
                                "
		SELECT `id`
		FROM $table
		WHERE $key=%d and
                      $key1=%d
	", $val, $val1));
    }

    function get_question_des($id) {
        global $wpdb;
        $des = $wpdb->get_var($wpdb->prepare(
                        "
		SELECT `question`
                FROM $this->table_que
		WHERE parent_id=%d 
                      
	", $id));

        return ($des) ? $des : '';
    }

    function get_option_des($poll_id, $opt_id) {
        global $wpdb;
        return $wpdb->get_var(
                        "
                    select description
                    from $this->table_opt
                    where 
                    ques_id = $poll_id
                    and
                    opt_id = $opt_id
                 "
        );
    }

    //returns active options for the poll as an sorted array without keys
    function get_active_options($poll_id) {
        global $wpdb;
        $db_opts = $wpdb->get_col($wpdb->prepare(
                                "
                    select opt_id 
                    from $this->table_opt
                    where ques_id = %d
                ", $poll_id
        ));

        $cur_opts = array();
        foreach ($db_opts as $value) {
            $cur_opts[] = (int) $value;
        }

        sort($cur_opts);
        return $cur_opts;
    }

    function delete_poll_option($post_id, $opt_id) {
        global $wpdb;
        return $wpdb->delete($this->table_opt, array('ques_id' => $post_id, 'opt_id' => $opt_id));

        //delete related answers too
    }

    // ajax functions
    function ajax_remove_option() {
        global $wpdb;
        $post_id = $_POST['post_id'];
        $opt_id = (int) preg_replace("/[^0-9]/", "", $_POST['id']);
        echo $this->delete_poll_option($post_id, $opt_id);




        exit();
    }

}
