=== Postmatic - WordPress Subscriptions & Comments by Email ===
Contributors: vernalcreative
Tags: email, subscription, comments, posts, reply, subscribe, mail, listserve, mailing, subscriptions, fantasticallyamazing
Requires at least: 3.9
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Postmatic lets your readers subscribe to comments or new posts by email. And leave a comment by simply hitting reply. No forms. No browsers. Easy.

== Description ==
[vimeo http://vimeo.com/108515948]

What’s the one thing people all over the world do a gazillion times a day? Check their email.
So that’s how Postmatic gets your content in front of your readers.
It's the easiest way for readers of your WordPress blog to see your content and interact with you. And each other.

Use Postmatic to let your users subscribe to comments. Instead of just being notified, they add a reply right from their inbox.

Send newsletters and posts in a beautiful mobile-ready template and let your subscribers send back a comment just by writing an email.

[Read our faq, play with a demo, and find out what you can do with Postmatic at gopostmatic.com](http://gopostmatic.com).


### Postmatic is in limited-release beta
> We are releasing a few hundred api keys per week. Download and install the plugin to join the waiting list, or jump right over to [our site](http://gopostmatic.com/beta) to join the list immediately. 

= Post Subscriptions =
Your content gets delivered to readers in an email. And they can comment just by hitting reply. No accounts. No forms. No browsers.

= Commenting by email =
Hit reply on any Postmatic email to comment on that post. You can send a reply on an existing post, too.

= Subscribe to Comments =
Email notifications for new comments? Sure! And just hit reply to leave a response. Everyone is in the loop—mobile and desktop. Or maybe just in Thunderbird.

= Intelligent Invitations =
Send subscription invitations to your existing commenters. Or, you can import your list from Feedburner, Mailchimp, Subscribe2 or Mailpoet.

= Mobile-Ready Template =
Your post will arrive in users' inbox as a beautiful, mobile-ready email. With support for images, galleries and shortcodes. You can even assign a header image and up to 3 native WordPress widgets for a totally customized footer.

= Total Compatability =
Postmatic comments are fully compatible with your existing WordPress discussion management workflow. Moderation, spam detection, comment voting and gravatars work right out of the box.

== Installation ==
Postmatic supports all the standard installation methods
[described in the codex](http://codex.wordpress.org/Managing_Plugins).

Once installed and activated, visit the plugin settings to:

1. Get an API key
2. Place the subscription widget
3. Customize your email template
3. Go Postmatic!

== Frequently Asked Questions ==

We have you covered in our [big FAQ](http://gopostmatic.com/faq/).

== Screenshots ==
1. A sidebar widget lets users subscribe to all site content, or just authors they are interested in.
2. New posts are sent as gorgeous mobile-ready emails. The user can just hit reply to send a comment. Nifty.
3. The footer of the email invites the user to to leave a comment or manage their subscription settings. Users can subscribe to comments on this post, unsubscribe from comments on the post, or leave their own reply.
4. Followup comments are also sent using a simpler email template. They are reply-enabled as well.
5. The plugin settings allow you to define a header image as well as up to 3 native widgets to the footer.
6. The plugin registers a sidebar for configuring footer widgets to use in your email template.
7. The invitation system is fantastic. Postmatic will send invitations to an imported list or let you choose from your existing community. Users can reply to the invitation to subscribe to your site.
8. A closeup of the invitation system. You can choose to send invites to an imported list, people who have commented recently, commented the most, or anyone that has ever commented.
9. Using the invitation system to send invites to the most active commenters.

== Changelog ==

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

