<h1 class="ui header">WP Nostr Settings</h1>

<div class="ui form">
  <div class="ten wide field">
    <div class="ui action labeled input">
      <div class="ui label">
        Private Key
      </div>

      <?php if ($this->encrypted_privkey != ''): ?>
      <input type="password" id="private-key" name="private-key" placeholder="nsec1..." value="Encrypted private key stored 101010101010101010101010101010101010">
      <button id="save-private-key" type="button" class="ui icon green button">
        <i class="check icon"></i>
      </button>
      <?php else :?>
      <input type="password" id="private-key" name="private-key" placeholder="nsec1...">
      <button id="save-private-key" type="button" class="ui icon violet button">
        <i class="save icon"></i>
      </button>
      <?php endif; ?>

    </div>
  </div>
</div>
<div class="ui hidden divider"></div>
<table class="ui collapsing celled striped table">
  <thead>
    <tr><th colspan="3">
    Relays
    </th>
  </tr></thead>
  <tbody id="relay-tbody">
  </tbody>
</table>
<div class="ui hidden divider"></div>

<div class="ui form">
  <div class="five wide field">
    <div class="ui action labeled input">
      <div class="ui label">
        Add Relay
      </div>
      <input type="text" id="new-relay-url" name="new-relay-url" placeholder="wss://...">
      <button id="add-relay" type="button" class="ui icon violet button disabled">
        <i class="save icon"></i>
      </button>
    </div>
  </div>
</div>