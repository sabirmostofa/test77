<?php
/*
  File to add meta box in the new poll page
 */

//generate content
global $post, $wpdb;
$post_id = $post->ID;
$scr = get_current_screen();
$action = ($scr->action == 'add') ? 'add' : 'edit';
$img_delete = $this->image_dir . '/b_drop.png';
$content_question = $this->get_question_des($post_id);

if ($action == 'edit') {

    //active options
    $cur_opts = $this->get_active_options($post_id);  
   
    //generate contents for options
    
    foreach ($cur_opts as $op) {        
        $con = $this -> get_option_des($post_id, $op);
        $con_pre = "content_{$op}";
        $$con_pre = $con;
    }

    //generate extra contents
    $last_item = end($cur_opts);
    $limit = 21 - count($cur_opts);
    for ($i = $last_item + 1; $i < $limit; $i++) {
        $st = "content_{$i}";
        $$st = '';
    }
}


//when adding a poll all options are empty
if ($action == 'add')
    for ($i = 1; $i < 21; $i++) {
        $st = "content_{$i}";
        $$st = '';
    }
    
//var_dump($cur_opts);
?>
<p style="text-align: center"><span style = "float: left;padding-top: 5px;margin-right:10px" > Poll Shortcode: </span>

<input type="text" style="width:80%;border:2px black solid;display: block;padding:4px;margin:4px;text-align: center " name="" value='[super_poll poll_id="<?php echo $post_id; ?>" width="default" ]' readonly="readonly" onclick="select()" />
</p>
<!--<p  style="border:2px black solid;display: block;padding:4px;margin:4px;text-align: center " >
    [super_poll poll_id="<?php echo $post_id; ?>" width="default" ] 
</p>-->
<div style="clear:both"></div>

<h3>Question</h3>

<?php
//editor for the question
wp_editor($content_question, 'poll_question', array('textarea_rows' => 2));
echo "<br/>";
echo "<h2>Options</h2>";
//add 20 options at max
if ($action == 'add') {
    for ($i = 1; $i < 21; $i++) {
        echo "<div id='poll_option_div_{$i}' class='poll_option_admin' >";
        echo "<p><span style='font-size:1.4em;;margin-right: 20px'> #Option <span class='option_number'> {$i}</span> </span> <a title='Delete this option' class='delete_option' href='#' ><img src='$img_delete'> </a> </p>";
        $content = "content_{$i}";
        wp_editor($$content, "poll_option_{$i}", array('textarea_rows' => 1));
        echo "</div>";
    }
} else {



    //if edit poll generating content
    $edit_i=0;
    foreach ($cur_opts as $i) {
        $edit_i++;
        echo "<div id='poll_option_div_{$i}' class='poll_option_admin' >";
        echo "<p><span style='font-size:1.4em;;margin-right: 20px'> #Option <span class='option_number'> {$edit_i}</span> </span> <a title='Delete this option' class='delete_option' href='#' ><img src='$img_delete'> </a> </p>";
        $content = "content_{$i}";
        wp_editor($$content, "poll_option_{$i}", array('textarea_rows' => 1));
        echo "</div>";
    }


    //generate extra areas;

    for ($i = $last_item + 1; $i < $limit; $i++) {
        $edit_i++;
        echo "<div id='poll_option_div_{$i}' class='poll_option_admin' >";
        echo "<p><span style='font-size:1.4em;;margin-right: 20px'> #Option <span class='option_number'> {$edit_i}</span> </span> <a title='Delete this option' class='delete_option' href='#' ><img src='$img_delete'> </a> </p>";
        $content = "content_{$i}";
        wp_editor($$content, "poll_option_{$i}", array('textarea_rows' => 1));
        echo "</div>";
    }
}

//wp_editor($content,'option2', array('textarea_rows'=>5));
?>
<br/>
<a title="Add a new option" href="#" id="add_option" class="button-primary">Add option</a>

<?php
include 'poll_results.php';



