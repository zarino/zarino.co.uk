---
layout: post-custom
title: "Bypassing WiFi password complexity requirements on a Zyxel wireless router"
image: /media/zyxel-alert.png
summary: >
  The admin UI for current Zyxel routers requires WiFi passwords to contain letters, numbers, and special characters. If you prefer a different style of password (eg: diceware passwords), you can bypass the complexity requirement with the help of your web browser’s Developer Tools.
related:
  - /post/debugging-crappy-internet/
  - /post/transmission-zerotier-one-docker-synology-dsm-7/
---

<div class="container-readable" markdown="1">

Last weekend, my broadband provider, Hyperoptic, offered to send me a new wireless router. They promised it’d be a plug-and-play swap. But, since I’m the sort of trouble-maker who does _crazy_ stuff like _actually changing my WiFi network name_ from the stock “Hyperoptic Fibre 4321”, I knew there’d be a wrinkle.

Sure enough, when the router arrived and I started modifying its wireless settings to match my old set-up, I noticed the new router ([a Zyxel EX3301](https://www.hyperoptic.com/press/posts/hyperoptic-upgrades-hyperhub-router-range-setting-new-standards-in-full-fibre-broadband-technology/)) enforces a minimum complexity requirement on your WiFi network passwords:

<a href="/media/zyxel-alert.png" target="_blank">{% img src="/media/zyxel-alert.png" alt="Password complexity warning on a Zyxel router" width="1400" height="700" class="rounded" %}</a>

Now, password complexity is A Good Thing. But there are many _types_ of complexity. For passwords I’m likely to need to speak out loud (eg: when sharing my WIFI PASSWORD with a new visitor to my apartment) I tend to prefer [diceware passwords](https://diceware.dmuth.org/), which just use a string of lower case words, rather than having to explain out loud the difference between “o” and “O” and “0”, for example. Diceware passwords are also much easier to type on devices with crappy software keyboards (like, oh I dunno, when I’m entering my WIFI PASSWORD into my new Smart TV).

Plus I already had a nice, secure diceware password I wanted to keep. Changing to anything Zyxel-approved would mean changing the password all my devices (and my friends’ devices, when they come to visit) were expecting to connect with. No thanks.

So, I needed to… _persuade_ my router to accept a new password without any numbers or special characters in it. Thankfully, [some kind person on the internet had already solved this problem and posted about it](https://community.three.co.uk/t5/Broadband/Issues-with-setting-WPA-key-on-Zyxel-NR5103E-router/m-p/14429) – on a community support forum for customers of Three broadband.

But, because there’s no telling when a forum like that will just disappear, I figured why not repeat the advice here, just in case.

## How to bypass the password complexity requirement in the Zyxel web admin UI

Load up your router’s admin UI (it’s most likely at <https://192.168.1.1/>) and navigate to the WiFi settings screen. Untick the “Random password” checkbox, to enable you to set your own password.

</div>

<div class="container-fluid" style="max-width: 1000px">
    <p>
        <a href="/media/zyxel-wifi-settings.png" target="_blank">{% img src="/media/zyxel-wifi-settings.png" alt="WiFi network settings on a Zyxel router" width="1400" height="660" class="rounded" %}</a>
    </p>
</div>

<div class="container-readable" markdown="1">

Open your browser’s Developer Tools, and find the `app.js` file that your browser is loading. In Chrome, this will be under the “Sources” tab, in Firefox it’s under “Debugger”.

There’ll be a button somewhere that lets you “Pretty print” the file, which will make it much easier to edit. In Firefox, the button looks like a pair of curly braces, `{}`.

Search the file (`⌘`–`F` or equivalent) for the term `checkPasswordStrenth:` (yes, with the typo, no “g” in “Strenth”):

</div>

<div class="container-fluid" style="max-width: 1000px">
    <p>
        <a href="/media/zyxel-before-breakpoint.png" target="_blank">{% img src="/media/zyxel-before-breakpoint.png" alt="The WiFi settings page, with browser tools open showing the app.js file" width="1400" height="660" class="rounded border" %}</a>
    </p>
</div>

<div class="container-readable" markdown="1">

Add a breakpoint on the first line inside the function (note the breakpoint showing in blue, on the line number to the left of the source code):

</div>

<div class="container-fluid" style="max-width: 1000px">
    <p>
        <a href="/media/zyxel-after-breakpoint.png" target="_blank">{% img src="/media/zyxel-after-breakpoint.png" alt="The WiFi settings page, with browser tools open showing a breakpoint added to the app.js file" width="1400" height="660" class="rounded border" %}</a>
    </p>
</div>

<div class="container-readable" markdown="1">

Now, whenever this `checkPasswordStrenth` function is called, your browser will pause for your input.

Type your desired password into the form input on the webpage. As soon as you click away, Zyxel’s code runs the `checkPasswordStrenth` function, and your browser pauses for input.

Head over to the “Console” tab in your Developer Tools, and override the value of the `t` variable with something Zyxel would approve of. eg:

    t = "StUpId%ZyXeL1!"

</div>

<div class="container-fluid" style="max-width: 1000px">
    <p>
        <a href="/media/zyxel-during-breakpoint.png" target="_blank">{% img src="/media/zyxel-during-breakpoint.png" alt="Execution of the WiFi settings page paused at the breakpoint, as the user enters some text into the browser console" width="1400" height="500" class="rounded border" %}</a>
    </p>
</div>

<div class="container-readable" markdown="1">

Press enter to run that line of code, then switch back to the Debugger/Sources interface and click the “Resume” button to hand control back over to the webpage.

Your password will be accepted as input. Hooray! But you haven’t saved the form yet. You’ll need to override the `t` variable and Resume execution again when you click “Save” at the bottom of the form too.

Once you’re done, remove the breakpoint from the Debugger/Sources tab if you want to keep tidy.

Thanks to [Three forum user Hello1024](https://community.three.co.uk/t5/Broadband/Issues-with-setting-WPA-key-on-Zyxel-NR5103E-router/m-p/14429) for the tip!

</div>
