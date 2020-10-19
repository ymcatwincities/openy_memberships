# Memberships Builder framework for OpenY.

## Description

By installing this module, you will have completed solution for membership search and select functionality for you Open Y website.
It includes landing pages and application for memberships builder.

## Requirements

PHP 7.2+ with ini_set function enabled,

Open Y 2.5+

## Installation

You can install this module with help of composer. Please, use this command:

`composer config minimum-stability dev`
`composer require openy/openy_memberships`

We strongly recomment to use composer, because OpenY Membership project requires a lot of dependencies.

You need to build styles using this commands

`cd modules/openy_memberships_front/app`
`npm install`
`cd ../../..`
`npm install`
`npm run scss-build`

To have demo content you need to install additional package:

`composer require openy/openy_memberships_demo`

Steps for installation - CI sources https://github.com/ymcatwincities/openy-cibox-build/blob/master/devops/reinstall/vars/environments/membership_framework_env.yml

- Install Open Y standard - no demo content!

- run ```drush then openy_carnation```

- run ```drush en openy_addthis openy_demo_bfooter openy_demo_tarea openy_demo_nbranch migrate_tools openy_memberships_demo```

- run ```drush mim --group=openy_demo_fblock```

- run ```drush mim --group=openy_demo_term```

- run ```drush --group=openy_demo_nbranch```

- Visit /membership-builder page to check how it works. Step 6 installs demo branches. You can skip it if you are adding Membership Framework to currently installed Open Y.
