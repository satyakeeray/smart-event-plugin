jQuery(document).on( 'click', '.event-pagination a' , function(e) {
    e.preventDefault();
    let current_block_id = jQuery(this).closest('.event-listing-wrapper').data('block-id');
    let loader           = jQuery(".loader-" + current_block_id);
    let paged            = parseInt(jQuery(this).text());
    const eventContainer = jQuery(".event-listing-wrapper-"+ current_block_id + " #smart-event-container");
    let event_type       = eventContainer.data('event-type');
    let title            = eventContainer.data('title');
    let limit            = parseInt(eventContainer.data('limit'));
    let curent_page_attr = parseInt(eventContainer.data('page'));
    console.log(loader);
    console.log(current_block_id);
    if( jQuery(this).hasClass('prev')) {
        paged = curent_page_attr - 1
    }
     if( jQuery(this).hasClass('next')) {
        paged = curent_page_attr + 1
    }
    loader.show();
    jQuery(".event-listing-wrapper-" + current_block_id).hide();

    jQuery.ajax({
        url: smartEventAjax.ajax_url,
        type: 'POST',
        data: {
            action: 'smart_event_pagination',
            security: smartEventAjax.nonce,
            event_type: event_type,
            title: title,
            limit: limit,
            paged: paged,
            security: smartEventAjax.nonce
        },
        success: function(response) {
            jQuery(".event-listing-wrapper-" + current_block_id).html('');
            jQuery(".event-listing-wrapper-" + current_block_id).html(response);
            setTimeout(function(){
                loader.hide();
                jQuery(".event-listing-wrapper-" + current_block_id).show();
            }, 1000);
        }
    });
});
