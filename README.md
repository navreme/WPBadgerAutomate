=== WPBadgerAutomate ===
Contributors: Navreme Boheme
Tags: openbadges
Requires at least: 
Tested up to: 3.5

Badge Automation

== Description ==


== Frequently Asked Questions ==

reqired
=======

- WPBadger plugin required
- permalinks must be enabled
- openbadges API doesnt work in IE browser!!!

install
=======

- install plugin
- add WPBadgerAutomate Widget to right column - needed for waiting badges asertation and for direct asertation


post type and fields
====================

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


== Changelog ==

= 0.1 =
* initial release
