watchmysql for PHP 7.x - cPanel Plugin (based on https://www.ndchost.com/cpanel-whm/addons/watchmysql/)

### Important information
* I am not the owner of the original code. The original author is NDCHost (https://www.ndchost.com/cpanel-whm/addons/watchmysql/) 
* I used this cPanel Plugin in many servers and I found out that it doesn't work for server that have PHP 7.x installed. It was throwing an internal server error.
* Based on the above I decided to look the code and make a fix. I am sure many people are looking for a working version. 
----
### How to install

* `mdkir /usr/src/watchmysql`

* `cd /usr/src/watchmysql`

* `git clone https://github.com/konstantinosbotonakis/watchmysql.git`

* `sh ./install`

----
### Change Log
- 10.1
Added PHP 7.x support and MySQLi