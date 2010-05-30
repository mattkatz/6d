<?php
$htaccess = <<<eos
Options +FollowSymLinks -MultiViews
RewriteEngine On
RewriteBase /6d

RewriteCond %{HTTPS} on
RewriteBase /6d

ErrorDocument 404 index.php

RewriteRule ^$ index.php [QSA]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php?/$1 [QSA,L]

# This block is required to do HTTP digest authentication in environments where PHP is executed
# as a CGI.
RewriteCond %{HTTP:Authorization} !^$
RewriteRule .* - [E=PHP_AUTH_DIGEST:%{HTTP:Authorization},L]

# for maintenance.
#DirectoryIndex maintenance.php
#RewriteRule ^$ maintenance.php [QSA]
#DirectoryIndex index.php
#RewriteRule ^/?([a-zA-Z0-9/\.^\?^\&]+)/?$ maintenance.php [QSA,L]
eos;

