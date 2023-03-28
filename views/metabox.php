<textarea id="wpnostr-note" class="ui input" style="width:100%;height:120px;"><?php
  echo get_the_excerpt($post->ID) . "\n\n";
  echo get_permalink($post->ID);?>
</textarea>
<?php if($post->_wpnostr_posted): ?>
<button disabled="disabled" id="wpnostr-post" class="ui button" style="margin-top:15px">POSTED</button>
<?php else: ?>
<button id="wpnostr-post" class="ui button" style="margin-top:15px">Post to Nostr</button>
<?php endif; ?>

<div class="modal-overlay closed" id="modal-overlay"></div>

<div class="modal closed" id="modal">
  <button class="close-button closed" id="close-button">Done</button>
  <div class="modal-guts closed">
    <h1>Posting to Nostr - Please wait.</h1>
    <p id="nostr-log"></p>
  </div>
</div>

<?php