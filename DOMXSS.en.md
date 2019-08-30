
# DOMXSS

DOMXSS Scanner

## SINKS

### Headline

Checking the JavaScript code for DOMXSS sinks

### Category

JavaScript

### Description

At least one code segment was found by scanning your website that may, under certain circumstances, indicate a DOM-based [https://en.wikipedia.org/wiki/Cross-site_scripting cross-site scripting vulnerability]. This segment can be a security flaw on your website.

### Background

[https://www.siwecos.de/wiki/Cross-Site_Scripting Cross-Site-Scripting] is a method of manipulating and infiltrating the HTML code on your website. It allows an attacker to send scripts indirectly to your visitor's browser and to execute malicious code on the side of the visitor.

### Consequence

[https://en.wikipedia.org/wiki/Cross-site_scripting Cross-site scripting] allows criminals to store malicious code on your website. This code can infect your visitors or customers and thus cause severe harm, for example if the malicious code leads to the installation of a [https://en.wikipedia.org/wiki/Ransomware ransomware] in their company's network. In this case you could be held liable for the damage. IT security companies could list you on their index of dangerous websites and thus prevent access to your website for security reasons. The information that your website contains/contained malicious code can still be found by search engines, even many years after the malicious code was removed. If your website is listed on such a blacklist, you may no longer be able to receive or send emails, because your entire network and the IP would be rated as a security risk to others.

### Solution_Tips

If unsafe JavaScript code was reported, the web application may be vulnerable to so-called DOMXSS attacks.
The check result can only be taken as an indication of security flaws. Further tests are necessary to confirm that there are vulnerabilities on the website.

### Link

DOMXSS vulnerability

### Negative

Unsafe JavaScript code used (sinks).

### Positive

No unsafe code components for DOMXSS sinks were recognized in an automatic check.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## SOURCES

### Headline

Check of JavaScript code for DOMXSS sources

### Category

JavaScript

### Description

During the check, at least one vulnerability was found on the web page that could be controlled by an external, potentially untrustworthy source.

### Background

A potential vulnerability for your website is caused by loading files and code from unsafe or external sources. An attacker who controls the external source could upload malicious code which could then be executed on your web page.

### Consequence

[https://en.wikipedia.org/wiki/Cross-site_scripting Cross-site scripting] allows criminals to store malicious code on your website. This code can infect your visitors or customers and thus cause severe harm, for example if the malicious code leads to the installation of a [https://en.wikipedia.org/wiki/Ransomware ransomware] in their company's network. In this case you could be held liable for the damage. IT security companies could list you on their index of dangerous websites and thus prevent access to your website for security reasons. The information that your website contains/contained malicious code can still be found by search engines, even many years after the malicious code was removed. If your website is listed on such a blacklist, you may no longer be able to receive or send emails, because your entire network and the IP would be rated as a security risk to others.

### Solution_Tips

If unsafe JavaScript code was reported, the web application may be vulnerable to so-called DOMXSS attacks.
The check result can only be taken as an indication of security flaws. Further tests are necessary to confirm that there are vulnerabilities on the website.

### Link

Malicious-Code-By-External-Sources

### Negative

Unsafe JavaScript code used (sources)

### Positive

No unsafe code components for DOMXSS sources were recognized in an automatic check.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## _RESULTS

### NO_CONTENT

The site was empty and there was nothing to scan for.

### NO_SCRIPT_TAGS

The scanner found no script tags to rate.

### NO_SINKS_FOUND

No "sinks" were found.

### NO_SOURCES_FOUND

No "sources" were found.

### SINKS_FOUND

"Sinks" were found.

### SOURCES_FOUND

"Sources" were found.
