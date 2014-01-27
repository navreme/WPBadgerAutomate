#WPBadgerAutomate#

##Overview##

WPBadgerAutomate is a WordPress plugin that extends WPBadger plugin https://github.com/davelester/WPBadger . It creates "autoawards" - processes that automaticaly issue badges based on preconfigured criteria.

Current version provides following features:
* issue selected badge after registration
* issue selected badge based on accessing unique URL
* issue selected badge based on previous earning of some other badge/s (enabling hierarchization of badges)

And also:
* page with the overview of badges that could be issued automatically using autoawards (with respect to its "visibility"; some could be secret); URL: mydomain.me/autobadges/
* widget showing badges that are waiting to be issued

##Installation##

###Requirements###

* WPBadger plugin version 0.6.* required
 - NOTE: current version of WPBadger (0.7.0) is not supported!!
 - feel free to update/rewrite the code
* permalinks must be enabled
* openbadges API doesnt work in IE browser!!!

###Install###

- install plugin
- add WPBadgerAutomate Widget to right column - needed for waiting badges asertation and for direct asertation

##Instructions for Using WPBadgerAutomate##

* Add Badges using WPBadger plugin as usual
* Click the "Autoawards" link in menu and Add a new autoaward. Select type (after registration, based on URL, bonus=hierarchy)
* select Badge to be awarded automatically
* based on a type, set other required parameters (e.g. URL, select prerequisit badge/s)
* set Visibility (what should be publically available to users - from super-secret to publically available)
* set limits for availability based on date (from-to)
* set if badge should be awarded directly (without sending email) or indirectly (email will be sent as WPBadger does)
* set autoaward as enabled (default)
* Submit

Thats it! The whole process of awarding of badge follows the process model of WPBadger.

##Initiated and sponzored by##

Jakub Štogr (Navreme Boheme) - www.navreme.cz
 for the purpose of DisCo 2013 international conference - www.disconference.eu and www.BadgeBridge.net

##Tech doc##

###Post type and fields###

post_tape: autoaward

custom meta post fields:

* wpbadger-autoaward-choose-badge - badge ID
* wpbadger-autoaward-choose-award-type - autoaward type (1 = after registration, 2 = on URL, 3 = bonus after receiving some other badge/s)
* wpbadger-autoaward-date-start - awarding availibility "from" (empty= no limit), being tested during sending the email or when JavaScript code for assertion is trigger
* wpbadger-autoaward-date-end - awarding availibility "to" (empty= no limit), being tested during sending the email or when JavaScript code for assertion is trigger
* wpbadger-autoaward-visibility-page - visibility on page: /autobadges/ (1 = visible)
* wpbadger-autoaward-visibility-image - visibility of picture on page: /autobadges/ (1 = visible)
* wpbadger-autoaward-visibility-title - visibility of title on page: /autobadges/ (1 = visible)
* wpbadger-autoaward-visibility-description - visibility of description on page: /autobadges/ (1 = visible)
* wpbadger-autoaward-badges - comma-separated list of badge IDs; necessary only for the type 3 (bonus badge based on combination of badges)
* wpbadger-autoaward-status - status of autoaward (Enabled/Disabled)
* wpbadger-autoaward-direct - direct awarding vs. emailing instructions and link (Yes = direct, no email; No = sending email)
* wpbadger-autoaward-usedby - comma-separated list of emails that already received badge via autoaward; prevention agains repeated awarding the same badge based on types 2 (based on URL) and 3 (combination)
* wpbadger-autoaward-salt
