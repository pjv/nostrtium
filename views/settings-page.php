<div class="wrap ">
  <h1 class="ui header">Nostrtium Settings</h1>

  <div class="ui stackable grid container">
    <div class="row">
      <div class="sixteen wide column">
        <div class="ui form">
          <div class="eleven wide field">
            <div class="ui action labeled input">
              <div class="ui label">
                Private Key
              </div>

              <?php if ($this->encrypted_privkey != '') : ?>
                <input type="password" id="private-key" name="private-key" placeholder="nsec1..." value="Encrypted private key stored 101010101010101010101010101010101010">
                <button id="save-private-key" type="button" class="ui icon green button">
                  <i class="check icon"></i>
                </button>
              <?php else : ?>
                <input type="password" id="private-key" name="private-key" placeholder="nsec1...">
                <button id="save-private-key" type="button" class="ui icon violet button">
                  <i class="save icon"></i>
                </button>
              <?php endif; ?>

            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="six wide column">
        <table class="ui striped table">
          <thead>
            <tr>
              <th colspan="3">
                Relays
              </th>
            </tr>
          </thead>
          <tbody id="relay-tbody">
          </tbody>
        </table>

        <div class="ui form">
          <div class="ui action labeled fluid input">
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

      <div class="five wide column">
        <div id="ap-segment" class="ui segment">
          <div class="ui form">
            <div class="inline field">
              <div class="ui toggle checkbox <?php echo $ap_checked; ?>" id="auto-publish">
                <input type="checkbox" name="auto-publish">
                <label>Auto Post</label>
              </div>
              <div id="auto-publish-fields" class="grouped disabled fields" style="margin-left:20px">
                <div class="inline field">
                  <div id="post-excerpt" class="ui checkbox ap <?php echo $excerpt_checked; ?>">
                    <input type="checkbox" name="post-excerpt">
                    <label>Excerpt</label>
                  </div>
                </div>
                <div class="inline field">
                  <div id="permalink" class="ui checkbox ap <?php echo $permalink_checked; ?>">
                    <input type="checkbox" name="permalink">
                    <label>Permalink</label>
                  </div>
                </div>
                <div class="inline disabled field">
                  <div id="whole-post" class="ui checkbox ap <?php echo $whole_post_checked; ?>">
                    <input type="checkbox" name="whole-post">
                    <label>Whole Post Markdown (Coming)</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>


  </div>


</div>