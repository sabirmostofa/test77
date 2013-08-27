jQuery(document).ready(function($){
    
    function send_ajax(id,opt, cooks){
        
            $.ajax({
            type :  "post",
            url : PollsFrontVars.ajaxurl,
            timeout : 5000,
           
            data : {
                'action' : 'poll_submit_result',
                'poll_id' : id,
                'op_id' : opt,
                'ip':  PollsFrontVars.ip,
                'browser':  PollsFrontVars.browser,
                'platform':  PollsFrontVars.platform
            
            },
            success :  function(data){
                if(data == 'end'){
                    alert('Poll submission is over');
                    return;
                }
                
                if(data == 'only-user'){
                    alert('Please login or sign up to vote');
                    return;
                }
                alert("Thanks you for your vote!");
                $.cookie('wp_poll_cookies', cooks , { expires: 10000, path: '/' });
            }
            
            })
    }
    
    //poll submit click action //check if cookie exists, send ajax request, set
    //set cookie on success
    $('.poll_submit').click(function(e){ 
        var poll_id = $(this).prev().val() ;
        var opt_checked = $("input[name='poll-"+ poll_id + "']:checked").val();
        if(opt_checked === undefined){
            alert("You must have to select a option to vote");
            return;
        }
        
        var all_cookies = $.cookie('wp_poll_cookies');
        if(all_cookies === undefined){
            var cooks = new Array();
            cooks[0] = poll_id;
            var js_cooks = JSON.stringify(cooks);
            send_ajax(poll_id, opt_checked, js_cooks);  
        }
        else{
           var cooks = JSON.parse(all_cookies);
           
           if(cooks.indexOf(poll_id) == -1){
               alert('in');
              var len = cooks.length;
              cooks[len]= poll_id;
              var js_cooks = JSON.stringify(cooks);
              alert(js_cooks);
              send_ajax(poll_id, opt_checked, js_cooks);
           }else{
               
               alert("you've already voted for this Poll!");
           }
        }
    });
    
    
    //test
    $('#show-image-button').click(function(e){        
            $.ajax({
            type :  "post",
            url : wpvrSettings.ajaxurl,
            timeout : 5000,
            dataType : 'json',
            data : {
                'action' : 'get_image_src',
                'post_id':  wpvrSettings.post_id
            
            },
            success :  function(data){
                $('#show-image-button').hide();
                $('#show-var-image').text(data.data).hide().fadeIn('slow');
            }
            
    } )
    
    });
    
    var grades_val= new Array('Awesome!', 'Pretty Good', 'Just Ok', 'Pretty Lame', 'Worthless' );
       var common ={
           updating: false,
           gray_all: function(){
            $('.rate-grade-image a img').each(function(){
                var src =  $(this).attr('src');
                var src_color =  src.replace('color','gray');
                if( $(this).attr('src').match('color'))
                    $(this).attr('src', src_color);
                $(this).attr('id',null);
            });

           },
           color_src: function(grade){
                $('.rate-grade-image a img').each(function(){
                var src =  $(this).attr('src');
                var selVal =$(this).attr('class').match(/\d/);
                if(selVal[0] == grade){
                var src_color =  src.replace('gray','color');               
                    $(this).attr('src', src_color);
                    $(this).attr('id','user-grade');
                }
                });
               
           }
       }
       
       //initialize variable
            $('.rate-grade-image a img').each(function(){
            if( $(this).attr('src').match('color'))                 
                    common.updating = true;              
            
        })
   
    
    $('.rate-grade-image a img').mouseover(function(e){
        var elem = $(this);
        e.stopPropagation();
        var src = elem.attr('src');
//        var image = src.match(/\/[^\/]*\.png/);
//       alert(src.replace(/\/[^\/]*\.png/,''));
       var new_image= src.replace('gray','color');
       elem.attr('src', new_image);
       var selVal =$(this).attr('class').match(/\d/);
       $('.grade-text-value').html(grades_val[selVal[0]]);
        
    });
    $('.rate-grade-image a img').mouseout(function(e){
        var elem = $(this);
        e.stopPropagation();
        var src = elem.attr('src');
       var new_image= src.replace('color','gray');

       if($(this).attr('id') != 'user-grade')
            elem.attr('src', new_image);
        $('.grade-text-value').html(null);
        
    });

    
    $('.rate-grade-image a img').click(function(e){
        e.preventDefault();
        var action = 'submit-wpvote';
       var hovered_img = $(this).attr('src'); 
        
        var selVal =$(this).attr('class').match(/\d/);
        var to_update = false;
        $('.rate-grade-image a img').each(function(){
            if( $(this).attr('src').match('color')) 
                if($(this).attr('src') != hovered_img)
                    to_update = true;
                
            
        })
       
//        alert(wpvrSettings.post_id );
      var ans = true;
      if(common.updating)
           ans = confirm("Are you sure you want to update the grade?");
       
        if(ans == false) return;
        $.ajax({
            type :  "post",
            url : wpvrSettings.ajaxurl,
            timeout : 5000,
            dataType: 'json',
            data : {
                'action' : action,
                'grade-value': selVal[0],
                 'post_id':  wpvrSettings.post_id  
            },
            success :  function(data){
//                $('#colophon').html(data);    
//               alert(data);
                 if(data.action == 'none'){
                     alert("You need to log in to grade this topic");
                        window.location.href = data.login;
                        return;
             }
                switch(data.action){                  
                    case 'added':
                      common.gray_all();
                      common.color_src(data.grade);
                      common.updating = true;
                      $('#grade-users-count').text(data.count);
                      $('#av-grade-image').attr('src', data.image);
                     break;
                    case 'updated':
                        common.gray_all();
                        common.color_src(data.grade);
                       $('#grade-users-count').text(data.count);
                      $('#av-grade-image').attr('src', data.image);
                     break;

                }
//                   if(data == 'voted' )alert('You have already voted for this post');
//                   if(data == 'nv')alert('Your Rating has been saved for this post');
//                    if(data == 'updated')alert('Your Rating has been Updated for this post');
                   // window.location.href=window.location.href;
                    
                    }//end ajax success
            })//end ajax
        })//end click logic
})