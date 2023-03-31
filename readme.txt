=== Nostrtium ===
Contributors: pjv
Donate link: https://github.com/pjv/nostrtium
Tags: social media, nostr
Requires at least: 6.0
Requires PHP: 8.1
Tested up to: 6.2
Stable tag: 0.5.3
License: Unlicense
License URI: https://unlicense.org

Post to Nostr from WordPress.

== Description ==

Nostrtium lets you post from WordPress to Nostr.

This initial version just implements basic nostr settings (private key, relays) and provides a metabox in the WordPress Post editing page which is pre-populated with the Post Excerpt and a link to the Post and lets you post the content of that metabox to your configured relays.

You can change the content in the metabox as you like. If you have a good excerpt and post it as-is, it creates a twitter-style "announcement" note on nostr. A lot of nostr clients will render the link to the WordPress post as a nice-looking summary card with featured image and etc. This functionality is probably enough for many use-cases but I have plans to add more functionality to this plugin in the future, including generation of keys; support for NIP-07 browser extensions; separate Nostr profiles for individual WP users; support for full, long-form content from WP to Nostr; and more.

[Note that the private key is stored encrypted in the WordPress database using libsodium cryptography.]

### Requirements
Some of the included libraries have relatively recent dependency requirements so you will need the following in your WordPress platform:
* PHP 8.1+
* php-gmp module must be installed ([Installation on Ubuntu](https://computingforgeeks.com/how-to-install-php-on-ubuntu-linux-system/))
* WordPress 6.0+
* Writable installation directory (on activation, the plugin writes a cryptographic keyfile to its own install directory)

### How to Use
1. After installing and activating the plugin, go into Settings -> Nostrtium and copy/paste the private key (nsec1...) that you want to post from and tweak the relays to your liking.
2. Then visit the post editor page for an existing post and at or near the bottom you should see the Nostrtium metabox which is pre-populated with the excerpt and permalink for the post (you can change the content in the metabox as you like before posting):

### User Role / Capability Filter
You can create a filter snippet (in your theme's functions.php) to choose what user role or capability has access to Nostrtium functionality. The default is `edit_posts`. Here is a sample snippet that would change it so only adminstrators can see / use the plugin's functionality:

````
add_filter ('nostrtium_role', function($role){
	return 'administrator';
});
````

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Nostrtium, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Nostrtium” and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading the plugin and then uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

== Frequently Asked Questions ==

== Screenshots ==

1. Nostr relay management
2. Post to Nostr metabox

== Changelog ==

= 0.5.3 =
* Initial public release