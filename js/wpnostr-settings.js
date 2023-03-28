(function ($, window, document) {
  'use strict';
  // execute when the DOM is ready
  $(document).ready(function () {
    function buildRelayTable() {
      var tbody = $("#relay-tbody");
      tbody.html('');
      $.each(wpnostr.relays, function (i, relay) {
        var tr = $('<tr>');
        $('<td>').html('<i class="broadcast tower icon violet"></i>').appendTo(tr);
        $('<td>').html(relay).appendTo(tr);
        $('<td>').html('<i class="delete-relay minus circle icon red" index="' + i + '"></i>').appendTo(tr);
        tbody.append(tr);
      });
    }

    function saveRelays() {
      var data = {
        'action': 'pjv_wpn_save_relays',
        'relays': wpnostr.relays,
        'security': wpnostr.security
      }
      $.post(wpnostr.ajaxurl, data, function (response) {
        if (response.success) {
          $('body').toast({
            class: 'success',
            message: `Relays updated.`
          })
        } else {
          alert(response.data);
        }
      });
    }

    $(document).on("click", ".delete-relay", function (e) {
      console.log(e);
      var index = e.target.getAttribute('index');
      wpnostr.relays.splice(index, 1);
      saveRelays();
      buildRelayTable();
    });

    $("#new-relay-url").on('input', function () {
      var url = $(this).val();
      if (url.length > 6 && url.substring(0, 6) == 'wss://') {
        $("#add-relay").removeClass("disabled");
      }
    });

    $("#add-relay").click(function () {
      var relay = $("#new-relay-url").val();
      var tbody = $("#relay-tbody");
      var tr = $('<tr>');
      var i = wpnostr.relays.length;

      $('<td>').html('<i class="broadcast tower icon violet"></i>').appendTo(tr);
      $('<td>').html(relay).appendTo(tr);
      $('<td>').html('<i class="delete-relay minus circle icon red" index="' + i + '"></i>').appendTo(tr);
      tbody.append(tr);
      $("#new-relay-url").val('')
      wpnostr.relays.push(relay);
      saveRelays();
    });

    $("#save-private-key").click(function () {
      if (wpnostr.private_key_set) return;
      $(this).addClass('loading');
      var data = {
        'action': 'pjv_wpn_save_private_key',
        'nsec': $("#private-key").val(),
        'security': wpnostr.security
      }
      $.post(wpnostr.ajaxurl, data, function (response) {
        if (response.success) {
          $('body').toast({
            class: 'success',
            message: `Private Key Saved.`
          });
          $("#save-private-key").removeClass('violet').removeClass('loading').addClass('green');
          $("#save-private-key").children().removeClass('save').addClass('check');
          wpnostr.private_key_set = '1';
        } else {
          alert(response.data);
          $("#private-key").val('')
        }
        $("#save-private-key").removeClass('loading');
      });
    });

    $("#private-key").on('input', function () {
      if (wpnostr.private_key_set) {
        $(this).val('');
        wpnostr.private_key_set = '';
        $("#save-private-key").removeClass('green').addClass('violet');
        $("#save-private-key").children().removeClass('check').addClass('save');
      }
    });

    buildRelayTable();
  });
}(jQuery, window, document));