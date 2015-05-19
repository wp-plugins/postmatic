=== Postmatic - Engagement through email ===
Contributors: vernal
Tags: email, subscribe to comments, subscription, subscribe, commenting, reply, email,  comments, posts, reply, subscribe, mail, listserve, mailing, subscriptions, newsletter, newsletters, email newsletter, email subscription, newsletter signup, post notification, newsletter alert, auto newsletter, automatic post notification, email newsletters, email signup, auto post notifications, subscribe widget, signup widget, email subscription, newsletter plugin, widget, subscription, emailing, mailpoet, wysija, mandrill, mailchimp, mailgun, email comming, reply to email, email replies, engagement, invite, invitations, jetpack, subscribe2
Requires at least: 3.9
Tested up to: 4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Postmatic lets your readers subscribe to comments or new posts by email. And leave a comment by simply hitting reply. No forms. No browsers. Easy.

== Description ==

[Read about how we are revolutionizing WordPress commenting on our site](http://gopostmatic.com)

= Your posts delivered to their inbox. =
= Their comments delivered through a simple reply. =
= That’s how the conversation gets started. That’s how you increase engagement. =

Your WordPress posts will be delivered to readers as a simple email.  
And they can comment just by hitting reply. No accounts. No forms.  
No browsers. Easy.

= Engage your readers at their own pace with 100% email-based commenting =

The days of one-way notifications are over. It’s time to let them hit reply.

Postmatic lets your readers subscribe by email when they leave a comment. Subsequent comments and replies will land in their inbox, just like with _Mailpoet_, _Jetpack_ or _Subscribe to Comments_.**Except now with Postmatic, they can comment back and keep the conversation going just by hitting reply**. They never have to leave their inbox.

Online or offline, everyone stays in the loop—regardless of desktop or mobile.

[vimeo http://vimeo.com/108515948]

[Visit gopostmatic.com to learn more](http://gopostmatic.com)

= Key Features =
		
* Posts are converted into fully responsive html emails on the fly. We support images, video, shortcodes, and oEmbed.
* We handle email delivery through partnerships with Mailchimp and Rackspace. Lists with thousands of subscribers are no trouble atll.
* Replies are posted as comments in as little as 6 seconds.
* Intelligent comment templates provide conversational context and threaded commenting.
* Comment moderation via email (approve, trash, spam) with a single reply.
* Single-click migration tools for Jetpack, MailPoet, and Mailchimp lists.
* An incredible invitation system that turns past commenters into subscribers.
* Postmatic strictly adheres to WordPress best practices. It uses the native commenting and user systems. We’re just a magical email gateway.
* We’re serious about privacy and doing the right thing. We do not profile users, run ads, or sell data in any way and maintain a very strict [privacy](http://gopostmatic.com/privacy) policy.
* The basic version of Postmatic, which brings 100% email commenting to WordPress, is free without limits.

== Frequently Asked Questions ==

= This is really free? Do you sell my data or run advertising? =

Yes to free. [No to bad stuff](http://gopostmatic.com/privacy). We're not in the data brokering or advertising game. Instead we're in the business of making [Postmatic Premium](http://gopostmatic.com/premium) _so good_ and _so affordable_ that you'll happily upgrade.You can help fund our development while sending your engagement through the roof by subscribing to Postmatic Premium. We're even running [a launch special right now](http://gopostmatic.com/trial).

= Is this a 3rd party commenting system? =
Not at all. Postmatic uses native WordPress commenting. All we do is wire up some magic to turn emails into comments, then push them to your site via the WordPress api. You can [read all about](http://gopostmatic.com/technology) it here.

= How quickly do email comments post to my website =
It takes Postmatic **six to ten seconds** after you hit send to turn your email into a WordPress comment.

Find a few hundred more answers at our [big FAQ](http://gopostmatic.com/faq/).

== Screenshots ==
1. A sidebar widget lets users subscribe to all site content, or just authors they are interested in. Postmatic also integrates with 3rd party email signup plugins such as Magic Action Box.
2. New posts are sent as gorgeous mobile-ready emails. The user can just hit reply to send a comment. Nifty.
3. Comments are sent as beautiful and context-sensitive email notifications. Just reply to chime in.
4. The footer of the email invites the user to to leave a comment or manage their subscription settings. Users can subscribe to comments on this post, unsubscribe from comments on the post, or leave their own reply.
5. Followup comments are also sent using a simpler email template. They are reply-enabled as well.
6. All Postmatic emails are replyable and fully responsive.
7. The plugin registers a sidebar for configuring footer widgets to use in your email template.
8. The invitation system is fantastic. Postmatic will send invitations to an imported list or let you choose from your existing community. Users can reply to the invitation to subscribe to your site.
9. We're serious about privacy. Your data is yours, and always will be. Postmatic uses fully-native commenting. Just think of us as a magical email > WordPress gateway.
10. Postmatic is 100% compatible with all your favorite user and commenting plugins because it is fully WordPress native.

== Changelog ==

= 1.1.1 =

- All subscribe widget text is now customizable
- Added support for Zemanta Related Posts
- Made some readability adjustments to the new comment template on the free plan
- Better repression of post-content hooks so things like Jetpack sharing don't mess up the end of posts on the free plan
- Added more diagnostics code for troubleshooting missed cron jobs
- Whenever you refresh your favicon we'll delete the previous version from your media library

= 1.1 =

- Big news for anyone on the free plan: all posts and comments are now sent in html mode. This is a big improvement over our purely-text based markdown implementation of 1.0. Upgrade to premium for even better image, video, audio, shortcode, and embed support.
- Premium users can now edit the contents of the multipart text version of each post before sending. Great for fine-tuning for older devices.
- Updated Italian translation
- Misc Mailchimp importer bug fixes
- Misc widget fixes
- Plenty of small bugs squashed

= 1.0.1 = 

- Nothing crazy exciting. The Mailchimp importer was unhappy but is better now. 
- Improved flexibility with sites that use both http and https

= 1.0 =

- Moved the subscribe to comments by email checkbox. It now sits above the submit button and is more visible across all themes.
- Improvements to comment moderation language.
- Added more patterns to the signature stripping library, including the new gmail timestamps.
- We now support comment author urls and link to the author websites from the email-based comment template.

= 1.0 RC1 =

So many good things there aren't enough bits in the universe to explain it all. Head on over to [our blog](http://gopostmatic.com/blog) for the big news. Special thanks to our wonderful beta testers! We love you!

= 1.0 beta 19 =

Mostly integrations with other plugins as we make our way to our public release very  soon!

- Fixed an issue in which featured images smaller than 1350x1550 would sometimes break
- Added support for Magic Action Box. Full integration coming in their next release.
- Added translation support for email commands (subscribe, unsub, etc)
- Fixed occasional problem with images not floating correctly in galleries
- Added support for Jetpack image galleries
- Added support for Yumprint recipes plugin
- Added support for Hupso social sharing
- Added support for the official Twitter plugin
- HTML entities in post and site titles are now translated to UTF-8


= 1.0 beta 18 =

A few quick bug fixes that we found in b17:

- We now less agressively strip line breaks. I think we found a happy middle ground.
- A quick improvement to the _you've subscribed_ notification template. We stuck a button at the bottom of the comment stream which brings you to the conversation online.
- The button that lets you choose to show the featured image or not on new posts is now sticky.
- Support for the socialshare plugin
- We added an option to disable new post notifications by default. Find it in settings > postmatic > options.
- You can now display the subscribe form via shortcode. See [how it works here](http://docs.gopostmatic.com/article/96-can-i-insert-a-subscribe-form-via-shortcode).


= 1.0 beta 17 =
- Oh goodness. We decided to make an entire second set of templates for powering commenting. They are tiny, simple, and to the point. Try it to see.
- Sites without a header image will now use a favicon.
- We figured out a way to strip out single-line breaks that were impossed by oldschool clients. Inbound comments look much better now.
- We improved subjects all around. Now you know if a comment is a new top level, a new reply to someone else, or a new reply to you.
- Added support for stripping Wisestamp signatures. Or removed it. Not sure. But now you won't see them :)
- Subscribe / Unsubscribe and other commands will now be processed even if they are in the subject line (where they don't belong)

= 1.0 beta 16 =
- New: When you subscribe to a conversation the confirmation email will bring you up to date on the conversation thus far. Now when you subscribe to a post you'll know exactly what you've missed and where to jump in.
- We've squashed a ton of bugs with the new post email template. Things have been tightened up all around and older email clients are now better supported. **There are now new requirements and considerations for header images**. You'll want to read our [blog post](http://gopostmatic.com/blog) about it.
- We fixed an issue in which was keeping incoming emails from getting to your site if you were running wp zero spam on certain host environments. Sorry about that.
- Better notifications if a new post is emailed out with comments disabled.

= 1.0 beta 15 =
- Fixed more gmail and Thunderbird image problems which caused the template to get blown out. Looks great now.
- Added support for Fastmail links in the footer of emails.
- Added some langauge to the new post notification template which gives a warning about forwarding the email to others.
- Removed support for the Jetpack _Like This_ button. Sorry, no way to pull it off via email.
- Improved Jetpack share button support.
- A new top secret feature: to subscribe to the comments on a post you don't need to actually write 'subscribe'. Just reply with a blank email.
- Fixed a bug in which the inviter would mistakingly send duplicate invitations in rare situations.
- We now cache subscribers less agressively. This will avoid the problem of a user unsubscribing but still receiving a new post that is published right around the same time...

= 1.0 beta 14 =
- Added support to invite existing WordPress users to become subscribers.
- Changed links on embeds in email view. They now link directly to the embed url instead of the online post. Very cool on mobile.
- Added ability for users to subscribe to all site comments from their profile page.
- Added more classes to the comment form checkbox for improved styling.
- Added recommendations for fighting spam commenting. If you are not running proper anti-spam plugins we'll sniff it out and let you know.
- We made the title of the post link to the post url on new post notifications. It's a little bit of an easter egg but what it means is that if you get the email and want to view the post online without scrolling to the bottom... you can now do so.
- Added support for the wp-gist plugin. We'll now show your gists directly in the email body.
- Official full translations for French, German, and Spanish. All plugin functions and templates are now fully translated into all three of these languages. Four total if you count English.


= 1.0 beta 13 =
- Fixed up issues with overly-large aligncenter images in certain vesions of gmail.
- Better support for posts with comments turned off. If you have comments turned off when publishing a post replies to the email notification will be sent to you directly and not posted as comments. If a post is published with comments turned on, but they get turned off in the future, replies will then send the usual error notification to the sender.
- Fixed a bug in the subscription confirmation email which displayed the latest post incorrectly.
- Removed 1,500 limit on sending invites to past commenters. Limit stays in place for pasted email addresses.
- Modified the email template query so author-aware widgets (like bio widgets) now work happily

= 1.0 beta 12 =
- Added support for oembeds. If possible the content is displayed in the email. If not, a nice placeholder image will be used. The placeholders are even smart enough to know if they are standing in for audio, video, or a document.
- Improved handling of quoted text using markdown on replies
- New support tab in the admin interface including a handy button to send diagnostic info to our team. Your theme name as well as all active plugins will be sent if you click it. Glorious day.
- Minor bugs were squashed.

= 1.0 beta 11 =
- Removed support for MORE tag. Full post content will now be shown in email. Option to respect it coming soon.
- Fixed a typo in the subscription confirmation email
- More language bug fixes. Sorry about that. Spanish and French should be much happier now.

= 1.0 beta 10 =
- Put the _view this post online_ button back on new post notifications. By request. From many of you :)
- Added links to view the post online in comment notification emails.
- Improved styling of _this content not available online_ notifications.
- Added some experimental Mailpoet importing features. Move your Mailpoet subscribers over to Postmatic.

= 1.0 beta 9 =
- Fixed a bug that was removing images from post emails.

= 1.0 beta 8 =

Big news on this release: support for threaded commenting _by email_. Check out our blog post for more info http://gopostmatic.com/2014/11/beta8/.

- Added support for threaded commenting. From here on out replies to new post notifications will leave a top-level comment. Replies to comment notificiations (Shirley left a comment) will leave a child comment (in response to Shirley). And on and on and on. We now offer 100% email based commenting.
- Better debugging info for failed Jetpack imports


= 1.0 beta 7 =

This is mostly an under-the-hood release to take care of a few bugs and improve language support.

- Fixed a few bugs with the user exporter throwing a 404.
- Added support for Jetpack sharing (sharedaddy) buttons in the email template. Much better.
- Added official French translation by beta user http://www.languagesbylaura.com. Thanks, Laura!
- Added official Spanish translation by beta user http://www.languagesbylaura.com. Thanks again, Laura!
- Added some host-specific preflight checks and better error reporting for Jetpack imports

= 1.0 beta 6 =
- Started loading subscription form via an ajax request, which should improve page load times and cooperate well with caching systems.
- Jetpack importing!
- Added ability to export your active Postmatic users (from users.php)
- Finally nailed a responsive image bug in some versions of gmail. Happy all the images!
- Revised new post notification to make it even more obvious to reply to the email to leave a comment.
- Added support for Nextgen Gallery images in emails.
- Added support for displaying content from wp-types and views.
- Removed support for custom post types. Temporarily. Sorry! We need to figure out a few things first.
- Internationalization: Spanish translation. French and Dutch coming soon.


= 1.0 beta 5 =
Just a quick fix to address a couple of outstanding issues.

- Improved support for full sized images aligned center. They will now be displayed responsively on mobile.
- Changed default FROM name to reflect the blog name.

= 1.0 beta 4 =
- User management - see who is subscribed to what directly via the wp-admin users screen. Export coming soon.
- Support for custom post types! Choose which post types you want sent to your users.
- Fixed a bug in which the featured image would be placed in the email template even if you had asked it not to be.
- Fixed a bug wherein Postmatic would try to include the latest post in a subscription confirmation notification for subscribing to comments. 
- Revised the language shown in profile.php to make subscription settings easier to understand and change.


= 1.0 beta 3 =
- Toned down the opt-in text colors
- Awesome new comments template with support for gravatars as well as conversation recaps
- Added support for html and wpautop in invitation emails
- Internationalization support. Translations coming soon. Get in touch if you want to help: @gopostmatic
- We've added an option to disable widgets in the footer and instead display definable text
- Moved the advanced options to their own tab in the settings screen
- Lots of little bugs squashed
