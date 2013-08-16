<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//vars

$d_settings = array( 
    'poll_set_width' => '300px',
    'poll_set_back' => '#FFFFFF' ,
    'poll_set_border' => '1px' ,
    'poll_set_border_color' => '#000000' ,
    'poll_set_pad' => '4px' ,
    'poll_set_margin' => '5px' ,   
    'poll_set_date' => '-1' ,
    'poll_set_title' => '0' ,
    'poll_set_blank' => '-1' ,
    'poll_submit_text' => 'Submit'
    
 
);

$s_set = get_post_meta($post_id, 'pol_set',true);
//var_dump($s_set);

extract(wp_parse_args($s_set, $d_settings));
$poll_opts = $this->get_active_options($post_id);
//var_dump($poll_opts);

?>

<div class="poll_wrapper" style="
     width:<?php echo $poll_set_width ?>;
     background-color: <?php echo $poll_set_back ?>;
     border: <?php echo $poll_set_border ?>  solid   <?php echo $poll_set_border_color ?>;
     margin: <?php echo $poll_set_margin ?>;     
     padding: <?php echo $poll_set_pad ?>;     
     ">
    
    <?php if($poll_set_title){       
        
        echo '<div class="poll_title" style="text-align:center">'.  get_the_title($post_id) ,'</div>';       
    }
    ?>
    <?php echo $this->get_question_des($post_id); ?>
    
    <table class="poll_table">
        
        <?php foreach ($poll_opts as $op):
            $opt_des = $this -> get_option_des($post_id,$op);
            ?>
            <tr>
            <td>
            <input type="radio" name="poll-<?php echo $post_id; ?>" value="<?php echo $op; ?>" class="poll_option" /> 
            </td>
            <td>
            <?php echo $opt_des; ?>
            </td>
            </tr>
          <?php endforeach;   ?>
        
    </table>
    <div style="text-align:center">
        <input type="hidden" class="poll_id" name="" value="<?php echo $post_id ?>" />
    <button class="poll_submit" style="text-align">Submit</button>
    </div>
</div>


