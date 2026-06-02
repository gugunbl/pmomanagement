# Security Deployment Checklist

## Laravel production

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Run `php artisan config:cache` after updating `.env`.
- Run `php artisan route:cache` only if all routes are cache-safe.
- Rotate `APP_KEY`, database password, and third-party credentials if the project root was ever exposed publicly.

## Nginx document root

The web server document root must point to Laravel's `public` directory, not the project root.

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/assetmgt/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(env|sql|bak|zip|tar|gz|log|map|md|sh)$ {
        deny all;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php7.4-fpm.sock;
    }
}
```

## Malware follow-up

- Scan the deployed server using server-side antivirus or malware scanner.
- Review recently modified PHP files and writable upload folders.
- Check web server access logs for requests to `.env`, `vendor`, `storage`, `shell.php`, `cmd.php`, and unexpected upload paths.
