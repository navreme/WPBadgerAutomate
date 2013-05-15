#WPBadgerAutomate#

##Overview##

[TODO - be more specific]

WPBadgerAutomate is a WordPress plugin that extends WPBadger plugin https://github.com/davelester/WPBadger . It creates "autoawards" - processes that automaticaly issue badges based on preconfigured criteria.
Current version is going to provide following features:
* issue selected badge after registration
* issue selected badge based on accessing configurable unique URL
* issue selected badge based on earning some other badge/s (hierarchization)
And also:
* page with the overview of autoawards (with respect to its "visibility"; some could be secret;)

##Installation##

###Requirements###

- WPBadger plugin required
- permalinks must be enabled
- openbadges API doesnt work in IE browser!!!

###Install###

- install plugin
- add WPBadgerAutomate Widget to right column - needed for waiting badges asertation and for direct asertation

##Instructions for Using WPBadgerAutomate##

[TODO - be more specific; synch texts]

* Add Badges using WPBadger plugin as usual.
* Click the "Autoawards" link in menu and Add a new autoaward. Select type (after registration, based on URL, hierarchy)
* select Badge to be awarded automatically
* based on a type, set other required parameters (e.g. URL, select prerequisit badge/s)
* set Visibility (what should be publically available to users - from requirements super-secret to known)
* set limits for availability based on date (from-to) 
* Submit

Thats it! The whole process of awarding of badge follows the process model of WPBadger, therefore emails are being sent and has to be approved...

##Initiated and sponzored by##

Jakub Štogr (Navreme Boheme) - www.navreme.cz - stogr@navreme.cz
 for the purpose of DisCo 2013 international conference - www.disconference.eu
 and www.BadgeBridge.net

##Tech doc##

###Post type and fields###

post_tape: autoaward

custom meta post fields:

wpbadger-autoaward-choose-badge - badge ID

wpbadger-autoaward-choose-award-type - autoaward type (1 = po registraci, 2 = na URL, 3 = po získání kombinace badgů)

wpbadger-autoaward-date-start - datum odkdy je možné udělení (prázdné = bez limitu), testuje se v okamžiku odeslání e-mailu nebo zobrazení JavaScript kódu pro sběr

wpbadger-autoaward-date-end - datum dokdy je možné udělení (prázdné = bez limitu), testuje se v okamžiku odeslání e-mailu nebo zobrazení JavaScript kódu pro sběr

wpbadger-autoaward-visibility-page - viditelnost na stránce /autobadges/ (1 = zobrazí se)

wpbadger-autoaward-visibility-image - viditelnost obrázku na stránce /autobadges/ (1 = zobrazí se)

wpbadger-autoaward-visibility-title - viditelnost názvu na stránce /autobadges/ (1 = zobrazí se)

wpbadger-autoaward-visibility-description - viditelnost popisky na stránce /autobadges/ (1 = zobrazí se)

wpbadger-autoaward-badges - čárkami oddělený seznam badge ID, má význam pouze pro typ 3 (po získání kombinace badgů)

wpbadger-autoaward-status - stav (Enabled/Disabled)

wpbadger-autoaward-direct - přímé přidělení/odeslán emailu s odkazem pro získání (Yes = přímé přidělení, No = přes email)

wpbadger-autoaward-usedby - čárkou oddělený seznam e-mailů uživatelů, kterým by tento autoaward udělen, slouží jako ochrana proti opakovanému přidělování autoawardu za URL a za kombinaci badgů

wpbadger-autoaward-salt