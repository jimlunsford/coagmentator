"""Fixed disposable edge scenarios; no caller-selected forwarding policy."""
from pathlib import Path
import sys

case = sys.argv[1]
direct = case.startswith('direct') or case in ['invalid-certificate', 'wrong-certificate-host']
common = '''
    root /workspace/.runtime/wordpress/src;
    index index.php;
    access_log off;
    error_log /dev/null;
    server_tokens off;
    client_max_body_size 2m;
    ssl_certificate /etc/nginx/tls/server.crt;
    ssl_certificate_key /etc/nginx/tls/server.key;
    ssl_protocols TLSv1.2 TLSv1.3;
'''
if direct:
    body = '''
    location / { try_files $uri $uri/ /index.php?$args; }
    location ~ \\.php$ {
        try_files $uri =404;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS $https;
        fastcgi_param HTTP_AUTHORIZATION $http_authorization;
        fastcgi_pass php:9000;
    }
'''
else:
    # Every header decision is constant or derived from the edge's real socket.
    # Negative cases deliberately model a broken trusted edge, not request flags.
    proto = {'proxy-multiple-scheme': 'https,http', 'proxy-malformed-scheme': 'HTTPS'}.get(case, '$scheme')
    host = {'proxy-multiple-host': 'wordpress.test,evil.test', 'proxy-malformed-host': 'wordpress.test:443'}.get(case, 'wordpress.test')
    forwarded = 'proto=https;host=evil.test' if case == 'proxy-conflicting-forwarded' else ''
    auth = '' if case.startswith('proxy-strip') else '$http_authorization'
    body = f'''
    if ($http_host != wordpress.test) {{ return 403; }}
    location = /wp-json/coagmentator/v1/site_info {{
        {'return 307 https://redirect.test/c03b-sink.php;' if case == 'redirect' else ''}
        proxy_pass http://origin:8080;
        proxy_set_header Host wordpress.test;
        proxy_set_header X-Forwarded-Proto "{proto}";
        proxy_set_header X-Forwarded-Host "{host}";
        proxy_set_header Forwarded "{forwarded}";
        proxy_set_header X-Forwarded-Port "";
        proxy_set_header X-Forwarded-For "";
        proxy_set_header Authorization "{auth}";
        proxy_set_header X-Authorization "";
        proxy_set_header X-Original-Authorization "";
        proxy_set_header Proxy-Authorization "";
    }}
    location / {{
        proxy_pass http://origin:8080;
        proxy_set_header Host wordpress.test;
        proxy_set_header X-Forwarded-Proto "{proto}";
        proxy_set_header X-Forwarded-Host "{host}";
        proxy_set_header Forwarded "{forwarded}";
        proxy_set_header X-Forwarded-Port "";
        proxy_set_header X-Forwarded-For "";
        proxy_set_header Authorization "{auth}";
        proxy_set_header X-Authorization "";
        proxy_set_header X-Original-Authorization "";
        proxy_set_header Proxy-Authorization "";
    }}
'''
edge = 'server {\n    listen 443 ssl;\n    listen 80;\n    server_name wordpress.test;\n' + common + body + '}\n'
# Independently verified same-CA destination, used only for redirect non-replay.
edge += '''
server {
    listen 443 ssl;
    server_name redirect.test;
    ssl_certificate /etc/nginx/tls/redirect.crt;
    ssl_certificate_key /etc/nginx/tls/redirect.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    root /workspace/.runtime/wordpress/src;
    access_log off;
    error_log /dev/null;
    location = /c03b-sink.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_AUTHORIZATION $http_authorization;
        fastcgi_pass php:9000;
    }
    location / { return 404; }
}
'''
Path('.runtime/c03b-edge.conf').write_text(edge)
