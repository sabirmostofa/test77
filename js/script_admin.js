/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    //Common functions
    function getURLParameter(name) {
    return decodeURI(
        (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
    );
    }
    
    
    // codes for polls page
    if (pagenow == 'wppolls') {

        $('#add_poll_meta_box .hndle').hide();
        $('div#add_poll_meta_box').removeClass('postbox');
        var to_show = +PollsAdminVars.options_to_show;
        for (i = 1; i <= to_show; i++) {
            $('#poll_option_div_' + i).show();
        }

        $('#publish').click(function(evt) {
            alert('check');
            $('.wp-editor-area').each(function() {
                //alert($(this).text());
            })
            //evt.preventDefault();
            
            // remove the empty fields
            $('.poll_option_admin:hidden').each(function(){
                $(this).remove();
                
            })


        })

        //add option
        $('#add_option').click(function(evt) {
            evt.preventDefault();
            var v_items = $('.poll_option_admin:visible').length
            $('.poll_option_admin :eq('+ v_items + ')').show();


        })

        //remove option
        $('.delete_option').click(function(evt) {
            evt.preventDefault();
            var self = $(this);
            var id = self.parent().parent().attr('id');
            var post_id = getURLParameter(post);
            //alert(id);
            var item_nums = $('.poll_option_admin:visible').length;
            if (item_nums == 2){
                alert("There must be at least 2 items in a Poll");
                return;
            }
       
       
        //delete from database using ajax delete the html element on success
        //only do ajax if poll is being updated
        if(post_id !==  'wppolls')
        $.ajax({
            type: "post",
            url: ajaxurl,
            timeout: 5000,
            data: {
                'action': 'option_remove',
                'id': id,
                'post_id': post_id
            },
            success: function(data) {                
                    //alert(data);
                    self.parent().parent().fadeOut().remove();
                     //renumber the options
                    var i  = 0;
                    $('.option_number').each(function(){
                        i++;
                        $(this).text(i);
                
                
            })
                
            },
            error: function(data) {
               alert('Ajax failure, Unable to delete the option');
            }
        })//end of delete ajax
            
            


        })// end of remove option

    }//END OF POLLS PAGE





    $('.widefat img').bind('click', function(evt) {
        evt.preventDefault();
        var id = $(this).attr('class');

        var self = $(this);

        $.ajax({
            type: "post",
            url: ajaxurl,
            timeout: 5000,
            data: {
                'action': 'city_remove',
                'id': id
            },
            success: function(data) {
                if (data == 1) {
                    self.parent().parent().parent().hide('slow');
                }
            }
        })	//end of ajax	

    })
})
