
# DOMXSS

DOMXSS Scanner

## SINKS

### Headline

Überprüfung des JavaScript-Codes nach DOMXSS-Sinks

### Category

JavaScript

### Description

Es wurde mindestens eine Codestelle beim Scan Ihrer Webseite gefunden, der unter bestimmten Voraussetzungen auf eine DOM-basierende [[Cross-Site Scripting|Cross-Site Scripting-Anfälligkeit]] hindeutet. Diese Stelle kann eine Schwachstelle auf Ihrer Webseite darstellen.

### Background

[[Cross-Site Scripting]] stellt eine Möglichkeit dar, den HTML-Code auf Ihrer Webseite zu manipulieren und zu infiltrieren. Es ermöglicht einem Angreifer, Skripte indirekt an den [[Browser]] Ihres Webseiten-Besuchers zu senden und damit Schadcode auf der Seite des Besuchers auszuführen.

### Consequence

[[Cross-Site Scripting]] ermöglicht es Kriminellen auf Ihrer Webseite Schadcode zu hinterlegen. Dieser Code kann Ihre Besucher oder Kunden infizieren und so möglicherweise massiven Schaden anrichten, z. B. wenn der Schadcode zur Installation eines [[Ransomware|Erpressungstrojaners]] in dessen Unternehmensnetzwerk führt. In diesem Fall könnten Sie für den Schaden haftbar gemacht werden. IT-Sicherheitsunternehmen könnten Sie in den Index von gefährlichen Webseiten aufnehmen und so Dritten den Zugriff auf Ihre Webseite aus Sicherheitsgründen verweigern. Die Information, dass Ihre Webseite Schadsoftware enthält/enthielt, ist auch viele Jahre nach dem Entfernen des Schadcodes bei Internet-Suchmaschinen ersichtlich. Eine Listung auf solch einer Blacklist kann zudem dazu führen, dass Sie auch keine [[Email|E-Mails]] mehr empfangen oder senden können, da Ihr gesamtes Netzwerk und die [[IP]] als Gefährdung anderer eingestuft wird.

### Solution_Tips

Wenn unsicherer JavaScript-Code gemeldet wird, ist die [[Webanwendung]] eventuell anfällig für sog. [[DOMXSS-Sinks|DOMXSS]]-Angriffe.
Das Ergebnis der Untersuchung kann nur als Hinweis auf Sicherheitslücken verwendet werden. Weitere Tests sind erforderlich, um die [[Schwachstellen|Schwachstellen]] auf der Webseite zu bestätigen.

### Link

DOMXSS-Schwachstelle

### Negative

Unsicheren [[JavaScript]]-Code verwendet [[DOMXSS-Sinks]].

### Positive

Automatisiert wurden keine unsicheren Codebestandteile für [[DOMXSS-Sinks]] erkannt.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## SOURCES

### Headline

Überprüfung des JavaScript-Codes nach DOMXSS-Sources

### Category

JavaScript

### Description

Bei der Überprüfung wurde mindestens eine [[Schwachstellen|Schwachstelle]] auf der Webseite gefunden, die von einer externen, möglicherweise nicht vertrauenswürdigen Quelle gesteuert werden könnte.

### Background

Durch das Laden von Dateien und Codes aus unsicheren bzw. externen Quelle entsteht für Ihre Webseite eine potentielle Sicherheitslücke. Ein Angreifer, der die externe Quelle kontrolliert, könnte einen Schadcode hochladen, der dann auf Ihrer Seite ausgeführt werden kann.

### Consequence

[[Cross-Site Scripting]] ermöglicht es Kriminellen auf Ihrer Webseite Schadcode zu hinterlegen. Dieser Code kann Ihre Besucher oder Kunden infizieren und so möglicherweise massiven Schaden anrichten, z. B. wenn der Schadcode zur Installation eines [[Ransomware|Erpressungstrojaners]] in dessen Unternehmensnetzwerk führt. In diesem Fall könnten Sie für den Schaden haftbar gemacht werden. IT-Sicherheitsunternehmen könnten Sie in den Index von gefährlichen Webseiten aufnehmen und so Dritten den Zugriff auf Ihre Webseite aus Sicherheitsgründen verweigern. Die Information, dass Ihre Webseite Schadsoftware enthält/enthielt, ist auch viele Jahre nach dem Entfernen des Schadcodes bei Internet-Suchmaschinen ersichtlich. Eine Listung auf solch einer Blacklist kann zudem dazu führen, dass Sie auch keine [[Email|E-Mails]] mehr empfangen oder senden können, da Ihr gesamtes Netzwerk und die [[IP]] als Gefährdung anderer eingestuft wird.

### Solution_Tips

Wenn unsicherer JavaScript-Code gemeldet wird, ist die [[Webanwendung]] eventuell anfällig für sog. [[DOMXSS-Sinks|DOMXSS]]-Angriffe.
Das Ergebnis der Untersuchung kann nur als Hinweis auf Sicherheitslücken verwendet werden. Weitere Tests sind erforderlich, um die [[Schwachstellen|Schwachstellen]] auf der Webseite zu bestätigen.

### Link

Schadcode-Ueber-Fremde-Quellen

### Negative

Unsicheren [[JavaScript]]-Code verwendet (Sources).

### Positive

Automatisiert wurden keine unsicheren Codebestandteile für [[DOMXSS-Sources]] erkannt.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## _RESULTS

### NO_CONTENT

Auf der Seite wurde kein Inhalt gefunden.

### NO_SCRIPT_TAGS

Der Scanner hat keine Skript-Inhalte zum Bewerten gefunden.

### NO_SINKS_FOUND

Es wurden keine „[[DOMXSS-Sinks]]“ gefunden.

### NO_SOURCES_FOUND

Es wurden keine „[[DOMXSS-Sources]]“ gefunden.

### SINKS_FOUND

Es wurden „[[DOMXSS-Sinks]]“ gefunden.

### SOURCES_FOUND

Es wurden „[[DOMXSS-Sources]]“ gefunden.
