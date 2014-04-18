# Apache

## Hide public `/icons` folder (alias)

 1. Find `/etc/apache2/mods-available` directory
 2. Open `alias.conf` file
 3. Inside of `<Directory "/usr/share/apache2/icons">...</Directory>`, replace `Options Indexes MultiViews` with `Options -Indexes MultiViews`