# DLAW (Drupal for Legal Aid Websites)

DLAW is a Drupal distribution for building legal aid public information websites. DLAW is developed and maintained by Urban Insight, Inc. (http://urbaninsight.com). Development of the DLAW has been supported by Legal Service Corporation (LSC) Technology Initiative Grants. For more information see http://openadvocate.org

## DLAW 9 release
DLAW version 9 was built with Drupal 9. The upgrade includes visual theme refreshments, greater control over homepage layout, adoption of new legal aid standards for schema.org, adoption of WCAG 2.1 accessibility standards, and adoption of the new NSMI taxonomy.

DLAW9 was developed by Urban Insight in partnership with Kansas Legal Services, with funding provided by the Legal Services Corporation. For a hosted and managed version of DLAW, please visit OpenAdvocate.org.


## Lando support
`.lando.yml` file is included for the users of [Lando](https://lando.dev/).

## Setup Guide (for Lando users)
1. Clone the repo
2. Run `lando start` to download and start the project containers.
3. Run `lando composer install` to fetch composer managed code.
4. Install site with drush
   1. `lando drush si dlaw --db-url=mysqli://drupal9:drupal9@database/drupal9 --account-name=admin`
5. Login as admin (user-1). Password is provided from previous step.
6. Create a new Landing Page that will become the homepage
   1. At https://dlaw9-dist.lndo.site/node/add/landing_page
   2. Until then, https://dlaw9-dist.lndo.site will show "Page not found".
   3. It will save as node #1. Enrich the home page by adding paragraph components.
7. Visit the site at `https://dlaw9-dist.lndo.site`

**If you are installing the site without Lando, a bit of Drupal installation experience should do. You'll need to setup Solr server on your own.**


## Set Google Analytics Web Property ID in settings.php
```
// Override Google Analytics Web Property
$config['google_analytics.settings']['account'] = 'UA-12345678-1';

Or for both UA & GA4

// Override Google Analytics Web Property
$config['google_analytics.settings']['account'] = 'UA-12345678-1,G-ABCDEFGHI';
```
