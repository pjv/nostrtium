<textarea id="wpnostr-note" class="ui input" style="width:100%;height:120px;"><?php
  echo get_the_excerpt($post->ID) . "\n\n";
  echo get_permalink($post->ID);?>
</textarea>
<?php if($post->_wpnostr_posted): ?>
<button disabled="disabled" id="wpnostr-post" class="ui button" style="margin-top:15px">POSTED</button>
<?php else: ?>
<button id="wpnostr-post" class="ui button" style="margin-top:15px">Post to Nostr</button>
<?php endif; ?>

<div class="modal">
  <h2>Posting to Nostr</h2>
  <p class="" id="nostr-log">Please wait...</p>
</div>

<?php