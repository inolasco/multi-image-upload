function appendRow(theAttch) {

    if (!theAttch) {
        return;
    }

    var data = {
        id: theAttch.id,
        thumbnail: ((theAttch.sizes || {}).thumbnail || {}).url,
        filename: theAttch.filename,
        title: theAttch.title
    }

    var emptyRowTemplate = '<div id="row-miu-'+ data.id +'" style=\'margin-bottom: 20px;\'><img width="60" height="60" src="' + data.thumbnail + '" class="attachment-post-thumbnail" alt="'+data.filename+'" style=\'vertical-align: top;\' /><h4 style=\'display: inline-block;width: 180px;margin-left: 10px;\'>' + data.title + '</h4><a class="miu_remove_image_button" id="miu_row-'+ data.id +'">Remove</a><input type="hidden" name="miu_images[]" value="'+ encodeURIComponent(JSON.stringify(data)) +'" /></div>';

    jQuery('#miu_images').append(emptyRowTemplate);
}

jQuery(document).ready(function() {

    // Uploading files
    var file_frame;
    var miu_itemCount;

    jQuery('#miu_images').on('click', '.miu_remove_image_button', function(e) {
        e.preventDefault();

        var id = jQuery(this).attr("id");
        var btn = id.split("-");
        var img_id = btn[1];

        jQuery("#row-miu-" + img_id).remove();
    });

    jQuery('#miu_upload_image_button').on('click', function(event) {

        // Prevent the the default action of the event from being triggered.
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (file_frame) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery(this).data('uploader_title'),
            button: {
                text: jQuery(this).data('uploader_button_text'),
            },
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on('select', function() {
            // We set multiple to false so only get one image from the uploader
            var attachment = file_frame.state().get('selection').first().toJSON();

            if (attachment) {
                appendRow(attachment);
            }
        });

        // Finally, open the modal
        file_frame.open();
    });
});
