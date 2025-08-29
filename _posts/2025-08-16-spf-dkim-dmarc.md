---
layout: post
title: "Setting up SPF, DKIM, and DMARC for outgoing emails on an Ubuntu web server"
summary: >
  If you need to send emails from your web server, and don’t want to pay for a third-party SMTP service, I’ll walk you through the steps required to at least stand a good chance of your emails not being marked as spam.
related:
  - /post/hetzner-docker-caddy/
  - /post/daily-monitoring-emails-ubuntu/
---

Apparently, just [under half of all emails are spam](https://www.statista.com/statistics/420400/spam-email-traffic-share-annual/). If you want to send emails from your web server (for example, [system monitoring emails](/post/daily-monitoring-emails-ubuntu/)) and you don’t want to pay for a third-party SMTP service like [Amazon SES](https://aws.amazon.com/ses/) or [Mailgun](https://www.mailgun.com/), you’ll need to take steps to avoid your emails being flagged as spam by your recipients’ email providers.

There are three anti-spam measures that overlap:

- [Sender Policy Framework](https://en.wikipedia.org/wiki/Sender_Policy_Framework) (SPF)
- [DomainKeys Identified Mail](https://en.wikipedia.org/wiki/DomainKeys_Identified_Mail) (DKM)
- and [Domain-based Message Authentication, Reporting and Conformance](https://en.wikipedia.org/wiki/DMARC) (DMARC)

Theoretically, you only need _either_ SPF or DKIM set up, to then enable DMARC for your domain. But having both means that an SPF check can fail (because, say, there was some issue with your DNS provider) and as long as the DKIM signature on the message is still valid, DMARC will pass.

But, before you can authenticate your email, you need to be able to actually _send_ it in the first place.

# Set up sendmail on your server

[Sendmail](https://en.wikipedia.org/wiki/Sendmail) and [Postfix](https://en.wikipedia.org/wiki/Postfix_%28software%29) are two popular, low-level mail transfer agents – the things that will accept emails from software on your server and then deliver them to mail servers in the outside world.

I chose to use Sendmail for software running natively on my server, but Postfix in a Docker container for other Docker containers to send emails through (more on that at the end of this post).

Setting up Sendmail isn’t really the focus of this blog post, and will vary a lot depending on your situation. But, in case it helps you, in my case (an Ubuntu server, on Hetzner cloud) I…

Set my machine’s hostname:

```sh
hostnamectl set-hostname server.zarino.co.uk
```

Installed various mail tools:

```sh
sudo apt install sendmail sendmail-bin mailutils
```

And made sure mail ports were open in Ubuntu’s firewall, for local processes to connect to port 25:

```sh
sudo ufw allow from 127.0.0.1 to any port 25
sudo ufw allow from ::1 to any port 25
```

(Note: some guides will suggest you allow `smtp`, `587/tcp`, and `465/tcp`, but the first of those—`smtp`, which is just a shortcut for `25/tcp`—will result in your firewall being wide open for any external service to communicate with your server on port 25, and the latter two are only for when you want mail clients to be able to log into your server directly, which you don’t need if all you’re doing is sending emails out. So the more targeted `allow` lines, for just port 25 on the IPv4 and IPv6 loopback addresses, are much more secure.)

And ran the sendmail setup, keeping all the default options:

```sh
make -C /etc/mail/
systemctl restart sendmail
```

Ports 25 and 465 are blocked by default on Hetzner Cloud servers. So I had to request they were unblocked by selecting “Server issue: Sending mails is not possible” from the [Hetzner Cloud Console support form](https://console.hetzner.cloud/support). (When you select the “Sending mails is not possible” option, it gives you a link to click to submit a request to unblock the ports. I submitted a request, and they were instantly unblocked as soon as I submitted a message – I think because my Hetzner account was a few months old, and I’d already paid an invoice, so they were happy I’m not a spammer.)

While I was there, I also [set up Reverse DNS](https://docs.hetzner.com/cloud/servers/cloud-server-rdns), which maps my server’s IP addresses back to one of my domain names (which then has A/AAAA records set which point back to the IP address, forming a complete loop and satisfying email spam detectors). In the end, the “Public Network” settings for my Hetzner server looked like:

<table class="table">
<thead>
<tr>
<th>Primary IP</th>
<th>Protocol</th>
<th>Reverse DNS</th>
</tr>
</thead>
<tbody>
<tr>
<td>188.245.214.167</td>
<td>IPv4</td>
<td>server.zarino.co.uk</td>
</tr>
<tr>
<td>2a01:4f8:c0c:f39a::/64</td>
<td>IPv6</td>
<td>server.zarino.co.uk</td>
</tr>
</tbody>
</table>

And the corresponding DNS records in my domain registrar’s control panel:

<table class="table">
<thead>
<tr>
<th>Type</th>
<th>TTL</th>
<th>Name</th>
<th>Content</th>
</tr>
</thead>
<tbody>
<tr>
<td>A</td>
<td>10 mins</td>
<td>server.zarino.co.uk</td>
<td>188.245.214.167</td>
</tr>
<tr>
<td>AAAA</td>
<td>10 mins</td>
<td>server.zarino.co.uk</td>
<td>2a01:4f8:c0c:f39a::1</td>
</tr>
</tbody>
</table>

# Set up SPF records for each domain you want to send emails from

For each domain you want to send emails from, add a TXT record for that domain via your domain registrar’s control panel. The TXT record should contain all the servers you want to allow to send emails for the given domain.

Example TXT record value:

```
v=spf1 a mx ip4:188.245.214.167 ip6:2a01:4f8:c0c:f39a::1 include:_spf.google.com ~all
```

Explanation:

- `v=spf1` – this is an SPF record!
- `a mx` – test the `A`, `AAAA`, and `MX` records of the sender’s IP to find the domain the email has been sent from
- `ip4:188.245.214.167 ip6:2a01:4f8:c0c:f39a::1` – allow sending from the given (Hetzner VPS) IPv4 and IPv6 addresses
- `include:_spf.google.com` – allow sending from Google’s (Google Workspace) addresses
- `~all` – finally, allow email that doesn’t match the above rules, but mark it as suspicious

# Set up DKIM for each domain you want to send emails from

It’s easiest to switch into the root user for all of these commands:

```sh
sudo su
```

Install OpenDKIM:

```sh
apt update
apt install opendkim opendkim-tools
```

For each domain, create directories to store the OpenDKIM keys, eg:

```sh
mkdir -p /etc/opendkim/keys/zarino.co.uk
mkdir -p /etc/opendkim/keys/server.zarino.co.uk
mkdir -p /etc/opendkim/keys/zappia.co.uk
```

Generate OpenDKIM keys for each domain, eg:

```sh
opendkim-genkey -D /etc/opendkim/keys/zarino.co.uk -s default -d zarino.co.uk
opendkim-genkey -D /etc/opendkim/keys/server.zarino.co.uk -s default -d server.zarino.co.uk
opendkim-genkey -D /etc/opendkim/keys/zappia.co.uk -s default -d zappia.co.uk
```

Explanation:

- `-D /etc/opendkim/keys/…` tells opendkim-genkey to create the key files in the given directory (rather than the current working directory)
- `-s default` specifies the “selector” for our key – this matches the default selectors in our KeyTable and SigningTable. Technically, we could have left this out, as default is the default selector name anyway
- `-d` is the domain to create a key for
- There’s no need for `-b 1024` as it’s the default key size, and it’s what the DKIM spec recommends

Note that this will have created both public (`default.txt`) and private (`default.private`) keys in each domain’s subdirectory. You’ll need to copy and paste the contents of these default.txt files into DNS TXT records in a later step.

At the very least, you should give the `opendkim` user ownership of the private keys (eg: with `sudo chown opendkim:opendkim default.private`) but many guides, including the [Debian](https://wiki.debian.org/opendkim) and [Arch](https://wiki.archlinux.org/title/OpenDKIM) wikis, suggest you give `opendkim` ownership of the entire `/etc/opendkim` directory, and even (in Debian’s case) make it readable only by the `opendkim` user, with `chmod 0700`:

```sh
chown -R opendkim:opendkim /etc/opendkim
chmod 0700 /etc/opendkim
```

Edit `/etc/opendkim.conf` (on some distros you need to copy it from `/usr/share/doc/opendkim/opendkim.conf.sample` first – I didn’t on Ubuntu) and make sure the following are set:

```
Syslog                  yes
SyslogSuccess           yes
LogWhy                  yes

Canonicalization        relaxed/simple
Mode                    sv

PidFile                 /var/run/opendkim/opendkim.pid
Socket                  inet:12301@localhost

ExternalIgnoreList      refile:/etc/opendkim/TrustedHosts
InternalHosts           refile:/etc/opendkim/TrustedHosts
KeyTable                refile:/etc/opendkim/KeyTable
SigningTable            refile:/etc/opendkim/SigningTable

UserID                  opendkim:opendkim
SignatureAlgorithm      rsa-sha256

AutoRestart             yes
AutoRestartRate         10/1h
```

Explanation:

- `Syslog yes` enables logging, most likely to `/var/log/mail.log`
- `LogWhy` includes extra info in the logs, about why a message was or wasn’t signed or verified
- `Canonicalization` specifies how OpenDKIM should handle whitespace in the message header and body, relaxed/simple was the default on my install
- `Mode sv` tells OpenDKIM to both verify (`v`) DKIM signatures on incoming mail, and sign (`s`) outgoing mail
- The `PidFile` specifies where OpenDKIM will write its process ID file, which can be useful for monitoring whether OpenDKIM is running
- `Socket` is the socket OpenDKIM listens on – Sendmail will connect to this socket when it has an email for OpenDKIM to verify or sign
- The `refile:` prefixes on the file paths mean that OpenDKIM will parse each line of the files as a regular expression, allowing for pattern matching of IP addresses or hostnames
- `UserID` is the user and group OpenDKIM will use
- `SignatureAlgorithm rsa-sha256` tells OpenDKIM to use the default RSA encryption for signatures
- `AutoRestart` and `AutoRestartRate 10/1h` tell OpenDKIM to restart up to 10 times an hour, if it crashes
- There is no need for a `Domain` or `Selector` values, as we’re using a `KeyTable` and `SigningTable` to specify selectors for multiple domains
- We could set `RequireSafeKeys False` to disable strict permissions checking on the OpenDKIM key files, but it’s better practice to make sure the keys are owned by the `opendkim` user/group anyway (see above), so we leave this out, meaning OpenDKIM defaults to `RequireSafeKeys True`.

Create a text file at `/etc/opendkim/KeyTable` which tells OpenDKIM how to associate each domain’s public DKIM subdomain address with the relevant private key. Eg:

```
default._domainkey.zarino.co.uk zarino.co.uk:default:/etc/opendkim/keys/zarino.co.uk/default.private
default._domainkey.server.zarino.co.uk server.zarino.co.uk:default:/etc/opendkim/keys/server.zarino.co.uk/default.private
default._domainkey.zappia.co.uk zappia.co.uk:default:/etc/opendkim/keys/zappia.co.uk/default.private
```

Create another text file at `/etc/opendkim/SigningTable` which tells OpenDKIM which keys to use when signing emails. Eg:

```
*@zarino.co.uk default._domainkey.zarino.co.uk
*@server.zarino.co.uk default._domainkey.server.zarino.co.uk
*@zappia.co.uk default._domainkey.zappia.co.uk
```

And another text file at `/etc/opendkim/TrustedHosts` which tells OpenDKIM which hosts to trust (replacing `SERVER_IPV4` and `SERVER_IPV6` with the server’s public IP addresses):

```
127.0.0.1
::1
localhost
SERVER_IPV4
SERVER_IPV6
zarino.co.uk
server.zarino.co.uk
zappia.co.uk
```

You’ll also need to tell Sendmail to sign messages with OpenDKIM. Edit `/etc/mail/sendmail.mc` and add the following line (note the TCP Socket address must match the Socket line you added to `/etc/opendkim.conf`):

```
INPUT_MAIL_FILTER(`opendkim', `S=inet:12301@localhost')dnl
```

Then regenerate the `sendmail.cf`:

```sh
make -C /etc/mail
```
 
Now switch to your DNS provider, and create TXT records for each domain, using the subdomain values you defined in the `KeyTable` and `SigningTable`, and the `default.txt` public key file contents from the earlier step.

For example, the `TXT` record at `default._domainkey.zarino.co.uk` might look like `"v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8A…"`

Note: You might also have other, third-party domain keys in your DNS – for example `google._domainkey` if you’ve enabled DKIM signing for emails sent via Google Workspace.

Restart OpenDKIM and Sendmail:

```sh
systemctl restart opendkim
systemctl restart sendmail
```

Use [mail-tester.com](https://www.mail-tester.com) (3 free email tests per day) to verify that Sendmail has included a valid `DKIM-Signature` value in your outgoing email headers, and that the receiving server has been able to authenticate it (the receiving server will often add its own header to show this, eg: `Authentication-Results: dkim=pass …`).

For example:

```sh
echo "Test from zarino.co.uk" | mail -s "Test 1" test-XXXXXXXX@srv1.mail-tester.com -r example@zarino.co.uk
echo "Test from server.zarino.co.uk" | mail -s "Test 2" test-XXXXXXXX@srv1.mail-tester.com -r example@server.zarino.co.uk
echo "Test from zappia.co.uk" | mail -s "Test 3" test-XXXXXXXX@srv1.mail-tester.com -r example@zappia.co.uk
```

# Set up DMARC for each domain you want to send emails from

DMARC requires you to provide a public email address where delivery errors can be automatically reported. Services like [Postmark’s DMARC Digest](https://dmarc.postmarkapp.com/) will give you an email address to provide in your DMARC records (which they’ll then summarise for you) or you can provide your own. Just be aware that it might receive a lot of mail!

In my case, I set up a Google Group, under my Google Workspace plan, to collect all the emails, and that way, I can receive a daily digest, or no notifications at all, and see the full history of notifications via the Google Groups web interface. However, since this means my DMARC report email address sometimes wouldn’t be on the same domain as the emails about which I’m receiving reports, as an extra security step, [DMARC requires me to add a special DNS TXT records](https://support.google.com/a/answer/10032472?sjid=5962157666375753320-EU#reports-domain) to the `_report._dmarc.` subdomain of `zarino.co.uk`, allowing it to receive DMARC reports about other domains:

```
TXT server.zarino.co.uk._report._dmarc.zarino.co.uk "v=DMARC1;"
TXT zappia.co.uk._report._dmarc.zarino.co.uk "v=DMARC1;"
```

Once you have an email address for each domain, to which DMARC reports can be sent, include that email address in a new TXT record on the `_dmarc.` subdomain of each sending domain, containing the DMARC policy for that domain, eg:

```
TXT _dmarc.zarino.co.uk "v=DMARC1; p=none; rua=mailto:dmarc@zarino.co.uk"
TXT _dmarc.server.zarino.co.uk "v=DMARC1; p=none; rua=mailto:dmarc@zarino.co.uk"
TXT _dmarc.zappia.co.uk "v=DMARC1; p=none; rua=mailto:dmarc@zarino.co.uk"
```

Once you’re confident everything’s been set up correctly, you can replace the DMARC records with stricter variants, which actually enforce that all emails should pass SPF and DKIM checks:

```
… "v=DMARC1; p=quarantine; sp=quarantine; rua=mailto:dmarc@zarino.co.uk; ruf=mailto:dmarc@zarino.co.uk; adkim=s; aspf=s"
```

# Bonus: Sending email with a Postfix Docker container, signed with DKIM keys from the host

The steps above have got email working from software running on the host machine. If you’ve followed [my guide to setting up a dockerised web server with Caddy](/post/hetzner-docker-caddy/) you might assume that things running _inside_ these Docker containers can _also_ send via this route – but they can’t!

Part of the security benefit of running stuff inside Docker containers is that processes are isolated from the host machine – so a PHP vulnerability in, say, your WordPress container, can’t easily bring down your whole server. As a result—unless you undermine this security with something like a Docker network that allows traffic directly to the host—the software running inside the containers can’t communicate with Sendmail on the host.

But it’s no big deal. In fact, it’s the Docker ethos working as intended – pushing us towards isolating each of our services inside _their own_ containers, so we can manage and reproduce them individually, without sacrificing security.

The solution, then, is to set up a mail transfer agent (Postfix, in this case, as it’s easier to configure than Sendmail) in a Docker container, and then use Docker’s in-built network routing to direct mail to it from all the other containers in your stack.

If you [followed my previous guide](/post/hetzner-docker-caddy/), you’ll already have a `docker-compose.yml` file with separate services that host different websites, all connected by a bridge network, eg:

```yaml
services:
  caddy:
    container_name: caddy
    hostname: caddy
    image: caddy:latest
    restart: unless-stopped
    depends_on:
      - php-fpm
    cap_add:
      - NET_ADMIN
    ports:
      - "80:80"
      - "443:443"
      - "443:443/udp"
    networks:
      - caddynet
    volumes:
      - ./etc-caddy:/etc/caddy:ro
      - /srv:/srv
      - /var/log/caddy:/var/log/caddy
      - caddy_data:/data
      - caddy_config:/config

  php-fpm:
    container_name: php-fpm
    hostname: php-fpm
    image: php:fpm
    restart: unless-stopped
    networks:
      - caddynet
    volumes:
      - /srv:/var/www/html

networks:
  caddynet:
    attachable: true
    driver: bridge

volumes:
  caddy_data:
  caddy_config:
```

We want to add a Postfix service to the `services:` list, eg:

```yaml
services:
  postfix:
    image: "boky/postfix"
    container_name: postfix
    hostname: postfix
    restart: unless-stopped
    volumes:
      - /etc/opendkim/keys:/etc/opendkim/keys:ro
      - postfix_spool:/var/spool/postfix
    networks:
      - caddynet
    env_file:
      - ./conf/postfix.env

volumes:
  postfix_spool:
```

The [Boky Postfix image](https://hub.docker.com/r/boky/postfix) I’m using is [configured through environment variables](https://github.com/bokysan/docker-postfix), but to avoid my `docker-compose.yml` becoming cluttered with config, I instead use the `env_file:` directive, and put the Boky environment variables in `./conf/postfix.env`:

```sh
ALLOWED_SENDER_DOMAINS="zarino.co.uk server.zarino.co.uk zappia.co.uk"
AUTOSET_HOSTNAME=true
DKIM_SELECTOR=default
INBOUND_DEBUGGING=true
TZ=Europe/London
```

Explanation:

- `postfix:` – this is the name of the service. Other Docker containers on the same `caddynet` network will be able to communicate with this container, by just calling `postfix` instead of a domain name or IP address.
- `container_name: postfix` technically not required, as docker-compose automatically generates unique names for your containers, but I like my containers to have shorter names, which match the service name, so here’s where I set that.
- `hostname: postfix` tells things _inside_ the container what their machine’s host name is, which isn’t required, but does no harm, so I tend to do it by default, setting it to the name of the service. That way software both inside and outside the container knows to use the same hostname for the service.
- The `/etc/opendkim/keys:/etc/opendkim/keys:ro` volume binding allows Postfix inside the container to use the same DKIM keys to sign emails, as Sendmail does running _outside_ the server. Note that the path before the colon matches the path we set up for OpenDKIM keys much earlier in this guide. The `:ro` suffix mounts the directory read-only, so the host’s keys cannot be modified or deleted from within the Docker container.
- The `postfix_spool:/var/spool/postfix` volume binding means that messages in what is effectively Postfix’s “outbox” are stored _outside_ the container, in the `postfix_spool` volume, which means they won’t be lost if the container is stopped or recreated between an email being added to Postfix’s queue and finally being sent. There’s a very low likelihood this would ever happen, but it doesn’t harm to share this directory just in case.
- `networks: caddynet` puts the container for this `postfix` service on the same internal Docker network as all my other containers, meaning all the other containers can communicate with this one at the hostname `postfix`.

And the Boky Postfix image environment variables:

- `ALLOWED_SENDER_DOMAINS` is a space-separated list of all the domains I want this Postfix image to be able to send emails from.
- `AUTOSET_HOSTNAME` tells the image to work out its own hostname by performing a reverse DNS check on the machine’s public IP address, which is simpler than hard-coding it.
- `DKIM_SELECTOR` is the “selector” of our DKIM keys. If you followed my guide, above, then your selector will be `default`.
- `INBOUND_DEBUGGING` enables more detailed logs.
- And `TZ=Europe/London` is me setting the timezone, so timestamps in logs are correct.

With all of that in place, you can start the service, eg:

```sh
docker compose up -d postfix
```

And now any other docker container on the `caddynet` network can communicate with the `postfix` container, over SMTP, to send emails.

For example, using the [WP Mail SMTP plugin](https://wordpress.org/plugins/wp-mail-smtp/) in a WordPress site hosted on this stack, I can just set the SMTP server address to:

```
postfix
```

…And emails magically flow from the WordPress container, to the `postfix` container (over SMTP), are then signed with the DKIM keys from the host machine, and sent out for delivery via the recipients’ mail servers.

Likewise, in the [changedetection.io service](https://github.com/dgtlmoon/changedetection.io) I run on my server, I can have notifications for webpage updates sent to me by email, by setting the AppRise notification URL to:

```
mailto://postfix?to=me@example.com&from=changedetection@example.com
```

Easy peasy!
