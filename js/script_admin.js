/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function($) {
    $( "#tabspoll" ).tabs();

    //settings function


    //Common functions
    function getURLParameter(name) {
        return decodeURI(
                (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search) || [, null])[1]
                );
    }
    //delete a poll option
    function delete_option(self) {
        self.parent().parent().fadeOut().remove();
        //renumber the options
        var i = 0;
        $('.option_number').each(function() {
            i++;
            $(this).text(i);
        })
    }


    // codes for polls page
    if (pagenow == 'wppolls') {

        //add or hide color pickers
        $('#poll_back_holder').farbtastic('#poll_set_back');
        $('#poll_back_holder').hide();

        $('#poll_border_holder').farbtastic('#poll_set_border_color');
        $('#poll_border_holder').hide();

        $('#poll_set_back').focus(function() {
            $('#poll_back_holder').show();
        })
        $('#poll_set_back').focusout(function() {
            $('#poll_back_holder').hide();
        })

        $('#poll_set_border_color').focus(function() {
            $('#poll_border_holder').show();
        })
        
        $('#poll_set_border_color').focusout(function() {
            $('#poll_border_holder').hide();
        })
        $('#poll_set_date').datepicker();

        $('#add_poll_meta_box .hndle').hide();
        $('div#add_poll_meta_box').removeClass('postbox');
        var to_show = PollsAdminVars.options_to_show;
        if (Object.prototype.toString.call(to_show) === '[object Array]' && to_show.length > 0) {
            for (i in to_show) {

                $('#poll_option_div_' + to_show[i]).show();

            }
        }

        else
            for (i = 1; i <= 2; i++) {
                $('#poll_option_div_' + i).show();
            }

        $('#publish').click(function(evt) {
            //alert('check');
            $('.wp-editor-area').each(function() {
                //alert($(this).text());
            });
            //evt.preventDefault();

            // remove the empty fields
            $('.poll_option_admin:hidden').each(function() {
                $(this).remove();

            });


        });

        //add option
        $('#add_option').click(function(evt) {
            evt.preventDefault();
            var v_items = $('.poll_option_admin:visible').length;
            $('.poll_option_admin :eq(' + v_items + ')').show();


        });

        //remove option
        $('.delete_option').click(function(evt) {
            evt.preventDefault();
            var self = $(this);
            var id = self.parent().parent().attr('id');
            var post_id = getURLParameter(post);
            //alert(id);
            var item_nums = $('.poll_option_admin:visible').length;
            if (item_nums == 2) {
                alert("There must be at least 2 items in a Poll");
                return;
            }


            //delete from database using ajax delete the html element on success
            //only do ajax if poll is being updated else only delete the other ids
            if (post_id !== 'wppolls')
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
                        delete_option(self);

                    },
                    error: function(data) {
                        alert('Ajax failure, Unable to delete the option');
                    }
                })//end of delete ajax
            else
                delete_option(self);




        });// end of remove option

    }
    ;//END OF POLLS PAGE





});

