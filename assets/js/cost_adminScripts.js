jQuery(document).ready(function(){


    // admin generate idea
    jQuery(document).on("submit", "#create_cost_range", function(e){
        e.preventDefault();
        let formdata = new FormData(this);
        formdata.append('action','admin_create_cost_range_action');
        jQuery.ajax({
            type: "post",
            data : formdata,
            url: opt.ajaxUrl,
            success: function(msg){
                let split_msg = msg.split("|");
                if(split_msg[0] == "success"){
                    location.reload();
                }
                else{
                    alert(split_msg[1]);
                    location.reload();
                }
                // console.log(msg)
                
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });





    // admin delete range
    jQuery(document).on("click", ".delete_range", function(e){
        e.preventDefault();
        var result = confirm("Confirm delete this range?");
        if (result) {
            let formdata = new FormData();
            let range_id = jQuery(this).attr('data-id');
            formdata.append('action','admin_delete_range_action');
            formdata.append('range_id', range_id);
            jQuery.ajax({
                type: "post",
                data : formdata,
                url: opt.ajaxUrl,
                success: function(msg){
                    let split_msg = msg.split("|");
                    if(split_msg[0] == "success"){
                        location.reload();
                    }
                    else{
                        alert(split_msg[1]);
                        location.reload();
                    }
                    
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
        else{
            return;
        }
        
    });




});