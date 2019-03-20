# SIWECOS Documentation

This documentation describes the two modules "HTTP Secure Header Scanner" and "DOMXSS-Scanner".

# Startup using Docker

`docker run --rm --name siwecos-hshs-domxss-scanner -p 8000:2015 siwecos/hshs-domxss-scanner`

# HTTP Secure Header Scanner

This module scans the HTTP header of a specific URL and returns a report that can be used to improve the configuration for a better security.

## API-Call

Send a POST-Request to `http://localhost/api/v1/header`:

```
POST /api/v1/header HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache


{
  "url": "https://siwecos.de",
  "callbackurls": [
    "http://localhost:9002"
  ]
}
```

The parameters `url` and `callbackurls` are required:

- `url` must be a `string`.
- `callbackurls` must be an `array` with only contains one or more `string`s.

### Sample output

```json
{
  "name": "HEADER",
  "hasError": false,
  "errorMessage": null,
  "score": 66,
  "tests": [
    {
      "name": "CONTENT_SECURITY_POLICY",
      "hasError": true,
      "errorMessage": "HEADER_NOT_SET",
      "score": 0,
      "scoreType": "warning",
      "testDetails": []
    },
    {
      "name": "CONTENT_TYPE",
      "hasError": false,
      "errorMessage": null,
      "score": 100,
      "scoreType": "warning",
      "testDetails": [
        {
          "placeholder": "META",
          "values": ["<meta charset=\"UTF-8\" \\/>"]
        },
        {
          "placeholder": "CT_META_TAG_SET_CORRECT"
        },
        {
          "placeholder": "HEADER",
          "values": ["text\\/html; charset=UTF-8"]
        },
        {
          "placeholder": "CT_CORRECT"
        }
      ]
    },
    {
      "name": "PUBLIC_KEY_PINS",
      "hasError": true,
      "errorMessage": "HEADER_NOT_SET",
      "score": 0,
      "scoreType": "info",
      "testDetails": []
    },
    {
      "name": "STRICT_TRANSPORT_SECURITY",
      "hasError": true,
      "errorMessage": "HEADER_NOT_SET",
      "score": 0,
      "scoreType": "warning",
      "testDetails": []
    },
    {
      "name": "X_CONTENT_TYPE_OPTIONS",
      "hasError": false,
      "errorMessage": null,
      "score": 100,
      "scoreType": "warning",
      "testDetails": [
        {
          "placeholder": "HEADER",
          "values": ["nosniff"]
        },
        {
          "placeholder": "XCTO_CORRECT"
        }
      ]
    },
    {
      "name": "X_FRAME_OPTIONS",
      "hasError": false,
      "errorMessage": null,
      "score": 100,
      "scoreType": "warning",
      "testDetails": [
        {
          "placeholder": "HEADER",
          "values": ["SAMEORIGIN"]
        },
        {
          "placeholder": "XFO_CORRECT"
        }
      ]
    },
    {
      "name": "X_XSS_PROTECTION",
      "hasError": false,
      "errorMessage": null,
      "score": 100,
      "scoreType": "warning",
      "testDetails": [
        {
          "placeholder": "HEADER",
          "values": ["1; mode=block"]
        },
        {
          "placeholder": "XXSS_CORRECT"
        },
        {
          "placeholder": "XXSS_BLOCK"
        }
      ]
    }
  ]
}
```

## Scanned headers and descriptions

### Content-Type (`Content-Type`)

##### Description

When a server sends a document to a user agent (eg. a browser) it also sends information in the Content-Type field of the accompanying HTTP header about what type of data format this is. This information is expressed using a MIME type label. Documents transmitted with HTTP that are of type text, such as text/html, text/plain, etc., can send a charset parameter in the HTTP header to specify the character encoding of the document.

##### Best-Practice

`text/html; charset=utf-8;`

##### Impact and Feasibility (10/10)

A correct header with the setted charset prevents different XSS attacks that use other charsets than the original webpage so they can bypass XSS prevention.

It's easy and harmless to set the correct charset without affecting the sites content.

### Content-Security-Policy (`Content-Security-Policy`)

##### Description

Content Security Policy (CSP) requires careful tuning and precise definition of the policy. If enabled, CSP has significant impact on the way browser renders pages (e.g., inline JavaScript disabled by default and must be explicitly allowed in policy). CSP prevents a wide range of attacks, including Cross-site scripting and other cross-site injections.

##### Best-Practice

Best Practice is to use the CSP with `default-src 'none'` and without any `unsafe-eval` or `unsafe-inline` directives.

##### Impact and Feasibility (7/10)

The Content-Security-Policy can prevent a wide range of attacks that infiltrate external content and code. With the correct setting it's a powerful method to increase the sites security.

On the other hand it's often not possible to set a secure CSP header without modifying the website's source code.

Impact-Rating: 10/10 | Feasibility: 5/10

### Public-Key-Pins (`Public-Key-Pins`)

##### Description

HTTP Public Key Pinning (HPKP) is a security mechanism which allows HTTPS websites to resist impersonation by attackers using mis-issued or otherwise fraudulent certificates. (For example, sometimes attackers can compromise certificate authorities, and then can mis-issue certificates for a web origin.).

##### Best-Practice

Do not use this. // `pin-sha256="<HASH>"; pin-sha256="<HASH>"; max-age=2592000; includeSubDomains`

##### Impact and Feasibility (3/10)

For small and medium-sized enterprises as is the target group of SIWECOS this header is a 'nice to have' but not a absolutely must.

If this header is misconfigured your website would not be available for the users until the correct certificates are used or `max-age` is reached.

### Strict-Transport-Security (`Strict-Transport-Security`)

##### Description

HTTP Strict Transport Security (HSTS) is a web security policy mechanism which helps to protect websites against protocol downgrade attacks and cookie hijacking. It allows web servers to declare that web browsers (or other complying user agents) should only interact with it using secure HTTPS connections, and never via the insecure HTTP protocol.

##### Best-Practice

`max-age=63072000; includeSubdomains`

##### Impact and Feasibility (10/10)

This is a must-have header for every webpage and easy and harmless to integrate.
The header guaranteed that the traffic between the server and client has to be encrypted to communicate.

### X-Content-Type-Options (`X-Content-Type-Options`)

##### Description

Setting this header will prevent the browser from interpreting files as something else than declared by the content type in the HTTP headers.

##### Best-Practice

`nosniff`

##### Impact and Feasibility (6/10)

Easy to implement and no further adjustments on the website are needed.
Only effects Internet Explorer.

### X-Frame-Options (`X-Frame-Options`)

##### Description

X-Frame-Options response header improve the protection of web applications against Clickjacking. It declares a policy communicated from a host to the client browser on whether the browser must not display the transmitted content in frames of other web pages.

##### Best-Practice

Best Practice is to set this header accordingly to your needs.

Do not use `allow-from: *`. Do not use any wildcards.

##### Impact and Feasibility (9/10)

Prevents Clickjacking attacks.

Easy to implement and no further adjustments on the website are needed.

### X-Xss-Protection (`X-Xss-Protection`)

##### Description

This header enables the Cross-site scripting (XSS) filter in the browser.

##### Best-Practice

`1; mode=block`

##### Impact and Feasibility (9/10)

Prevents reflected XSS attacks.

Easy to implement and no further adjustments on the website are needed.

# DOMXSS-Scanner

This module scans the given URL and checks for DOMXSS sinks and sources.

## API-Call

Send a POST-Request to `http://localhost/api/v1/domxss`:

```
POST /api/v1/domxss HTTP/1.1
Host: localhost:8000
Content-Type: application/json
Cache-Control: no-cache

{
  "url": "https://siwecos.de",
  "callbackurls": [
    "http://localhost:9002"
  ]
}
```

The parameters `url` and `callbackurls` are required:

- `url` must be a `string`.
- `callbackurls` must be an `array` with only contains one or more `string`s.

### Sample output

```json
{
  "name": "DOMXSS",
  "hasError": false,
  "errorMessage": null,
  "score": 50,
  "tests": [
    {
      "name": "SOURCES",
      "hasError": false,
      "errorMessage": null,
      "score": 100,
      "scoreType": "info",
      "testDetails": [
        {
          "placeholder": "NO_SOURCES_FOUND",
          "values": []
        }
      ]
    },
    {
      "name": "SINKS",
      "hasError": false,
      "errorMessage": null,
      "score": 0,
      "scoreType": "info",
      "testDetails": [
        {
          "placeholder": "SINKS_FOUND",
          "values": {
            "AMOUNT": 11
          }
        }
      ]
    }
  ]
}
```

## Scanned tasks and descriptions

### Sources (`SOURCES`)

##### Description

A source is an input that could be controlled by an external (untrusted) source.

> https://github.com/wisec/domxsswiki/wiki/Glossary

##### Impact (1/10)

The scan's result can only be used as an indication if there might be security vulnerabilities.
Further advanced tests would be needed to confirm if there are vulnerabilities on the site or not.

### Sinks (`SINKS`)

##### Description

A sink is a potentially dangerous method that could lead to a vulnerability. In this case a DOM Based XSS.

> https://github.com/wisec/domxsswiki/wiki/Glossary

##### Impact (2/10)

The scan's result can only be used as an indication if there might be security vulnerabilities.
Further advanced tests would be needed to confirm if there are vulnerabilities on the site or not.

# Scanner Interface Values

## HSHS-Scanner

| Placeholder                     | Message                                                                                                             |
| ------------------------------- | ------------------------------------------------------------------------------------------------------------------- |
| **GENERAL**                     |                                                                                                                     |
| HEADER_NOT_SET                  | The header is not set.                                                                                              |
| HEADER_SET_MULTIPLE_TIMES       | The header is set multiple times.                                                                                   |
| HEADER_ENCODING_ERROR           | The header is not correctly encoded.                                                                                |
| INCLUDE_SUBDOMAINS              | `includeSubDomains` is set.                                                                                         |
| MAX_AGE_ERROR                   | An error occured while checking `max-age`.                                                                          |
| NO_HTTP_RESPONSE                | No HTTP-Response for the given URL.                                                                                 |
| **CONTENT-SECURITY-POLICY**     |                                                                                                                     |
| CSP_CORRECT                     | The header is `unsafe-` free and includes `default-src 'none'`.                                                     |
| CSP_DEFAULT_SRC_MISSING         | The `default-src` directive is missing.                                                                             |
| CSP_LEGACY_HEADER_SET           | The legacy header `X-Content-Security-Policy` is set. The new and standardized header is `Content-Security-Policy`. |
| CSP_NO_UNSAFE_INCLUDED          | The header is free of any `unsafe-` directives.                                                                     |
| CSP_UNSAFE_INCLUDED             | The header contains `unsafe-inline` or `unsafe-eval` directives.                                                    |
| **CONTENT-TYPE**                |                                                                                                                     |
| CT_CORRECT                      | The header is set with the charset and follows the best practice.                                                   |
| CT_HEADER_WITH_CHARSET          | The header is set with the charset.                                                                                 |
| CT_HEADER_WITHOUT_CHARSET       | The header is set without the charset.                                                                              |
| CT_META_TAG_SET                 | A meta tag is set with a charset.                                                                                   |
| CT_META_TAG_SET_CORRECT         | A meta tag is set with a charset and follows the best practice.                                                     |
| CT_WRONG_CHARSET                | The given charset is wrong and thereby ineffective.                                                                 |
| **PUBLIC-KEY-PINS**             |                                                                                                                     |
| HPKP_LESS_15                    | The keys are pinned for less than 15 days.                                                                          |
| HPKP_MORE_15                    | The keys are pinned for more than 15 days.                                                                          |
| HPKP_REPORT_URI                 | A `report-uri` is set.                                                                                              |
| **REFERRER-POLICY**             |                                                                                                                     |
| NO_REFERRER                     | The directive `no-referrer` is set.                                                                                 |
| SAME_ORIGIN                     | The directive `same-origin` is set.                                                                                 |
| EMPTY_DIRECTIVE                 | The directive is explicitly set as empty.                                                                           |
| STRICT_ORIGIN                   | The direcitve 'strict-origin' is set.                                                                               |
| STRICT_ORIGIN_WHEN_CROSS_ORIGIN | The direcitve 'strict-origin-when-cross-origin' is set.                                                             |
| ORIGIN                          | The direcitve 'origin' is set.                                                                                      |
| ORIGIN_WHEN_CROSS_ORIGIN        | The direcitve 'origin-when-cross-origin' is set.                                                                    |
| NO_REFERRER_WHEN_DOWNGRADE      | The direcitve 'no-referrer-when-downgrade' is set.                                                                  |
| UNSAFE_URL                      | The direcitve 'unsafe-url' is set.                                                                                  |
| WRONG_DIRECTIVE_SET             | A wrong or unknown directive is set.                                                                                |
| **SET-COOKIE**                  |
| SECURE_FLAG_SET                 | The `secure` flag is set.                                                                                           |
| NO_SECURE_FLAG_SET              | The `secure` flag is not set.                                                                                       |
| HTTPONLY_FLAG_SET               | The `httpOnly` flag is set.                                                                                         |
| NO_HTTPONLY_FLAG_SET            | The `httpOnly` flag is not set.                                                                                     |
| **STRICT-TRANSPORT-SECURITY**   |                                                                                                                     |
| HSTS_LESS_6                     | The value for `max-age` is smaller than 6 months.                                                                   |
| HSTS_MORE_6                     | The value for `max-age` is greater than 6 months.                                                                   |
| HSTS_PRELOAD                    | `preload` is set.                                                                                                   |
| **X-CONTENT-TYPE-OPTIONS**      |                                                                                                                     |
| XCTO_CORRECT                    | The header is set correctly.                                                                                        |
| XCTO_NOT_CORRECT                | The header is not set correctly.                                                                                    |
| **X-FRAME-OPTIONS**             |                                                                                                                     |
| XFO_CORRECT                     | The header is set and does not contain any wildcard.                                                                |
| XFO_WILDCARDS                   | The header contains wildcards and is thereby useless.                                                               |
| **X-XSS-PROTECTION**            |                                                                                                                     |
| XXSS_CORRECT                    | The header is set correctly.                                                                                        |
| XXSS_BLOCK                      | `mode=block` is activated.                                                                                          |

## DOMXSS-Scanner

| Placeholder      | Message                                               |
| ---------------- | ----------------------------------------------------- |
| **GENERAL**      |                                                       |
| NO_HTTP_RESPONSE | No HTTP-Response for the given URL.                   |
| NO_CONTENT       | The site was empty and there was nothing to scan for. |
| NO_SCRIPT_TAGS   | The scanner found no `script` tags to rate.           |
| **SINKS**        |                                                       |
| NO_SINKS_FOUND   | The scanner found no sinks.                           |
| SINKS_FOUND      | The scanner found some sinks.                         |
| **SOURCES**      |                                                       |
| NO_SOURCES_FOUND | The scanner found no sources.                         |
| SOURCES_FOUND    | The scanner found some sources.                       |
