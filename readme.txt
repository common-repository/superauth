=== SuperAuth Passwordless Sign In & Alert Service ===
Contributors: SuperAuth
Donate link: http://www.doctorswithoutborders.org/
Tags: username, password, passwords, no, fingerprint, superauth, auth, web, app, login, push, notification, android, iOS, windows, iPhone, iPad, phone, mobile, smartphone, computer, oauth, sso, authentication, encryption, ssl, secure, security, strong, harden, single sign-on, signon, signup, sign in, login, log in, sign out, lock, unlock, alert,  wp-login, 2 step authentication, two-factor authentication, two step, two factor, 2-Factor, 2fa, two, tfa, mfa, qr, multi-factor, multifactor
Requires at least: 4.7.0
Tested up to: 6.4.3
Stable tag: 1.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

SuperAuth provides passwordless sign in, alert service, sign out from all devices, lock or unlock user account from any computer, smartphone, tablet, kiosk, or other device. SuperAuth also ensures that you are secure and protected from phishing.

== Description ==

[SuperAuth](https://SuperAuth.com)  provides the following services.

- **Passwordless Sign in**: Users can login without username and password.

- **Alert Service**: Alert the user when anyone signed in using their account, including password sign in.

- **Sign out from all devices**: Users can sign out of your website on all devices using the SuperAuth app.

- **Lock / Unlock account**: Users can lock or unlock their account from your website using the SuperAuth app.

You can integrate SuperAuth services in your websites or apps within 30 seconds. SuperAuth is focused on ease of integration, ease of use, and extensibility. SuperAuth also ensures that your site is secured and protected from phishing. 

You can easily add or remove SuperAuth plugin without disturbing your user management. SuperAuth plugin works with your existing users also. 

https://www.youtube.com/watch?v=K8yALTahBkI

https://www.youtube.com/watch?v=rAeueEqQmwU

= SuperAuth Features =
- **No password required**: Heck, users never USE a password or username ever again!

- **Easy integration**: No need to change your database or existing user management systems. SuperAuth users map seamlessly onto your existing users. 

- **Alert Service**: Notify users when anyone signed with their account. 

- **Sign out**: If a user didn't sign in, they can simply sign out of your website on all devices by simply pressing a button from the SuperAuth app. 

- **Lock / Unlock**: Users can lock and unlock their account by simply pressing a button from the SuperAuth app. 

= More Information =

Visit the <a href="https://superauth.com/">SuperAuth website</a> for documentation and support.

== Installation ==

https://www.youtube.com/watch?v=rAeueEqQmwU

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'SuperAuth'
3. Activate SuperAuth from your Plugins page.

= From WordPress.org =

1. Download SuperAuth.
2. Upload the 'SuperAuth' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate SuperAuth from your Plugins page.

= Once Activated =

Visit 'Settings > SuperAuth' and configure client id and client secret key.

1. Login to [SuperAuth.com](https://SuperAuth.com) and register your website under webapps to get API credentials.
2. During webapp creation, specify the return URL as specified in WordPress SuperAuth plugin setting page.
3. Get the client id and secret key. Configure in WordPress SuperAuth plugin setting page.
4. To enable alert service, please select the service check box in both webapps configuration (step 1) and Wordpress settings (step 3).

= Once Configured =

* You will see the SuperAuth login button in the login and register page.
* You can also use [superauth] shortcode to add the login button.

If you need any support, please <a href="https://superauth.com/#contact">contact us.</a>

== Frequently Asked Questions ==

= Are the SuperAuth app and plugin free? =

Yes. SuperAuth mobile app is FREE. Basic free tier allows free authentication in any WordPress site.

= How can I enable alert service? =

To enable alert service, you need to select the checkbox in two places. 
1. Sign in to SuperAuth.com and select the alert service in your webapps configuration.
2. Login to your Wordpress website, go to settings > SuperAuth and select the alert service. 

= How can WordPress admin unlock users? =

WordPress admin can unlock users, who locked the account using SuperAuth app, from the Users page.

= Will this work on WordPress multisite? =

Yes! If your WordPress installation has multisite enabled, each site needs to be configured with separate SuperAuth API credentials.

= Can I use my existing WordPress theme? =

Yes! SuperAuth works with nearly every WordPress theme.

= Where can I get support? =

Our community provides free support at <a href="https://superauth.com/#contact">https://SuperAuth.com</a>.

= Where can I report a bug? =

Report bugs and suggest ideas at <a href="https://superauth.com/contactus">https://superauth.com/contactus</a>.

== Screenshots ==

1. **Login Page** - WordPress login page integrated with SuperAuth passwordless login.
2. **SuperAuth Settings** - Easy integration. Just specify your API credentials in the SuperAuth settings page.
3. **Sample Website** 
3. **SuperAuth App**

== Upgrade Notice ==

= 1.1.4 =
* Alert, sign out, lock / unlock features added.

= 1.1.3 =
* Improved performance.

= 1.1.2 =
* Improved performance.

= 1.1.1 =
* Add an action after the wp login method.

= 1.1.0 =
* Fix to work with other security plugins.

= 1.0.0 =
* Initial version.

== Changelog ==

= 1.1.4 =
* Alert, sign out, lock / unlock features added.

= 1.1.3 =
* Improved performance.

= 1.1.2 =
* Improved performance.

= 1.1.1 =
* Add an action after the wp login method.

= 1.1.0 =
* Fix to work with other security plugins.

= 1.0.0 =
* Initial version.