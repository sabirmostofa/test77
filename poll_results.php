<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$countrs = $this -> get_countries($post_id);

$browsers = $this -> get_browsers($post_id);

$oss = $this -> get_oss($post_id);


$usrs = $this -> get_usrs($post_id);



$op_per = $this-> get_option_percentage($post_id);

var_dump($op_per);
?>

<div id="tabspoll" style="margin-top: 50px">
  <ul>
    <li><a href="#tabspoll-1">Country</a></li>
    <li><a href="#tabspoll-2">Operating System</a></li>
    <li><a href="#tabspoll-3">Browser</a></li>
    <li><a href="#tabspoll-4">Registered/Unregistered</a></li>
  </ul>
  <div id="tabspoll-1">
      <div id="highcontain"></div>      
      <?php $this -> output_javascript('#highcontain', __('Countries', 'wp-super-poll' ) , $countrs[2]); ?>
      Total votes: <?php echo $countrs[0]; ?>
      <br/>
<?php foreach($countrs[1] as $k=>$c): echo "$k:$c<br/>";  endforeach;  ?>
      </div>
    
    
  <div id="tabspoll-2">
                  <div id="oscontain"></div>    
           <?php $this -> output_javascript('#oscontain', __('Operating Systems', 'wp-super-poll' ) , $oss[2]); ?>
      Total votes: <?php echo $countrs[0]; ?>
      <br/>
<?php foreach($oss[1] as $k=>$c): echo "$k:$c<br/>";  endforeach;  ?>
    
  </div>
    
    
  <div id="tabspoll-3">      
  
      <div id="brcontain"></div>    
           <?php $this -> output_javascript('#brcontain', __('Browsers', 'wp-super-poll' ) , $browsers[2]); ?>
      Total votes: <?php echo $countrs[0]; ?>
      <br/>
<?php foreach($browsers[1] as $k=>$c): echo "$k:$c<br/>";  endforeach;  ?>
      </div>
    
  <div id="tabspoll-4">      
  
      <div id="regcontain"></div>    
           <?php $this -> output_javascript('#regcontain', __('Registed/Unregisterd', 'wp-super-poll' ) , $usrs[2]); ?>
      Total votes: <?php echo $usrs[0]; ?>
      <br/>
<?php foreach($usrs[1] as $k=>$c): echo "$k:$c<br/>";  endforeach;  ?>
      </div>
  
  
</div>