
DAM Frontend is providing a powerful possibilty to select files out of the dam.

Main features:
* display category trees in the frontend to select files
* combine categories via "or selection" 
* combine category selections via "and" selections between two cat trees
* providing File Downloads, which are manually selected via a second plugin
* Downloading files via a "pushfile", so that the fileadmin area can be secured via a .htaccess file
* Set up download restrictions via categories for fe_users / fe_groups (not finished yet!)
* upload files to the dam

Important:
Language issue: currently we support is no language overlay for the dam entries itsself.

TypoScript:
All values that can be configured via TypoScript has htmlSpecialChars = 1 set by default.
You have to unset it for every field you do not want htmlSpecialChars used. Be aware of opening
XSS-Issues without htmlSpecialChars = 1!

Roadmap:

* language overlay for dam entries

Development Plattform:
http://forge.typo3.org/projects/show/extension-dam_frontend

release 0.3.1 / 19.03.2009