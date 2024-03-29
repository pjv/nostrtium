# nostrtium
_Post to nostr from WordPress_

![](.wordpress-org/banner-1544x500.png)

This is a beta quality first-pass at a plugin that lets you post from WordPress to nostr.

This initial version just implements basic nostr settings (private key, relays) and provides a metabox in the WordPress Post editing page which is pre-populated with the Post Excerpt and a link to the Post and lets you post the content of that metabox to your configured relays. You can change the content in the metabox as you like. On the settings page you can also set up automatic posting of excerpt, permalink, or both on publication of new WordPress posts.

If you have a good excerpt and post it as-is, it will create a twitter-style "announcement" note on nostr. A lot of nostr clients will render the link to the WordPress post as a nice-looking summary card / link preview with the post's featured image and the excerpt. This functionality is probably enough for many use-cases but I have plans to add more functionality to this plugin in the future, including generation of keys; support for NIP-07 browser extensions; separate nostr profiles for individual WP users; support for full, long-form content from WP to nostr; and more.

Please test and report issues here.

[Note that the private key is stored encrypted in the WordPress database using libsodium cryptography.]

## Requirements
Some of the included libraries have relatively recent dependency requirements so you will need the following in your WordPress platform:
* PHP 8.1+
* php-gmp module must be installed ([Installation on Ubuntu](https://computingforgeeks.com/how-to-install-php-on-ubuntu-linux-system/))
* php-bcmath module must be installed ([Installation on Ubuntu](https://www.itsolutionstuff.com/post/ubuntu-php-bcmath-extension-install-commands-exampleexample.html))
* WordPress 6.0+
* Writable uploads directory (on activation, the plugin writes a cryptographic keyfile to a storage directory)

## Installation
If you want to use the plugin, download the latest zip release from the [releases page](https://github.com/pjv/nostrtium/releases).

This repository does not include the required vendor directory, so you cannot just clone this repo and use it as-is. If you want to code on it, you can clone it and do `composer install`.

## How to Use
1. After installing and activating the plugin, go into Settings -> Nostrtium and copy/paste the private key (nsec1...) that you want to post from and tweak the relays to your liking.

![Settings Page](.wordpress-org/screenshot-1.png)

2. Then visit the post editor page for an existing post and at or near the bottom you should see the Nostrtium metabox which is pre-populated with the excerpt and permalink for the post (you can change the content in the metabox as you like before posting):

![Settings Page](.wordpress-org/screenshot-2.png)

### User Role / Capability Filter
You can create a filter snippet (in functions.php) to choose what user role or capability has access to Nostrtium functionality. The default is `edit_posts`. Here is a sample snippet that would change it so only adminstrators can see / use the plugin's functionality:

````
add_filter ('nostrtium_role', function($role){
	return 'administrator';
});
````

## Support
Want to support development of Nostrtium? Here's a bitcoin address to send to:

bc1q6w0jjc6wk6ts3zejt3zeu4sytnjwkrlm6ws5ej

## TODO
* Separate private keys for individual WP users
* Generate keys on the settings page and/or user profile page
* NIP-07 signing
* Long-form nostr posting
* Relay sets
* nostr profile editing