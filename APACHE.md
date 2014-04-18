# Apache

## Hide public `/icons` folder (alias)

 1. Find `/etc/apache2/mods-available` directory
 2. Open `alias.conf` file
 3. Inside of `<Directory "/usr/share/apache2/icons">...</Directory>`, replace `Options Indexes MultiViews` with `Options -Indexes MultiViews`

## Hide public `/manual` folder (alias)

 1. Find `/etc/apache2/conf.d` directory
 2. Open 'apache2-doc' file
 3. Comment out the following line: `Alias /manual /usr/share/doc/apache2-doc/manual/`