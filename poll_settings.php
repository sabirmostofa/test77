<?php

/*
Poll settings generate
 */

//set inital values
$post_id = $post->ID;


$d_settings = array( 
    'poll_set_width' => '300px',
    'poll_set_back' => '#FFFFFF' ,
    'poll_set_border' => '3px' ,
    'poll_set_border_color' => '#a9a9a9' ,
    'poll_set_pad' => '4px' ,
    'poll_set_margin' => '5px' ,   
    'poll_set_date' => '-1' ,
    'poll_set_title' => '0' ,
    'poll_set_blank' => '-1' ,
    'poll_submit_text' => 'Submit',
    'poll_align_center' => '1',
    'poll_is_mcq' => '0',
    'correct_option'=> '',
    'poll_mcq_correct' => 'Correct!',
    'poll_mcq_incorrect' => 'Incorrect!',   
    'poll_view_result_link'=> '0',
    'poll_set_message'=> '0',
    'poll_only_users' => '0'
     
);

$s_set = get_post_meta($post_id, 'pol_set',true);
//var_dump($s_set);

extract(wp_parse_args($s_set, $d_settings));

?>


<label for="poll_set_width">Width:</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_width]" id="poll_set_width" value='<?php echo $poll_set_width ?>' />


<label for="poll_set_back">Background color of poll</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_back]" id="poll_set_back" value='<?php echo $poll_set_back ?>' />
<div class='color_holder' id='poll_back_holder'> </div>

<label for="poll_set_border">Border:</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_border]" id="poll_set_border" value='<?php echo $poll_set_border ?>' />

<label for="poll_set_border_color">Border color</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_border_color]" id="poll_set_border_color" value='<?php echo $poll_set_border_color ?>' />
<div class='color_holder' id='poll_border_holder'> </div>

<label for="poll_set_pad">padding:</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_pad]" id="poll_set_pad" value='<?php echo $poll_set_pad ?>' />


<label for="poll_set_margin">Margin:</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_margin]" id="poll_set_margin" value='<?php echo $poll_set_margin ?>' />



<label for="poll_set_date">Poll end Date:</label>
<br/>
<input class='poll_settings' type="text" name="pol_set[poll_set_date]" id="poll_set_date" value='<?php echo $poll_set_date ?>' />

<p><input id="poll_set_title" type="checkbox" name="pol_set[poll_set_title]" value="1" <?php checked(1, $poll_set_title); ?>/> <label for="poll_set_title">Show title on top of Poll</label></p>

<p><input id="poll_set_blank" type="checkbox" name="pol_set[poll_set_blank]" value="1" <?php checked(1, $poll_set_blank); ?>/> <label for="poll_set_blank">Show poll in a blank page(without header, sidebar, footer)</label></p>


<p><input id="poll_only_users" type="checkbox" name="pol_set[poll_only_users]" value="1" <?php checked(1, $poll_only_users); ?>/> <label for="poll_only_users">Allow only registered users </label></p>


<?php



