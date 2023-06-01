<textarea id="nostrtium-note" class="ui input" style="width:100%;height:120px;"><?php
  echo get_the_excerpt($post->ID) . "\n\n";
  echo get_permalink($post->ID);?>
</textarea>
<?php if ($post->_nostrtium_posted) : ?>
  <button disabled="disabled" id="nostrtium-post" class="ui button" style="margin-top:15px">POSTED</button>
<?php else : ?>
  <button id="nostrtium-post" class="ui button" style="margin-top:15px">Post to Nostr</button>
<?php endif; ?>

<div class="modal">
  <h2>Posting to Nostr</h2>
  <p class="" id="nostr-log">Please wait...</p>
</div>