
# HEADER

Header Scanner

## CONTENT_SECURITY_POLICY

### Headline

Check of the Content Security Policy (CSP)

### Category

Webserver

### Description

The [https://en.wikipedia.org/wiki/Content_Security_Policy Content Security Policy (CSP)] is a security concept that is designed to reduce the risk of injection and execution of malicious commands in a web application (content injection attacks). By means of a whitelist (list of allowed sources), it determines from which sources Javascript code, images, fonts, and other content may be integrated into your site.

### Background

[https://en.wikipedia.org/wiki/Content_Security_Policy Content Security Policy (CSP)] requires careful coordination and precise definition of the security concept. When this option is enabled, CSP has a significant impact on the way the browser renders (composes) the pages. For example, inline [[JavaScript]] is disabled by default and must be explicitly allowed in the policy. The CSP can help mitigate code injection attacks.

### Consequence

The Content Security Policy is a powerful way to increase the security on web pages. On the other hand, it is rarely possible to integrate a secure CSP [[Header/EN|header]] without modifying the source code of the web page.

### Solution_Tips

If the Content Security Policy is not configured securely, your web application may load content from insecure sources.

Use the CSP with default-src 'none' or 'self' and without unsafe-eval or unsafe-inline directives. For more information about '''Content Security Policy''', please refer to '''[https://wiki.selfhtml.org/wiki/Sicherheit/Content_Security_Policy SELFHTML>>]'''

'''Example for the [[Header/EN|header]] on the start page:'''

 <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
 <meta http-equiv="X-Content-Security-Policy" content="default-src 'self'; script-src 'self'">
 <meta http-equiv="X-WebKit-CSP" content="default-src 'self'; script-src 'self'">

'''Configuration of the web server'''

If you can configure your own web server, which is usually not possible in low-budget hosting packages, there is this option via '''changes to .htaccess''':

 # Download / Load content only from explicitly allowed sites
 # Example: Allow everything from own domain, nothing from external sources:

 Header set Content-Security-Policy "default-src 'none'; frame-src 'self'; font-src 'self';img-src 'self' siwecos.de; object-src 'self'; script-src 'self'; style-src 'self';"

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

Content-Security-Policy-Vulnerability

### Negative

Content Security Policy insecure

### Positive

A secure configuration of the [https://en.wikipedia.org/wiki/Content_Security_Policy Content Security Policy (CSP)] was found.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## CONTENT_TYPE

### Headline

Check of the HTTP content type

### Category

Webserver

### Description

The content type is a declaration that is usually placed in the [[Header/EN|header]] of a web page, the so-called HTTP [[Header/EN|header]]. This declaration defines the character set and the type of data that the page contains. If the definition is missing, the web browser will try to guess the content type; this can lead to security flaws such as Code-Page-Sniffing. This information is also important for rendering the web page correctly in every browser and on every computer. If a server sends a document to a [https://en.wikipedia.org/wiki/User_agent User Agent] (for example to the browser), it is helpful to supply some information about the file format in the content type field of the HTTP [[Header/EN|header]]. This information declares the [https://en.wikipedia.org/wiki/Media_type MIME type] and sends the character encoding of the document, such as text/html, text/plain, etc. to the browser.

### Background

The content type is a meta data declaration which is placed in the [[Header/EN|header]] of a web page. This declaration defines the character set and the type of data that the page contains. This information is important for rendering the web page correctly in every browser and on every computer. The content type can be specified in the source code by entering a relatively short piece of code. The UTF-8 character set should be used.

### Consequence

By specifying the correct [[Header/EN|header]] declaration, various [https://en.wikipedia.org/wiki/Cross-site_scripting cross-site scripting attacks] can be prevented. If the [https://en.wikipedia.org/wiki/Character_encoding character encoding] is not specified, some web browser will try to interpret the source code, thus making certain attacks possible which require a different character set.

### Solution_Tips

If the content type declaration is not configured correctly, your website is probably vulnerable to attacks.

Add the appropriate HTTP [[Header/EN|header]] or, alternatively, add a <meta> tag. Please note that, unlike a HTTP [[Header/EN|header]], the <meta> tag can be attacked more easily.

'''text/html; charset=utf-8''';

 <meta http-equiv="Content-Type" content="text/html; charset=utf-8"></pre>

Furthermore, the server must be configured to send the correct charset information. In order to make these changes on the server, particular access rights are required. For further information about the different server configuration options, please refer to [https://www.w3.org/International/articles/http-charset/index.de W3.org].

Moreover, it is also possible to pass the correct charset information to the [http://httpd.apache.org/docs/2.0/howto/htaccess.html '''.htaccess'''] file, which will overwrite the declaration in the HTTP [[Header/EN|header]]. [https://www.w3.org/International/questions/qa-htaccess-charset charset in .htaccess]

'''Enter in the .htaccess file:'''
 AddType 'text/html; charset=UTF-8' html

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

Content-Type-Not-Correct

### Negative

The HTTP content type is configured incorrectly

### Positive

The content type is configured correctly.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## PUBLIC_KEY_PINS

### Headline

Check of Public Key Pinning (HPKP) - does not influence the score

### Category

Webserver

### Description

Powerful attackers, such as intelligence agencies, can create a signature with the help of a certification agency that is accepted by users. To prevent this, a website can be configured so that the [[Certificate|certificate]] must be saved permanently (pinning) when it is called up for the first time. If [https://en.wikipedia.org/wiki/HTTP_Public_Key_Pinning Key Pinning] is used, only the saved certificate will be accepted for the period of time specified by the website.

### Background

One of the most difficult [[Header/EN|headers]] for non-experts to configure. If you have a [[Certificate|SSL certificate]], you can communicate to the requesting browser how long the certificate will still be valid, and send a "key" as a unique identification. On the next request, the browser can then check whether the certificate is still the original certificate. If an attacker tries to offer a forged certificate to the user, the web browser will not send any data and not display any information. Further information about Public Key Pinning: [https://developer.mozilla.org/en-US/docs/Web/HTTP/Public_Key_Pinning Public Key Pinning (HPKP)].

### Consequence

For small and medium sized companies, the target group of SIWECOS, this [[Header/EN|header]] is usable, but not an absolute must. If this [[Header/EN|header]] is configured wrongly, your website may not be available for users for a long period of time, namely until the correct [[Certificate|certificates]] are used, or until the previously sent [[Header/EN|header]] expires.

### Solution_Tips

The setting of [[Public-Key-Pins-Disabled/EN|Public Key Pinning]] (HPKP) is not an absolute must, and is currently not taken into account by the SIWECOS Scanner. It is advisable not to activate them, or to do so only after consultation with an expert.

The browsers Mozilla Firefox and Google Chrome comply with [https://en.wikipedia.org/wiki/HTTP_Public_Key_Pinning Public Key Pinning] and therefore ignore HPKP-[[Header/EN|headers]]. If only a single pin is set, an error message will appear. In order for pin validation to be successful, it is therefore always necessary to provide at least two public keys or a back-up pin. Interested parties should get in touch with an IT security expert or web developer.

Further information can be found at [https://www.zdnet.com/article/google-chrome-is-backing-away-from-public-key-pinning-and-heres-why/ Article from ZDNET]




'''Activate HPKP''' - This feature can be activated easily by returning a public-key-pins HTTP [[Header/EN|header]] when the website is called up via HTTPS. ([https://developer.mozilla.org/en-US/docs/Web/HTTP/Public_Key_Pinning more informations]).

 Public-Key-Pins: pin-sha256="base64=="; max-age=expireTime [; includeSubdomains][; report-uri="reportURI"]

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])
<!--pin-sha256="<HASH>"; pin-sha256="<HASH>"; max-age=2592000; includeSubDomains;-->

### Link

Public-Key-Pins-Disabled

### Negative

[https://en.wikipedia.org/wiki/HTTP_Public_Key_Pinning Public Key Pinning] is not available (HPKP is currently not under review).

### Positive

[[Public-Key-Pins-Disabled/EN|Public Key Pinning]] is active (The result does not influence the score).

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## REFERRER_POLICY

### Headline

Checking the Referrer Policy

### Category

Webserver

### Description

A well-defined Referrer Policy '''protects the privacy''' of your website visitors, but has no ''direct'' influence on the security of your website.

### Background

A well-defined Referrer Policy '''protects the privacy''' of your website visitors.

### Consequence

A missing or incorrect referrer policy enables unwanted user-identifying information outflow.

### Solution_Tips

With the entry '''Referrer Policy''' in the [[Header/EN|Header]] which referrer information that was sent in the ''Referrer Header'' should be included in requests and which not can be regulated. There are many different options that can be set. Alongside Firefox, Chrome and Opera already support several options for this [[Header/EN|header]] entry. Currently these [[Header/EN|header]] entries form a [https://www.w3.org/TR/referrer-policy/ Empfehlungskandidaten des W3C vom 26.01.2017]. The document linked above provides an exact description of the individual possibilities.

'''Note on spelling:''' The correct English spelling is '''Referrer'''. However, the original RFC ([https://tools.ietf.org/html/rfc2068#section-14.37 RFC 2068]) contained an accidental misspelling ''Referer'' and thus raises this spelling to the standard within HTTP. In other standards such as DOM, the correct spelling is used. When a Referrer is set, the web browser sets its own Header, which is then called e.g. `Referer: google.com`. IN this case, Referrer fit spelled wrongly, but is correct according to the standard.

We recommend that the Referrer Policy Header be set to be as restrictive as possible, i.e. to be set to ”no-referrer," for example.

== Examples ==
'''Referrer Policy Definition by Server Header:'''
 # Referrer Policy
 Header set referrer-Policy "no-referrer"

'''Referrer Policy Definition by HTML code:'''
 <meta name="referrer" content="no-referrer" />
'''Statement:''' The value `'''no-referrer'''` instructs the browser to send '''Never''' ''Referer Header'', which is provided by your site. This includes links to pages on your own website.

{| class="wikitable" style="margin:auto;”
|- style="border: 4px solid #C31622; color:#000000; background-color:#f6f6f6;"
|
Other useful instructions can be `'''same-origin'''`, `'''strict-origin'''` or `'''origin-when-cross-origin`'''.
|}

The value `'''same origin'''` instructs the browser to send only ''Referer Header'' provided by your website. If the target is another [[domain]], no referrer information will be sent.

The value `'''strict-origin'''` instructs the browser, to always indicate the origin domain as ''Referer Header''.

The value '''origin-when-cross-origin'''` instructs the browser to send the full referrer URL only if you stay on the same [[Domain]]. Once the domain is left via [[HTTPS]] or another [[Domain]]  is addressed, only the source domain is sent.

Detailed information and examples can be found at [https://scotthelme.co.uk/a-new-security-header-referrer-policy/Scott Helme].

### Link

Referrer-Policy

### Negative

Referrer Policy is insecure

### Positive

Referrer Policy is secure

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## SET_COOKIE

### Headline

Check of Set-Cookie

### Category

Webserver

### Description

Cookies should be secured by setting the HttpOnly and Secure flags to ensure they cannot be read or altered by others.

### Background

Checks whether or not cookies are secured.

### Consequence

Unsecured cookies can be altered or read through a [https://en.wikipedia.org/wiki/Man-in-the-middle_attack man-in-the-middle-attack].

### Solution_Tips

`httpOnly`-flag: set this so that cookies cannot be accessed by Javascript. You protect session information from being stolen and misused. Whoever owns a session cookie is authenticated.
`secure`-Flag: set this to ensure that cookies are only transmitted across encrypted (https) channels.

### Link

Set-Cookie

### Negative

Cookies are not secured.

### Positive

Cookies are secured.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## STRICT_TRANSPORT_SECURITY

### Headline

Check of HSTS protection

### Category

Webserver

### Description

[https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security HTTP Strict Transport Security (HSTS)] ensures that the website can only be accessed via a secure HTTPS connection for a specified time period. The website operator can define the length of the time period, and whether this rule should also apply to subdomains.

### Background

[https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security HTTP Strict Transport Security (HSTS)] protection is inactive, the communication between your website and its visitors can be intercepted and manipulated.

### Consequence

Currently, your website is not protected against using an outdated [https://en.wikipedia.org/wiki/Transport_Layer_Security SSL/TLS] standard (protocol downgrade attacks) and against cookie hijacking. This allows an attacker to intercept and manipulate your user's communication. Using this information, an attacker could launch further attacks or spam your users with unwanted advertisements and malicious code. [https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security HTTP Strict Transport Security (HSTS)] is an excellent feature to strengthen your site and its implementation of TLS by forcing the user agent to use HTTPS.

### Solution_Tips

If the connection to your page is not encrypted, all communication between your site and its users can be intercepted and manipulated.

max-age=63072000; includeSubdomains;
HTTP Strict Transport Security (HSTS) is a web security policy mechanism that is easy to integrate.

 # Activate HTTP Strict Transport Security (HSTS)
 # Required: "max-age"
 # Optional: "includeSubDomains"</pre>
 '''Header set Strict-Transport-Security "max-age=31556926; includeSubDomains"'''

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

No-Encryption-Found

### Negative

HSTS protection error

### Positive

Your website can only be reached via the secure HTTPS protocol. Communication between your website and its visitors can not be intercepted or manipulated.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_CONTENT_TYPE_OPTIONS

### Headline

Check of the X-Content-Type header

### Category

Webserver

### Description

The X-Content-Type-Options settings in the [[Header/EN|header]] prevent that the browser interprets data as anything other than declared by the content type in the HTTP [[Header/EN|header]]. The [[Header/EN|header]] settings are not set here.

### Background

There is only one definable value "nosniff", which prevents the Internet Explorer and Google Chrome from searching for other possible MIME types, other than the declared Content-Type (for example text/html). For Chrome this also applies to downloading extensions. The [[Header/EN|header]] entry reduces the load from so-called [https://en.wikipedia.org/wiki/Drive-by_download drive-by download attacks]. Websites with support for uploading files which, if the names are chosen skillfully, will be treated as executable files or as dynamic [[HTML|HTML-Datei]] by the [[Browser]], could infect your computer or other computers with malicious code. For further information on '''X-Content-Type-Options''', please refer to the report by [https://www.golem.de/news/cross-site-scripting-javascript-code-in-bilder-einbetten-1411-110264-2.html Golem.de (German only)].

### Consequence

Implementation is easy and does not require additional adjustments. Prevents attacks on users of Internet Explorer.

### Solution_Tips

nosniff;

'''Code example of an .htaccess file on an Apache webserver.'''

 <IfModule mod_headers.c>
   # prevent mime based attacks like drive-by download attacks, IE and Chrome
   '''Header set X-Content-Type-Options "nosniff"'''
 </IfModule>

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

X-Content-Type-Options-Vulnerability

### Negative

X-Content-Type [[Header/EN|header]] is missing.

### Positive

The HTTP [[Header/EN|header]] is set correctly.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_FRAME_OPTIONS

### Headline

Checking the HTTP header X-frame options

### Category

Webserver

### Description

X-Frame-Options helps to prevent attacks carried out by rendering content within a frame. This largely mitigates the risk of [https://en.wikipedia.org/wiki/Clickjacking clickjacking attacks]. Downgrading attacks, as known in the Internet Explorer, are also minimized.

### Background

This [[Header/EN|header]] entry determines whether a browser is allowed to render a page in a ''frame'' or ''iframe''. This can prevent so-called clickjacking attacks by making sure that the website is not embedded in another website. The following options are available:
<poem>
'''DENY:''' The page is not rendered if it is being loaded in a ''frame'' or ''iframe''.
'''SAMEORIGIN:''' The page is only rendered if the ''frame'' or ''iframe'' is located in the same domain.
'''ALLOW-FROM DOMAIN:''' The page is not rendered if the domain is different from the domain specified here.
</poem>

### Consequence

Prevents for example [https://en.wikipedia.org/wiki/Clickjacking clickjacking attacks]. Easy to implement, and requires no further adjustments on the website.

### Solution_Tips

If is was reported, that the HTTP [[Header/EN|header]] X-Frame-Options is not set, your website is not sufficiently protected from [https://en.wikipedia.org/wiki/Clickjacking clickjacking attacks].

Set in the HTTP [[Header/EN|header]] X-Frame-Options according to your requirements. The '''X-Frame-Options''' field in the HTTP header can be used to determine whether a browser is allowed to render or embed the target page in a <frame>, <iframe> or <object>. Websites can use this header to deflect clickjacking attacks by preventing their content from being embedded in third party pages.

With the HTTP-Header command X-Frame-Options, modern web browsers can be instructed to prevent loading a page in a frame on another website. To do this, the following setting must be entered in the .htaccess file:

Header always append X-Frame-Options DENY

 Header always append X-Frame-Options DENY

Alternatively, you can permit the page to be embedded only in other pages within the same domain:

 Header always append X-Frame-Options SAMEORIGIN

If a website must be embedded in an external page, a domain can be specified:

 Header always append X-Frame-Options ALLOW-FROM botfrei.de

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

X-Frame-Options-Vulnerability

### Negative

HTTP [[Header/EN|header]] X-Frame-Options not set.

### Positive

The [[Header/EN|Header]] is set correctly and improves protection against framing attacks such as UI redressing and clickjacking.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_XSS_PROTECTION

### Headline

Check of the X-Content-Type header

### Category

Webserver

### Description

The HTTP [[Header/EN|header]] X-XSS-Protection defines how built-in XSS filters in the browser are configured. A default installation can indicate an incorrect configuration.

### Background

This [[Header/DE|Header]]] activates the one that is used in most current browsers (Internet Explorer, Chrome and Safari) built-in Cross-Site Scripting Protection (XSS). Protection is enabled by default, so this header is only for reactivating the filter if the user has disabled it. This header is only supported for IE 8+, Opera, Chrome and Safari.

### Consequence

Prevents reflected [https://en.wikipedia.org/wiki/Cross-site_scripting XSS attacks]. Easy to implement, and requires no further adjustments on the website.

### Solution_Tips

If it was reported, that your website is probably not sufficiently protected from [https://en.wikipedia.org/wiki/Cross-site_scripting XSS attacks]:

1; mode=block

'''Code example of an .htaccess file on an Apache webserver.'''

   # Turn on XSS prevention tools, activated by default in IE and Chrome
   '''Header set X-XSS-Protection "1; mode=block"'''

Here is an example of an .htaccess file which will set the '''Header Scanner''' to green.
([[Htaccess/EN|.htaccess example]])

### Link

XSS-Vulnerability

### Negative

[https://en.wikipedia.org/wiki/Cross-site_scripting Cross-site scripting] protection is not active or configured incorrectly.

### Positive

[https://en.wikipedia.org/wiki/Cross-site_scripting Cross-site scripting] (XSS) protection of the web browser is active on your website.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## _RESULTS

### CSP_CORRECT

The [[Header/EN|header]] is set correctly and corresponds to the recommendations.

### CSP_DEFAULT_SRC_MISSING

The default-src directive is missing.

### CSP_LEGACY_HEADER_SET

The outdated [[Header/EN|header]] '':HEADER_NAME'' is used. The new standardized [[Header/EN|header]] is ''Content-Security-Policy''.

### CSP_NO_UNSAFE_INCLUDED

The [https://en.wikipedia.org/wiki/Content_Security_Policy Content Security Policy (CSP)] does not contain any unsafe directives, but it may not be configured securely.

### CSP_UNSAFE_INCLUDED

The [[Header/EN|header]] is set insecurely because it contains 'unsafe-inline' or 'unsafe-eval' directives.

### CT_CORRECT

The [[Header/EN|header]] is set correctly and corresponds to the recommendations.

### CT_HEADER_WITHOUT_CHARSET

The [[Header/EN|header]] is used without a character set and thus not safe.

### CT_HEADER_WITH_CHARSET

The [[Header/EN|header]] is set correctly and contains a character set specification.

### CT_META_TAG_SET

The [[Header/EN|header]] is set correctly, but it does not contain a character set specification or does not correspond to the recommendations. ":META" was found.

### CT_META_TAG_SET_CORRECT

The ":META" specification in the HTML [[Header/EN|header]] is set correctly.

### CT_WRONG_CHARSET

A false or invalid character set was used. The configuration is not safe.

### DIRECTIVE_SET

The directive ':DIRECTIVE' is set.

### EMPTY_DIRECTIVE

The directive is explicity set as empty.

### HEADER_ENCODING_ERROR

The [[Header/EN|header]] ''':HEADER_NAME''' contains characters which cannot be processed.

### HEADER_NOT_SET

The [[Header/EN|header]] is not set.

### HEADER_SET_MULTIPLE_TIMES

The [[Header/EN|header]] was set several times.

### HPKP_LESS_15

The public keys are pinned for less than 15 days.

### HPKP_MORE_15

The public keys are pinned for more than 15 days.

### HPKP_REPORT_URI

A 'report-uri' is set.

### HSTS_LESS_6

The value of 'max-age' is less than 6 months.

### HSTS_MORE_6

The value of 'max-age' is greater than 6 months.

### HSTS_PRELOAD

The 'preload' directive is set.

### HTTPONLY_FLAG_SET

The HttpOnly flag is set.

### INCLUDE_SUBDOMAINS

'includeSubDomains' is set.

### INVALID_HEADER

The following elements of your [[Header/EN|Header]]] are invalid:
:HEADER

### MAX_AGE_ERROR

There was an error while checking the 'max-age' directive.

### NO_HTTPONLY_FLAG_SET

The HttpOnly flag is not set.

### NO_HTTP_RESPONSE

The specified URL did not respond.

### NO_SECURE_FLAG_SET

The secure flag is not set.

### SECURE_FLAG_SET

The secure flag is set.

### WRONG_DIRECTIVE_SET

A wrong or unknown directive is set.

### XCTO_CORRECT

The [[Header/EN|header]] is set correctly and corresponds to the recommendations.

### XCTO_NOT_CORRECT

The [[Header/EN|header]] is not set correctly.

### XFO_CORRECT

The [[Header/EN|header]] is set correctly and corresponds to the recommendations.

### XFO_WILDCARDS

The [[Header/EN|header]] contains wildcard information (*) and is therefore not configured securely.

### XXSS_BLOCK

The 'mode=block' directive is active.

### XXSS_CORRECT

The [[Header/EN|header]] is set correctly and corresponds to the recommendations.
