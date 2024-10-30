/**
 * Created by Spencer on 8/11/2017.
 */

jQuery(document).ready(function() {
    /*** DASHBOARD SETTINGS ***/
        //* IMAGE "Upload" BUTTON
    jQuery('.upload_image_button').click(function(e) {
        //e.preventDefault();
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = jQuery(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            button.parent().prev().attr('src', attachment.url);
            button.prev().val(attachment.id);
            wp.media.editor.send.attachment = send_attachment_bkp;
        }
        wp.media.editor.open(button);
        return false;
    });

    //* IMAGE "Remove" BUTTON
    jQuery('.remove_image_button').click(function() {
        var answer = confirm('Are you sure?');
        if (answer == true) {
            var src = jQuery(this).parent().prev().attr('data-src');
            jQuery(this).parent().prev().attr('src', src);
            jQuery(this).prev().prev().val('');
        }
        return false;
    });
});