# mailserver-config
Weboberfläche für den Mailserver der mithilfe von dem Tutorial von Christopf Haas erstellt wurde.
https://workaround.org/ispmail/wheezy

Die Oberfläche kann zurzeit nur ssha512 das wie in diesem Tutorial beschrieben ergenzt wurde.
https://blog.lordsandwurm.de/emailserver-mit-dovekot-datenbank-mit-sicheren-hashes/

Installation:
Alle Dateien in einen Ordner legen. Lesbar für euren Webserve.
config.php anpassen.

Desing:
Skeleton.css mit freundlicher Hilfe von Michael Schnurowatzki

SICHERHEIT:
Es sollte auf keinen Fall zugriff ohne SSL Verschlüsselung erfolgen, da die Passwörter im Klartext übertragen werden.
Desweiteren sollte ein webauth eingerichtet werden um die Seite besser zu schützen.

Configeditor für ssl einrichtung: https://blog.lordsandwurm.de/webserver-config-editor-fur-sicheres-ssl/

Beispielconfig für Nginx SERVERNAME durch eigenen Adresse ersetzen:
server
{
        listen 80;
        listen [::]:80;

        server_name SERVERNAME;

        return 301 https://SERVERNAME$request_uri;
}
server
{
        listen 443 ssl spdy;
        listen [::]:443 ssl spdy;
        server_name SERVERNAME;
        
        root /var/www/mailserver-config;
        index index.php;
        access_log /var/log/nginx/mailserver-config.access_log main;
        error_log /var/log/nginx/mailserver-config.error_log debug;

        location /
        {
                root /var/www/mailserver-config;
                index index.php;                
                auth_basic "Restricted";
                auth_basic_user_file /etc/nginx/.mailserver-config;
        }

        # Deny public access to config.php
        location ~* config.php
        { 
                deny all; 
        }

        location ~ \.php$
        {
                try_files $uri =404;
                fastcgi_pass unix:/var/run/php5-fpm.sock;
                fastcgi_index index.php;
                include /etc/nginx/fastcgi_params;
                fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                fastcgi_param HTTPS on;
        }

}

