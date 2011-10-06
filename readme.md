About
=====

This is a small bundle of PHP scripts that will import an XML list of Jira issues into Github Issues using the Github API. The issue Type and Components are added as labels. Any issues that are not "Open" or "In Progress" are closed after creating them.

The included `sample_issues.xml` has been imported onto this repository so you can [see the results](https://github.com/noahm/Jira2Github/issues?labels=Sample+Issues).

Requirements
============

* PHP 5.3+ with cURL support

Usage
=====

1. Edit `config.php` to fit your purposes
2. Run `php import_issues.php`

Known Issues / Future Work
==========================

* If you have many thousands of issues you might reach the rate limit Github imposes on API requests. This script does not look for those headers and will have to be modified to support such large numbers.
* Created labels always use the hex code `000000`
* Created issues are never assigned to anyone
