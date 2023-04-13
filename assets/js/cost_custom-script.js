jQuery(document).ready(function(){
    // console.log(opt.sale_price);

    var cost_range = [];
    var regular_price = [];
    var sale_price = [];

    let formdata = new FormData();
    formdata.append('action','get_cost_range_action');
    jQuery.ajax({
        type: "post",
        data : formdata,
        dataType: 'json',
        url: opt.ajaxUrl,
        success: function(msg){
            cost_range = msg[0];
            regular_price = msg[1];
            sale_price = msg[2]; 
            
            jQuery(".cc_budget_label").text(cost_range[0]);
            jQuery(".cc_crossed_price ").html('<s class="cc_cut_price">$'+regular_price[0]+'</s>');
            jQuery(".cc_sale_price").text("$"+sale_price[0]+"/mo");
        },
        cache: false,
        contentType: false,
        processData: false
    });

    
    jQuery(document).on("change", "#cc_range", function(){
        let range_value = jQuery(this).val();
        jQuery(".cc_budget_label").text(cost_range[range_value]);
        if(regular_price[range_value] != ""){
            jQuery(".cc_crossed_price ").html('<s class="cc_cut_price">$'+regular_price[range_value]+'</s>');
            jQuery(".cc_sale_price").text("$"+sale_price[range_value]+"/mo");
        }else{
            jQuery(".cc_crossed_price ").html(" ");
            jQuery(".cc_sale_price").text("Contact Us");
        }
        
    });


});