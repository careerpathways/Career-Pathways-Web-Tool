$('document').ready(function(){
        $('div.checkbox_container input').click(function(){
                var el = $(this);
                var r_id = el.val().split('_')[0];
                var v_id = el.val().split('_')[1];
                var url = '/a/post_assurance_ajax.php';
                $.post( url, {requirement_id : r_id, view_id : v_id},
                function( data ) {
                        if(data.rsp){
                                //Updated okay, response returns true
                        } else {
                                //Broken, response returns false
                                alert ("Could not update the requirement, you most likely do not have permission.");
                        }                                       
                },
                'json'
        );
        })
});