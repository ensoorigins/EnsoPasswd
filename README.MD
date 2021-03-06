# Requirements

* XAMP Stack
   * Apache >= 2
   * PHP >= 7
      * php-openssl
      * php-mysql
      * php-ldap (if used)
  * Relational DB system (MariaDB advised, any MySQL compatible system may function)

# Installation instructions

1. Extract the platform source onto the document root;
2. Create a database for Passwd to store data on;
2. Access the setup located at .../setup/ through a browser;
      * Ensure the directory you specify for the encryption key location has proper permission to the user running the setup script (write required pre-setup, read-only may be set and and is in fact advised post-setup).
3. Delete the setup folder to ensure that your settings cannot be malliciously overwritten;
4. The platform should be successfully installed on your webserver!

## Extra notes

If you have issues with the redirections, please check the index.html file at the document root, ensure it points to the ./web/ directory of your document root such as the following, as a final attempt use a fully fledged absolute URL.

```html
<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="1; url=web/">
        <script type="text/javascript">
            window.location.href = "web/"
        </script>
        <title>Page Redirection</title>
    </head>
    <body>
        <!-- Note: don't tell people to `click` the link, just tell them that it is a link. -->
        If you are not redirected automatically, follow this <a href='web/'>Passwd</a>.
    </body>
</html>
```

## Website

Visit [Enso Origins](https://enso-origins.com/home/)