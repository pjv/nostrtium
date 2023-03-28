(function ($, window, document) {
    'use strict';
    // execute when the DOM is ready
    $(document).ready(function () {
        $('#wpnostr-post').on('click', function (e) {
            e.preventDefault();
            $(this).html('posting...');

            $("#modal-overlay").removeClass('closed');
            $("#modal").removeClass('closed');
            $(".modal-guts").removeClass('closed');

            var note = $("#wpnostr-note").val();
            var data = {
              'action': 'pjv_wpn_post_note',
              'note': note,
              'post_id': wpnostr.post_id,
              'security': wpnostr.security
            }
            $.post(wpnostr.ajaxurl, data, function (response) {
              var button = $('#wpnostr-post');
              if (response.success) {
                button.html("POSTED");
                button.prop('disabled', true);
              } else {
                alert("Your note FAILED to post at all relays.");
                button.html("Post to Nostr");
                button.prop('disabled', false);
                button.blur();
              }
              $("#nostr-log").html(response.data);
              $("#close-button").removeClass('closed');
            });            
        });

        $("#close-button").click(function(e){
          e.preventDefault;
          $("#modal-overlay").addClass('closed');
          $("#modal").addClass('closed');
          $(".modal-guts").addClass('closed');
          $(this).addClass('closed');
        });

    });
}(jQuery, window, document));