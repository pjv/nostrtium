(function ($, window, document) {
  "use strict";
  // execute when the DOM is ready
  $(document).ready(function () {
    var loading = true;

    function buildRelayTable() {
      var tbody = $("#relay-tbody");
      tbody.html("");
      $.each(nostrtium.relays, function (i, relay) {
        var tr = $("<tr>");
        $('<td class="collapsing">')
          .html('<i class="broadcast tower icon violet"></i>')
          .appendTo(tr);
        $(
          '<td style="overflow: hidden;text-overflow: ellipsis;width: 100%;max-width: 0;">'
        )
          .html(relay)
          .appendTo(tr);
        $('<td class="collapsing">')
          .html(
            '<i class="delete-relay minus circle icon red" index="' +
              i +
              '"></i>'
          )
          .appendTo(tr);
        tbody.append(tr);
      });
    }

    function saveRelays() {
      var data = {
        action: "pjv_nostrtium_save_relays",
        relays: nostrtium.relays,
        security: nostrtium.security,
      };
      $.post(nostrtium.ajaxurl, data, function (response) {
        if (response.success) {
          $("body").toast({
            class: "success",
            message: `Relays updated.`,
          });
        } else {
          alert(response.data);
        }
      });
    }

    function saveAutoPublishSettings() {
      if (loading) {
        return;
      }
      var apSettings = {
        autoPublish: $("#auto-publish").checkbox("is checked"),
        apExcerpt: $("#post-excerpt").checkbox("is checked"),
        apPermalink: $("#permalink").checkbox("is checked"),
        apWholePost: $("#whole-post").checkbox("is checked"),
      };
      var data = {
        action: "pjv_nostrtium_save_auto_publish",
        apSettings: apSettings,
        security: nostrtium.security,
      };
      $.post(nostrtium.ajaxurl, data, function (response) {
        if (response.success) {
          $("body").toast({
            class: "success",
            message: `Settings updated.`,
          });
        } else {
          alert(response.data);
        }
      });
    }

    function setupCheckboxes() {
      if (nostrtium.ap_settings.autoPublish) {
        $("#auto-publish").checkbox("check");
      } else {
        $("#auto-publish").checkbox("uncheck");
      }

      if (nostrtium.ap_settings.apExcerpt) {
        $("#post-excerpt").checkbox("check");
      } else {
        $("#post-excerpt").checkbox("uncheck");
      }

      if (nostrtium.ap_settings.apPermalink) {
        $("#permalink").checkbox("check");
      } else {
        $("#permalink").checkbox("uncheck");
      }
      if (nostrtium.ap_settings.apWholePost) {
        $("#whole_post").checkbox("check");
      } else {
        $("#whole_post").checkbox("uncheck");
      }
    }

    $(document).on("click", ".delete-relay", function (e) {
      console.log(e);
      var index = e.target.getAttribute("index");
      nostrtium.relays.splice(index, 1);
      saveRelays();
      buildRelayTable();
    });

    $("#new-relay-url").on("input", function () {
      var url = $(this).val();
      if (
        url.length > 6 &&
        (url.substring(0, 6) == "wss://" || url.substring(0, 5) == "ws://")
      ) {
        $("#add-relay").removeClass("disabled");
      } else {
        $("#add-relay").addClass("disabled");
      }
    });

    $("#add-relay").click(function () {
      var relay = $("#new-relay-url").val();
      var tbody = $("#relay-tbody");
      var tr = $("<tr>");
      var i = nostrtium.relays.length;

      $("<td>")
        .html('<i class="broadcast tower icon violet"></i>')
        .appendTo(tr);
      $("<td>").html(relay).appendTo(tr);
      $("<td>")
        .html(
          '<i class="delete-relay minus circle icon red" index="' + i + '"></i>'
        )
        .appendTo(tr);
      tbody.append(tr);
      $("#new-relay-url").val("");
      nostrtium.relays.push(relay);
      saveRelays();
    });

    $("#save-private-key").click(function () {
      if (nostrtium.private_key_set) return;
      $(this).addClass("loading");
      var data = {
        action: "pjv_nostrtium_save_private_key",
        nsec: $("#private-key").val(),
        security: nostrtium.security,
      };
      $.post(nostrtium.ajaxurl, data, function (response) {
        if (response.success) {
          $("body").toast({
            class: "success",
            message: `Private Key Saved.`,
          });
          $("#save-private-key")
            .removeClass("violet")
            .removeClass("loading")
            .addClass("green");
          $("#save-private-key")
            .children()
            .removeClass("save")
            .addClass("check");
          nostrtium.private_key_set = "1";
        } else {
          alert(response.data);
          $("#private-key").val("");
        }
        $("#save-private-key").removeClass("loading");
      });
    });

    $("#private-key").on("input", function () {
      if (nostrtium.private_key_set) {
        $(this).val("");
        nostrtium.private_key_set = "";
        $("#save-private-key").removeClass("green").addClass("violet");
        $("#save-private-key").children().removeClass("check").addClass("save");
      }
    });

    $("#auto-publish").checkbox({
      onChecked: function () {
        $("#auto-publish-fields").removeClass("disabled");
      },
      onUnchecked: function () {
        $("#auto-publish-fields").addClass("disabled");
      },
      onChange: function () {
        saveAutoPublishSettings();
      },
    });

    $(".ui.checkbox.ap").checkbox({
      onChange: function () {
        saveAutoPublishSettings();
      },
    });

    buildRelayTable();
    setupCheckboxes();
    loading = false;
  });
})(jQuery, window, document);
