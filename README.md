Localize
========

**Android Localization Platform for Collaborative Translation of Applications**

A simple platform for collaborative translation of Android applications. It can be used in the web browser, where users may sign up as a translator or developer. Developers may host their own projects and let translators contribute.

Developers may review contributions and export a ZIP file containing all translations with just a few clicks. The translations are exported as Android XML resources so they are ready to be deployed right away.

Hopefully, this will speed-up the development and localization process for you, especially if you are a small company or a single developer.

**Live Demo:** www.localize.li

Features
------

+ Easily set up project folders and manage your translations online
+ Import existing translation XML files from your Android projects
+ Export your collaboratively edited translations as Android-ready XML files
+ Support for string, string-array and plurals elements
+ Keep all translations for your projects in sync
+ Three levels of visibility: public, signed-in users and invite-only
+ Convenient review system for suggested translations and applying translators
+ Support for LTR languages (English, Spanish, French, etc.)
+ Support for RTL languages (Arabian, Hebrew, Persian, etc.)
+ 83 supported languages
+ Unlimited projects, phrases and contributors
+ 100% free and without restrictions
+ Open Source

Requirements
------

+ PHP
+ MySQL
+ mod_rewrite

Installation
------

+ Upload all files to a public directory on your webserver
+ Import the file `database.sql` into your MySQL database
+ Edit the first 20 lines of file `index.php` and update it with your own configuration
+ Make the directory `_output` writable to the PHP user

Contributing
------

Every contribution is warmly welcomed. Please fork this repository, apply your changes, and submit your contributions by sending a pull request.

Third-party libraries
------

+ jQuery by the jQuery Foundation and other contributors (MIT License: https://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt)

License
========

```
	Copyright 2013 delight.im

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

	http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
```
