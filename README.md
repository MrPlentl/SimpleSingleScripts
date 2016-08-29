# Simple Single Scripts
### Cause sometimes I just need a Simple Solution with a Single Script

### Always Under Development
I'm always adding new stuff. Though everything checked in should word... theoretically.

## The Goal
Mostly all Command Line based, everything in here is is not just simple and easy to use, but it's small. Only one file to be exact. 
The point of these scripts is to execute their purpose while making the smallest footprint.

##Apache / PHP Updater
### If you're a Windows user like me and find it to be a pain to stay on the latest versions of Apache and PHP, this script should speed up the process.

SETUP
 * OPEN: the Apache-PHP-Updater.php script and look for the configuration section
 * EDIT: Set the Apache and PHP locations, also add any configuration files you will want copied to the new installation.
 * NOTE: If this is your first time running the script, set QUICK_INSTALL to FALSE to add sleep() breaks so you can see what is going on.
 * DOWNLOAD: the latest version of [Apache](https://www.apachelounge.com/download/) and [PHP](http://windows.php.net/download/) that matches your current installation.
 * MOVE: the Apache-PHP-Updater.php script and the downloaded Apache/PHP to the same location
 * STOP: the Apache Service. Without loading external dlls, this just didn't seem possible programmatically.
 * NAVIGATE: Open and command line and navigate to the Apache-PHP-Updater.php script location.
 * RUN: php Apache-PHP-Updater.php - PHP must be added to your System Environment Path, otherwise you will need to list the full path to the php.exe

Script Process
 * Welcome Message
 * Scan Current Directory for "http" and "php" zip files
 * Lists all available Apache / PHP Zip files
 * USER - Chooses Apache / PHP Version to Install
 * MSG - The current versions of the following files will be reused from previous installations:
 * (Reused files are defined in the configuration section at the top of this script)
 * DISPLAY - ReusedFiles Array (This )
 * MSG - Ctrl->C to Quit
 * SLEEP - 5 seconds
 * MSG - Starting the Update Process...
 * RENAME - Old Apache to <DIR NAME>_backup_<timestamp>
 * RENAME - Old PHP to <DIR NAME>_backup_<timestamp>.
 * EXTRACT - Create __processing folder and Extract Apache/PHP to it
 * SCAN - Look for the Config directory to validate Apache/PHP roota
 * MOVE - Move the ReusedFiles from Backup <DIR NAME> to the Processing
 * MOVE - Process Apache/PHP and move to the Previous Apache/PHP Install Directories
 * DELETE __processing folder
 * DONE

## Reporting a bug

To report a bug simply create a
[new GitHub Issue](https://github.com/MrPlentl/SimpleSingleScripts/issues/new) and describe
your problem or suggestion. I welcome all kinds of feedback (especially efficiency techniques) regarding the
any of my SimpleSingleScripts but not limited to:

 * When my SSS don't work as expected
 * When my SSS blows up and flatout doesn't work

Before reporting a bug look around to see if there are any open or closed tickets
that cover your issue. Feel free to shoot me a [Tweet](https://twitter.com/thabamboozlr/) if you just have a question.

## License

All files are published using [GNU General Public License (GPL) version 3](https://www.gnu.org/licenses/license-list.html#GNUGPL).
I'm guessing I picked the right one, but basically, I don't care what you do with this code.


## The SimpleSingleScripts Team

There is no "I" in Team... but in this case, it's just me. :) My SimpleSingleScripts are currently maintained by [Brandon Plentl](https://github.com/MrPlentl),

## Thank you!

I really appreciate all kinds of feedback and contributions. Let me know if any of this is useful!