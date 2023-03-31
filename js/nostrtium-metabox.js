(function ($, window, document) {
  "use strict";
  // execute when the DOM is ready
  $(document).ready(function () {
    $("#nostrtium-post").on("click", function (e) {
      e.preventDefault();
      $(this).html("posting...");
      $(".modal").modal({
        fadeDuration: 250,
      });

      var note = $("#nostrtium-note").val();
      var data = {
        action: "pjv_nostrtium_post_note",
        note: note,
        post_id: nostrtium.post_id,
        security: nostrtium.security,
      };
      $.post(nostrtium.ajaxurl, data, function (response) {
        var button = $("#nostrtium-post");
        if (response.success) {
          button.html("POSTED");
          button.prop("disabled", true);
        } else {
          alert("Your note FAILED to post at all relays.");
          button.html("Post to Nostr");
          button.prop("disabled", false);
          button.blur();
        }
        $("#nostr-log").html(response.data);
      });
    });
  });
})(jQuery, window, document);
