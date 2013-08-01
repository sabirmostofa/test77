<?php

/*
File to add meta box in the new poll page
 */

//generate content
$img_delete=$this->image_dir . '/b_drop.png';
$content_question ='';
for($i=1;$i<21;$i++){
    $st = "content_{$i}";
    $$st='';
    
}

?>
<h3>Question</h3>

<?php
//editor for the question
wp_editor($content_question,'poll_question', array('textarea_rows'=>2));
echo "<br/>";
echo "<h2>Options</h2>";
//add 20 options at max
for($i=1;$i<21;$i++){
    echo "<div id='poll_option_div_{$i}' class='poll_option_admin' >";
    echo "<p><span style='font-size:1.4em;;margin-right: 20px'> #Option <span class='option_number'> {$i}</span> </span> <a title='Delete this option' class='delete_option' href='#' ><img src='$img_delete'> </a> </p>";
    $content = "content_{$i}";
wp_editor($$content,"poll_option_{$i}", array('textarea_rows'=>1));
echo "</div>";
}

//wp_editor($content,'option2', array('textarea_rows'=>5));
?>
<br/>
<a title="Add a new option" href="#" id="add_option" class="button-primary">Add option</a>



