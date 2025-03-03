# The Ultimate .htaccess Configuration Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Basic .htaccess Concepts](#basic-htaccess-concepts)
3. [Web Server Security](#web-server-security)
4. [URL Rewriting and Redirection](#url-rewriting-and-redirection)
5. [Performance Optimization](#performance-optimization)
6. [Error Handling](#error-handling)
7. [Advanced Configurations](#advanced-configurations)
8. [Troubleshooting Common Issues](#troubleshooting-common-issues)
9. [Best Practices](#best-practices)

## Introduction

The `.htaccess` (hypertext access) file is a powerful configuration file used on Apache web servers to control various aspects of your website's functionality. It allows for server configuration changes on a per-directory basis, making it an invaluable tool for web developers and system administrators alike.

This guide covers everything from basic concepts to advanced techniques, helping you harness the full potential of `.htaccess` files to enhance your website's security, performance, and functionality.

## Basic .htaccess Concepts

### What is .htaccess?

The `.htaccess` file is a directory-level configuration file supported by the Apache HTTP server. It provides a way to make configuration changes on a per-directory basis without requiring root access to the server's main configuration file.

### File Location and Structure

- The `.htaccess` file should be placed in the directory you want the directives to apply to
- Changes affect the directory containing the file and all subdirectories
- File must be plain text and have the exact filename ".htaccess" (including the leading dot)
- No file extension should be used

### Basic Syntax

```apacheconf
# This is a comment
DirectiveName DirectiveValue
<DirectiveBlock>
    Directive1 Value1
    Directive2 Value2
</DirectiveBlock>
```

### Testing .htaccess Changes

Before implementing any changes to your production environment, always test your configurations in a staging environment. A single syntax error can make your entire website inaccessible.

## Web Server Security

### Directory Access Protection

Restrict access to specific directories with password protection:

```apacheconf
# Protect a directory with password
AuthType Basic
AuthName "Restricted Area"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

To create a `.htpasswd` file:

```bash
htpasswd -c /path/to/.htpasswd username
```

### Prevent Directory Listing

Stop users from browsing your directory contents:

```apacheconf
# Disable directory browsing
Options -Indexes
```

### Block Specific IP Addresses

```apacheconf
# Block specific IP addresses
<RequireAll>
    Require all granted
    Require not ip 192.168.1.1
    Require not ip 10.0.0.1
</RequireAll>
```

### Protect Against Cross-Site Scripting (XSS)

```apacheconf
# Add XSS protection header
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

### Implement Content Security Policy (CSP)

```apacheconf
# Basic Content Security Policy
<IfModule mod_headers.c>
    Header set Content-Security-Policy "default-src 'self';"
</IfModule>
```

### Prevent Access to Sensitive Files

```apacheconf
# Block access to sensitive files
<FilesMatch "(\.htaccess|\.htpasswd|\.git|\.env|\.DS_Store)">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

## URL Rewriting and Redirection

### Enable Rewrite Engine

```apacheconf
# Enable the rewrite engine
<IfModule mod_rewrite.c>
    RewriteEngine On
</IfModule>
```

### Redirect HTTP to HTTPS

```apacheconf
# Redirect all HTTP traffic to HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

### Remove www from URL

```apacheconf
# Redirect www to non-www
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
    RewriteRule ^(.*)$ https://%1/$1 [R=301,L]
</IfModule>
```

### Add www to URL

```apacheconf
# Redirect non-www to www
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP_HOST} !^www\. [NC]
    RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]
</IfModule>
```

### Create Clean URLs for SEO

```apacheconf
# Rewrite URLs to hide PHP extension
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME}.php -f
    RewriteRule ^(.*)$ $1.php [L]
</IfModule>
```

### Custom URL Routing (For MVC Frameworks)

```apacheconf
# Route all requests to index.php except for existing files/directories
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

## Performance Optimization

### Enable GZIP Compression

```apacheconf
# Enable GZIP compression
<IfModule mod_deflate.c>
    # Compress HTML, CSS, JavaScript, Text, XML and fonts
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
    AddOutputFilterByType DEFLATE application/x-font
    AddOutputFilterByType DEFLATE application/x-font-opentype
    AddOutputFilterByType DEFLATE application/x-font-otf
    AddOutputFilterByType DEFLATE application/x-font-truetype
    AddOutputFilterByType DEFLATE application/x-font-ttf
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE font/opentype
    AddOutputFilterByType DEFLATE font/otf
    AddOutputFilterByType DEFLATE font/ttf
    AddOutputFilterByType DEFLATE image/svg+xml
    AddOutputFilterByType DEFLATE image/x-icon
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
</IfModule>
```

### Browser Caching

```apacheconf
# Set browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/x-javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType application/x-javascript "access plus 1 month"
    ExpiresByType application/x-shockwave-flash "access plus 1 month"
    ExpiresByType image/x-icon "access plus 1 year"
    ExpiresDefault "access plus 2 days"
</IfModule>
```

### ETags Configuration

```apacheconf
# Configure ETags
<IfModule mod_headers.c>
    Header unset ETag
</IfModule>
FileETag None
```

### Keep-Alive Settings

```apacheconf
# Enable Keep-Alive
<IfModule mod_headers.c>
    Header set Connection keep-alive
</IfModule>
```

## Error Handling

### Custom Error Pages

```apacheconf
# Set custom error pages
ErrorDocument 400 /errors/400.html
ErrorDocument 401 /errors/401.html
ErrorDocument 403 /errors/403.html
ErrorDocument 404 /errors/404.html
ErrorDocument 500 /errors/500.html
```

### Prevent Server Signature

```apacheconf
# Hide server information
ServerSignature Off
```

### PHP Error Handling

```apacheconf
# Configure PHP error settings
php_flag display_errors off
php_value error_reporting 0
```

## Advanced Configurations

### Set Default Character Set

```apacheconf
# Set default character set
AddDefaultCharset UTF-8
```

### Control MIME Types

```apacheconf
# Define MIME types
AddType application/x-web-app-manifest+json .webmanifest
AddType image/svg+xml .svg .svgz
AddType application/font-woff .woff
AddType application/font-woff2 .woff2
```

### Set Environment Variables

```apacheconf
# Set environment variables
SetEnv APPLICATION_ENV production
```

### Enable HTTP/2

```apacheconf
# Enable HTTP/2 if available
<IfModule mod_http2.c>
    Protocols h2 h2c http/1.1
</IfModule>
```

### Limit Request Size

```apacheconf
# Limit upload size
LimitRequestBody 10485760
```

### Custom PHP Configuration

```apacheconf
# Custom PHP settings
php_value upload_max_filesize 20M
php_value post_max_size 20M
php_value max_execution_time 300
php_value max_input_time 300
```

### Restrict Access by User Agent

```apacheconf
# Block specific user agents
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTP_USER_AGENT} (bot|scraper|spider|crawl) [NC]
    RewriteRule .* - [F,L]
</IfModule>
```

### Enable Cross-Origin Resource Sharing (CORS)

```apacheconf
# Enable CORS
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
</IfModule>
```

## Troubleshooting Common Issues

### Diagnosing 500 Internal Server Errors

When you encounter a 500 Internal Server Error after modifying your `.htaccess` file:

1. Check for syntax errors in your `.htaccess` file
2. Verify that all required Apache modules are enabled
3. Temporarily comment out sections of your `.htaccess` file to isolate the problem
4. Check your server's error logs for specific error messages

### Handling Infinite Redirect Loops

If your redirects are causing an infinite loop:

1. Ensure your rewrite conditions are specific enough
2. Add conditions to prevent rewriting URLs that have already been rewritten
3. Use the `[L]` flag to stop processing rules once a match is found

```apacheconf
# Fix for infinite redirect loops
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Only redirect if not already on HTTPS
    RewriteCond %{HTTPS} off
    # Only redirect if not already on www
    RewriteCond %{HTTP_HOST} !^www\. [NC]
    RewriteRule ^(.*)$ https://www.%{HTTP_HOST}/$1 [R=301,L]
</IfModule>
```

### Module Not Available Errors

If your server reports that a module is not available:

1. Check if the module is installed on your server
2. Wrap module-specific directives in `<IfModule>` blocks
3. Consider alternative approaches if the module cannot be enabled

```apacheconf
# Safe module usage
<IfModule mod_rewrite.c>
    # Rewrite rules here
</IfModule>

<IfModule !mod_rewrite.c>
    # Fallback for when mod_rewrite is not available
    ErrorDocument 503 "This website requires mod_rewrite to be enabled."
</IfModule>
```

## Best Practices

### Security Recommendations

1. Always use `<IfModule>` to check for module availability
2. Keep your `.htaccess` file outside web-accessible directories when possible
3. Implement rate limiting for sensitive operations
4. Regularly audit your `.htaccess` configurations
5. Use specific permissions rather than broad access rules

### Performance Optimization Tips

1. Minimize the number of rewrite rules
2. Use the `[L]` flag to stop processing rules when appropriate
3. Combine multiple redirect rules into a single rule when possible
4. Test performance impact of changes in a staging environment

### Maintenance and Documentation

1. Add comments to explain complex configurations
2. Keep a backup of working `.htaccess` files
3. Document changes and reasons for specific configurations
4. Test thoroughly after making changes

---

*This article guide was crafted to help web developers and administrators harness the full power of .htaccess configurations. For more in-depth technical resources, visit [www.skytup.com](https://www.skytup.com)*
