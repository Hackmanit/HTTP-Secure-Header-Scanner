# SIWECOS Documentation

This documentation describes the two modules "HTTP Secure Header Scanner" and "DOMXSS-Scanner".

# Startup using Docker

`docker run -d -p 80:8181 siwecos/hshs-domxss-scanner`


# HTTP Secure Header Scanner
This module scans the HTTP header of a specific URL and returns a report that can be used to improve the configuration for a better security.

## API-Call
`http://localhost/api/v1/header?url=http://siwecos.de`

### Sample output
```json
{
    "checks": {
        "Content-Type": {
            "result": false,
            "comment": "The header is set with the charset and follows the best practice.",
            "directive": [
                "text/html; charset=UTF-8"
            ]
        },
        "Content-Security-Policy": {
            "result": true,
            "comment": "The header is not set.",
            "directive": null
        },
        "Public-Key-Pins": {
            "result": true,
            "comment": "The header is not set.",
            "directive": null
        },
        "Strict-Transport-Security": {
            "result": true,
            "comment": "The header is not set.",
            "directive": null
        },
        "X-Content-Type-Options": {
            "result": false,
            "comment": "The header is set correctly.",
            "directive": [
                "nosniff"
            ]
        },
        "X-Frame-Options": {
            "result": false,
            "comment": "The header is set and does not contain any wildcard.",
            "directive": [
                "SAMEORIGIN"
            ]
        },
        "X-Xss-Protection": {
            "result": false,
            "comment": "The header is set correctly.\n\"mode=block\" is activated.",
            "directive": [
                "1; mode=block"
            ]
        }
    }
}

```


## Scanned headers and descriptions


### Content-Type (`Content-Type`)

##### Description
When a server sends a document to a user agent (eg. a browser) it also sends information in the Content-Type field of the accompanying HTTP header about what type of data format this is. This information is expressed using a MIME type label. Documents transmitted with HTTP that are of type text, such as text/html, text/plain, etc., can send a charset parameter in the HTTP header to specify the character encoding of the document.

##### Best-Practice
`text/html; charset=utf-8;`

##### Scan-Result
`false`:
- The header is set and contains a charset.

`true`:
- The header is not set correctly.

##### Impact and Feasibility (10/10)
A correct header with the setted charset prevents different XSS attacks that use other charsets than the original webpage so they can bypass XSS prevention.

It's easy and harmless to set the correct charset without affecting the sites content.




### Content-Security-Policy (`Content-Security-Policy`)

##### Description
Content Security Policy (CSP) requires careful tuning and precise definition of the policy. If enabled, CSP has significant impact on the way browser renders pages (e.g., inline JavaScript disabled by default and must be explicitly allowed in policy). CSP prevents a wide range of attacks, including Cross-site scripting and other cross-site injections.

##### Best-Practice
Best Practice is to use the CSP with `default-src 'none'` and without any `unsafe-eval` or `unsafe-inline` directives.

##### Scan-Result

`false`:
- The header is set does not contain `unsafe-eval` or `unsafe-inline`.

`true`:
- The header is not set or does contain `unsafe-eval` or `unsafe-inline`.

##### Impact and Feasibility (7/10)
The Content-Security-Policy can prevent a wide range of attacks that infiltrate external content and code. With the correct setting it's a powerful method to increase the sites security.

On the other hand it's often not possible to set a secure CSP header without modifying the website's source code.

Impact-Rating: 10/10 | Feasibility: 5/10




### Public-Key-Pins (`Public-Key-Pins`)

##### Description
HTTP Public Key Pinning (HPKP) is a security mechanism which allows HTTPS websites to resist impersonation by attackers using mis-issued or otherwise fraudulent certificates. (For example, sometimes attackers can compromise certificate authorities, and then can mis-issue certificates for a web origin.).

##### Best-Practice
`pin-sha256="<HASH>"; pin-sha256="<HASH>"; max-age=2592000; includeSubDomains`

##### Scan-Result

`false`:
- The header is set correctly.

`true`:
- The header is not set.

##### Impact and Feasibility (3/10)
For small and medium-sized enterprises as is the target group of SIWECOS this header is a 'nice to have' but not a absolutely must.

If this header is misconfigured your website would not be available for the users until the correct certificates are used or `max-age` is reached.



### Strict-Transport-Security (`Strict-Transport-Security`)

##### Description
HTTP Strict Transport Security (HSTS) is a web security policy mechanism which helps to protect websites against protocol downgrade attacks and cookie hijacking. It allows web servers to declare that web browsers (or other complying user agents) should only interact with it using secure HTTPS connections, and never via the insecure HTTP protocol.

##### Best-Practice
`max-age=63072000; includeSubdomains`

##### Scan-Result

`false`:
- The header is set correctly.

`true`:
- The header is not set.

##### Impact and Feasibility (10/10)
This is a must-have header for every webpage and easy and harmless to integrate.
The header guaranteed that the traffic between the server and client has to be encrypted to communicate.



### X-Content-Type-Options (`X-Content-Type-Options`)

##### Description
Setting this header will prevent the browser from interpreting files as something else than declared by the content type in the HTTP headers.

##### Best-Practice
`nosniff`

##### Scan-Result

`false`:
- The header is set correctly.

`true`:
- The header is not set.

##### Impact and Feasibility (6/10)
Easy to implement and no further adjustments on the website are needed.
Only effects Internet Explorer.




### X-Frame-Options (`X-Frame-Options`)

##### Description
X-Frame-Options response header improve the protection of web applications against Clickjacking. It declares a policy communicated from a host to the client browser on whether the browser must not display the transmitted content in frames of other web pages.

##### Best-Practice
Best Practice is to set this header accordingly to your needs.

Do not use `allow-from: *`

##### Scan-Result

`false`:
- The header is set correctly.

`true`:
- The header is not set or contains wildcards `*`.


##### Impact and Feasibility (9/10)
Prevents Clickjacking attacks.

Easy to implement and no further adjustments on the website are needed.




### X-Xss-Protection (`X-Xss-Protection`)

##### Description
This header enables the Cross-site scripting (XSS) filter in the browser.

##### Best-Practice
`1; mode=block`

##### Scan-Result

`false`:
- The header is set correctly.

`true`:
- The header is not set.


##### Impact and Feasibility (9/10)
Prevents reflected XSS attacks.

Easy to implement and no further adjustments on the website are needed.


# DOMXSS-Scanner
This module scans the given URL and checks for DOMXSS sinks and sources.

## API-Call
`http://localhost/api/v1/domxss?url=http://siwecos.de`

### Sample output
```json
{
    "checks": {
        "sinks":true,
        "sources":true
    }
}
```

## Scanned tasks and descriptions

### Sources (`sources`)

##### Description
A source is an input that could be controlled by an external (untrusted) source.
 > https://github.com/wisec/domxsswiki/wiki/Glossary

##### Scan-Result
`true`:
 - At least one source was found on the scanned URL.

`false`:
- No sources were found on the scanned URL

##### Impact (1/10)
The scan's result can only be used as an indication if there might be security vulnerabilities.
Further advanced tests would be needed to confirm if there are vulnerabilities on the site or not.


### Sinks (`sinks`)

##### Description
A sink is a potentially dangerous method that could lead to a vulnerability. In this case a DOM Based XSS.
 > https://github.com/wisec/domxsswiki/wiki/Glossary

##### Scan-Result
`true`:
 - At least one sink was found on the scanned URL.

`false`:
- No sinks were found on the scanned URL

##### Impact (2/10)
The scan's result can only be used as an indication if there might be security vulnerabilities.
Further advanced tests would be needed to confirm if there are vulnerabilities on the site or not.
