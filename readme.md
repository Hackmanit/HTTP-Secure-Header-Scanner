# HTTP Secure Header Checker

### What?
The HTTP Secure Header Checker is an open source tool that allows you to scan the HTTP headers of your web application.
It includes a crawler to check every single site for its headers and reports the identified issues so that you can improve your site's overall security.

Furthermore the generated report includes a simple rating:

- A: The tested application is secure.
- B: The tested application is partly secure but you can do it better.
- C: The tested application is insecure.

##### Included header checks and ratings:
- Content-Security-Policy
- Content-Type
- Strict-Transport-Security
- Public-Key-Pins
- X-Content-Type-Options
- X-Frame-Options
- X-Xss-Protection


### Why?
Special HTTP headers can greatly increase your web applications security.

With the help of this tool, you are able to scan your the HTTP headers of your website for security related issues, locate them, and fix them.

There are some related tools, such as [securityheaders.io](https://securityheaders.io) or [Mozilla's Observatory](https://observatory.mozilla.org) project, but these are either closed source or hard to maintain/install.

In contrast to them, the HTTP Secure Header Scanner (HSHS) aims to be a single and **standalone tool** that you can **run with docker** on your own machine or servers.
It's **easy to use** and comes with a nice and **simple API** so you can include the checking functionality in your own projects or products.

Furthermore it includes a **crawler** that does not only check your frontpage but your whole application (optional).

Another nice feature is that you can even do **intranet checks** or use it with a **proxy server** to perform further analyzes.


### How to use?
`sudo docker run --rm -p 80:80 hackmanit/http-secure-header-scanner`

Switch to [http://localhost](http://localhost) in your favorite web browser and use it.

When you're done, just `ctrl+c` out the running terminal.


### How to contribute?
If you want to contribute to this project, just fork it and send us your PR.

As an alternative you can open or comment on the list of issues.
