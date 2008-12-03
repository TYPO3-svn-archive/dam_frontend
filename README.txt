
DAM Frontend is providing a powerful possibilty to select files out of the dam.

Main features:
* display category trees in the frontend to select files
* combine categories via "or selection"
* combine category selections via "and" selections between two cat trees
* providing File Downloads, which are manually selected via a second plugin
* Downloading files via a "pushfile", so that the fileadmin area can be secured via a .htaccess file
* Set up download restrictions via categories for fe_users / fe_groups (not finished yet!)

Important:
Language issue: currently there is no language overlay for the dam itsself. So if you're displaying categories or records from the dam, it is displayed as it is stored in the dam.

TypoScript:
All values that can be configured via TypoScript has htmlSpecialChars = 1 set by default.
You have to unset it for every field you do not want htmlSpecialChars used. Be aware of opening
XSS-Issues without htmlSpecialChars = 1!

Roadmap:
* RealURL Support

Development Plattform:
http://forge.typo3.org/projects/show/extension-dam_frontend

release 0.2.1 / .2008