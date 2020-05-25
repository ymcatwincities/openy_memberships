# Memberships Builder framework for OpenY.

## Description

By installing this module, you will have completed solution for membership search and select functionality for you Open Y website.
It includes landing pages and application for memberships builder.

## Requirements

PHP 7.2+ with ini_set function enabled,

Open Y 2.5+

## Installation

You can install this module with help of composer. Please, use this command:

`composer require ymcatwincities/openy_memberships`

We strongly recomment to user composer, because OpenY Membership project requires a lot of dependencies.

Once you enable this module, you will have predefined demo content and configuration installed.
If you open /membership-types page, you will see predefined content and links to memberships builder application.

You need to build styles using this commands

`cd modules/openy_memberships_front/app`
`npm install`
`cd ../../..`
`npm install`
`npm run scss-build`
