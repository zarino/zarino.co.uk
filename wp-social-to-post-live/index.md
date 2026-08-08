---
layout: page
title: Social-to-Post Live
---

# Social-to-Post Live

*Last updated: 2026-08-08*

“Social-to-Post Live” is a self-hosted WordPress plugin that imports a Facebook
Page’s posts into a WordPress site as Posts, maintained by Zarino Zappia (contact:
mail@zarino.co.uk). It’s an open-source, single-maintainer project, not a company or
commercial service.

This page covers two related but distinct things:

- **The relay** (“the Service”) – a small server-side component, hosted as
  [a Cloudflare Worker](https://www.cloudflare.com/products/workers/), that 
  exists solely to complete Facebook’s OAuth login handshake on behalf of
  the plugin (see [How the relay works](#how-the-relay-works) below).
- **The plugin** – the WordPress plugin code itself, which runs entirely on each
  individual user’s own WordPress site, under that user’s own control.

## How the relay works

Facebook’s OAuth flow requires a server that holds the app’s Client Secret to
exchange a short-lived authorization code for an access token – that secret can’t
safely live inside a plugin that ships to many self-hosted WordPress sites, so
this relay exists to do that one step centrally, on the plugin developer’s behalf.

The relay is **stateless**: it has no database, and doesn’t write the tokens it
handles to disk or to any logging/analytics service. Concretely, for each
connection:

1. A user clicks “Connect with Facebook” in their WordPress admin. The plugin
   opens the relay’s `/start` endpoint, which redirects them to Facebook’s own
   login/consent screen.
2. After the user approves access on Facebook’s site, Facebook redirects back to
   the relay’s `/callback` endpoint with a short-lived authorization code.
3. The relay exchanges that code for a Facebook access token (using its Client
   Secret), then immediately redirects the user’s browser back to their own
   WordPress site, with that token included in the redirect.
4. The relay does not keep a copy of the code or the token after that redirect
   completes. It has nothing to retain, log, or delete, because it never wrote
   anything down in the first place.

Cloudflare may keep its own short-lived, standard operational logs (e.g.
request timestamps and IP addresses, for abuse prevention and reliability) as 
most web infrastructure does – these are not used by this project to build a 
profile of any user, and are separate from anything the relay’s own code stores
or acts on.

## Privacy Policy

### What data reaches the relay

Only what’s needed to complete the Facebook login handshake described above: a
temporary OAuth authorization code, and briefly, the access token exchanged for
it, in transit, over HTTPS, for the few seconds it takes to redirect back to your
own site. The relay does not request, receive, or have any access to your
Facebook password, your personal messages, your friends list, or anything else
about your Facebook account beyond what’s needed to identify which Facebook
Pages you manage.

### What data the plugin stores, and where

Once connected, the Facebook Page Access Token is stored **encrypted, in the
database of your own WordPress site** – not on the relay, and not anywhere the
plugin’s maintainer can access. You (or whoever administers that WordPress
site) are the only one with access to it.

### What Facebook data the plugin accesses

Only data belonging to the specific Facebook Page(s) you explicitly choose to
connect:

- The Page’s ID and name.
- The Page’s own public posts (text, attached photos/videos, and link
  previews).

The plugin never accesses your personal Facebook profile data, other people’s
posts, comments or messages, or any Page you haven’t explicitly connected.

### What happens to that data next

Imported Facebook posts are copied into your WordPress site as ordinary
WordPress Posts (and their photos/videos into your WordPress Media Library),
stored in your site’s own database, under your own control, exactly like any
other content you publish there.

### Sharing

Neither the relay nor the plugin shares any data with third parties. Nothing is
used for advertising, and nothing is sold.

### Cookies

The relay itself sets no cookies – it only performs redirects.

### Children’s privacy

This service isn’t directed at children and isn’t knowingly used to collect
data from them.

### Changes to this policy

This page may be updated from time to time; the “Last updated” date at the top
reflects the most recent change.

### Contact

Questions about this policy: mail@zarino.co.uk.

## Terms of Service

By using Social-to-Post Live (the plugin and/or the relay), you agree to the
following:

- **What it’s for.** The Service exists to help you import your own Facebook
  Page’s posts into your own WordPress site. You confirm that you’re
  authorized to manage the Facebook Page(s) you connect, and that republishing
  their content on your WordPress site is something you’re entitled to do.
- **No warranty.** This is a free, open-source project, provided “as is,”
  without warranty of any kind. There’s no guarantee it will always be
  available, error-free, or fit for any particular purpose.
- **Limitation of liability.** To the fullest extent permitted by law, the
  maintainer isn’t liable for any damages or losses arising from your use of
  the Service.
- **Availability.** The relay is a free, personally-maintained service and may
  be modified, suspended, or discontinued at any time, for any reason, without
  notice. If that happens, the plugin’s manual-token connection method (pasting
  a Page Access Token directly, without the relay) continues to work
  independently of the relay’s availability.
- **Facebook Platform compliance.** Use of the Service is also subject to
  Facebook’s own [Platform Terms](https://developers.facebook.com/terms) and
  [Developer Policies](https://developers.facebook.com/policy).
- **Termination.** You can stop using the Service at any time by disconnecting
  your Facebook Page from the plugin’s settings page, or by deactivating or
  deleting the plugin entirely.
- **Changes.** These terms may be updated from time to time; continued use of
  the Service after a change constitutes acceptance of the new terms.

Questions about these terms: mail@zarino.co.uk.

## Data Deletion

**The relay has nothing to delete.** As described above, it’s stateless – it
never stores your authorization code, access token, or any other data about
you or your Facebook Page, so there’s no copy held centrally that could need
deleting.

**Data on your own WordPress site** (your encrypted Facebook Page Access
Token, and any Posts/media the plugin has imported) lives entirely in your own
site’s database, under your own control. You can remove it yourself, at any
time, without contacting anyone:

- **To disconnect your Facebook Page** (deletes the stored access token):
  go to the plugin’s settings page and click **Disconnect**.
- **To remove imported content** (deletes every Post and media file the
  plugin has imported, and resets its import history): go to the plugin’s
  settings page and click **Delete All Imported Content**.
- **To remove everything at once:** deactivate and delete the plugin via
  Plugins → Installed Plugins in your WordPress admin. This also permanently
  removes the plugin’s own settings from your site’s database.

If you’d prefer to ask instead of doing this yourself, or have any other data
deletion question, contact mail@zarino.co.uk and it will be actioned promptly –
though as above, there is nothing stored on the relay side to act on in the
first place.
