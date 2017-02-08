# HTTP Secure Header Checker

This project is still under heavy development. Do not use it for production.

### What?
The HTTP Secure Header Checker is an open source tool that allows you to scan your web application's HTTP header.
The included crawler will check every single site for it's headers and reports any issues so you can improve your site's security.

Furthermore the generated report will include a simple rating:

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
Special HTTP headers can heavily increase your web applications security.

With this tool you're able to scan your site's headers for security related issues, locate and fix them.

Some already existing tools like [securityheaders.io](https://securityheaders.io) or [Mozilla's Observatory](https://observatory.mozilla.org) project
are either closed source or hard to maintain / install.
The observatory for example includes many further tests and you're able to use other third party tools to check your application.


In contrast to the others this project aims to be a single and **standalone tool** that you can **run with docker** on your own machine or servers.
It's **easy to use** and comes with a nice and **simple API** so you can include the checking functionality in your own projects or products.

Furthermore it includes a **crawler** that does not only checks your frontpage but your whole application if you want to.

Another nice feature is that you can even do **intranet checks** or use it with a **proxy server** to perform further analyzes.


### How to use?
Clone this repo in recursive mode, cd in to the `laradock` folder and run:
 
`docker-compose up -d workspace nginx redis`.

Switch to [http://localhost](http://localhost) in your favorite web browser and use it.


### How to contribute?
If you want to contribute to this project, just fork it and send us your PR.

As an alternative you can open or comment on the list of issues.