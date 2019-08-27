
# HEADER

Header Scanner

## CONTENT_SECURITY_POLICY

### Headline

Überprüfung der Content Security Policy (CSP)

### Category

Webserver

### Description

Die [[Content-Security-Policy]] ist eine strukturierte Vorgehensweise, welche das Injizieren und Ausführen von evtl. bösartigen Befehlen in einer [[Webanwendung]] ([[Injection|Injection-Angriffe]]) mildern soll. Es stellt über eine [[Whitelist]] dar, von welchen Quellen z. B. [[Javascript]], Bilder, Schriftarten und andere Inhalte auf Ihrer Seite eingebunden werden dürfen.

### Background

Content Security Policy (CSP) erfordert eine sorgfältige Abstimmung und genaue Definition des Sicherheitskonzeptes. Wenn diese Option aktiviert wurde, hat CSP erhebliche Auswirkungen auf die Art und Weise, wie der Browser die Seiten rendert (zusammensetzt). Zum Beispiel Inline [[JavaScript]] ist standardmäßig deaktiviert und muss explizit in der Policy erlaubt werden. Die CSP kann dazu beitragen, Code-Injection-Angriffe zu mildern.

### Consequence

Die Content-Security-Policy ist eine leistungsfähige Möglichkeit, die Sicherheit auf Webseiten zu erhöhen. Auf der anderen Seite ist es nur selten möglich, einen sicheren CSP-[[Header/DE|Header]] zu integrieren, ohne den Quellcode der Webseite zu modifizieren.

### Solution_Tips

Wenn die Content Security Policy nicht sicher konfiguriert ist, lädt Ihre [[Webanwendung]] eventuell Inhalte aus unsicheren Quellen nach. 

Verwenden Sie den CSP mit default-src 'none' oder 'self' und ohne 'unsafe-eval' oder 'unsafe-inline' Richtlinien. Mehr zu '''Content Security Policy''' (zu deutsch etwa "Richtlinie für die Sicherheit der Inhalte") finden Sie bei '''[https://wiki.selfhtml.org/wiki/Sicherheit/Content_Security_Policy SELFHTML >>]'''

'''Beispiele für den [[Header/DE|Header]] der Startseite:'''

 <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
 <meta http-equiv="X-Content-Security-Policy" content="default-src 'self'; script-src 'self'">
 <meta http-equiv="X-WebKit-CSP" content="default-src 'self'; script-src 'self'">

'''Konfiguration des Webservers'''

Wenn man seinen eigenen Webserver konfigurieren kann, was bei günstigen Hostingangeboten in aller Regel nicht der Fall ist, dann gibt es diese Einstellungsmöglichkeit über die '''Bearbeitung der .htaccess''':

 # Download: Lade Inhalte nur von Seiten, die explizit erlaubt sind
 # Beispiel: Alles von der eigenen Domain erlauben, keine Externas:

 Header set Content-Security-Policy "default-src 'none'; frame-src 'self'; font-src 'self';img-src 'self' siwecos.de; object-src   'self'; script-src 'self'; style-src 'self';"

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

Content-Security-Policy-Schwachstelle

### Negative

Content Security Policy unsicher

### Positive

Eine sichere Konfiguration der Content Security Policy ([[Content-Security-Policy-Schwachstelle/DE/Background|CSP]]) wurde gefunden.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## CONTENT_TYPE

### Headline

Überprüfung des HTTP Content-Types

### Category

Webserver

### Description

Der Content-Type ist eine Angabe, die für gewöhnlich im Kopfbereich der Webseite, dem sogenannten [[Header/DE|Header]], untergebracht wird. Durch diese Angaben wird der Zeichensatz und der Typ des Inhalts der Seite definiert. Sollte eine Definition fehlen, wird der [[Browser]] versuchen, den Content-Type zu erraten; dies kann zu [[Schwachstellen|Sicherheitslücken]] wie [[Sniffer|Code-Page-Sniffing]] führen. Diese Angaben sind zudem wichtig, damit die Webseite in jedem [[Browser]] und auf jedem Computer einwandfrei dargestellt wird. Wenn ein Server ein Dokument an einen [https://de.wikipedia.org/wiki/User_Agent User-Agent] sendet (zum Beispiel zum [[Browser]]) ist es nützlich, im Content-Type-Feld des HTTP-Headers die Art des Dateiformates zu hinterlegen. Diese Informationen deklarieren den [https://de.wikipedia.org/wiki/Internet_Media_Type MIME-Typ] und senden entsprechend die Zeichenkodierung des Dokuments wie text/html, text/plain, etc. an den Browser.

### Background

Der Content-Type ist eine [https://de.wikipedia.org/wiki/Meta-Element Meta-Element-Angabe], die im Kopfbereich der Website, dem sogenannten [[Header/DE|Header]] untergebracht wird. Durch diese Angabe wird der Zeichensatz und der Typ des Inhalts der Seite definiert. Diese Angaben sind wichtig, damit die Website in jedem Browser und auf jedem Computer einwandfrei dargestellt wird. Die Einbettung des Content-Types im Quellcode ist durch eine relativ kurze Angabe möglich. Es sollte der [[UTF-8]] Zeichensatz verwendet werden.

### Consequence

Durch die Angabe der korrekten [[Header/DE|Header]]-Deklaration können diverse [[Cross-Site Scripting|XSS-Angriffe]] verhindert werden. Wird der verwendete [https://de.wikipedia.org/wiki/Zeichenkodierung Zeichensatz] nicht angegeben, so interpretieren manche [[Browser|Webbrowser]] den Quellcode selbst, wodurch bestimmte Angriffe möglich werden, die einen anderen Zeichensatz voraussetzen.

### Solution_Tips

Wenn die [[Content-Type-Nicht-Korrekt/DE|Content-Type-Angabe]] nicht korrekt konfiguriert ist, sind Angriffe auf Ihre Webseite wahrscheinlich möglich.

Fügen Sie den entsprechenden HTTP-[[Header/DE|Header]] oder alternativ ein <meta> Tag hinzu. Bitte beachten Sie, dass <meta> im Gegensatz zu einem HTTP-[[Header/DE|Header]] leichter umgangen werden kann.

'''text/html; charset=utf-8''';

 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

Weiterhin muss der Server selber konfiguriert werden, damit die '''richtige charset-Information''' gesendet wird. Dazu werden entsprechende Berechtigungen benötigt, um die Änderungen am Server durchführen zu können. Weitere Informationen zu den verschiedenen Serverkonfigurationen finden Sie auf den Seiten von [https://www.w3.org/International/articles/http-charset/index.de W3.org].

Darüber hinaus gibt es auch die Möglichkeit die '''richtige charset-Information''' der [http://httpd.apache.org/docs/2.0/howto/htaccess.html '''.htaccess'''] zu übergeben, welche die Angaben im HTTP-[[Header/DE|Header]] überschreiben. [https://www.w3.org/International/questions/qa-htaccess-charset charset in .htaccess]

'''In die .htaccess eintragen:'''
 AddType 'text/html; charset=UTF-8' html

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

Content-Type-Nicht-Korrekt

### Negative

Inkorrekte HTTP Content-Type Konfiguration

### Positive

Die [[Content-Type-Nicht-Korrekt/DE/Background|Content Type Angabe]] ist korrekt konfiguriert.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## PUBLIC_KEY_PINS

### Headline

Überprüfung des Public Key Pinning (HPKP) - hat keinen Einfluß auf die Bewertung

### Category

Webserver

### Description

Mächtige Angreifer wie bspw. Geheimdienste können ggf. eine [[Digitale_Signatur|Signatur]] mit der Hilfe einer von den Benutzern akzeptierten [[Zertifizierungsstelle]] erstellen lassen. Um dies zu verhindern, kann eine Webseite definieren, dass beim ersten Aufruf des [[Zertifikate|Zertifikats]] das Zertifikat dauerhaft gespeichert wird (pinning). Mit der Hilfe von [[HTTP_Public_Key_Pinning|Key-Pinning]] wird für die von der Webseite definierten Zeit lediglich das gespeicherte [[Zertifikate|Zertifikat]] akzeptiert.

### Background

Einer der für Laien am schwierigsten zu konfigurierende [[Header/DE|Header]]. Besitzt man ein [[Zertifikate|SSL-Zertifikat]] kann man dem anfragenden [[Browser]] mitteilen, wie lange dieses noch gilt und einen “Schlüssel” = eine eindeutige Kennung senden. Damit kann beim erneuten Aufruf festgestellt werden, ob das [[Zertifikate|Zertifikat]] noch das zuvor angegebene [[Zertifikate|Zertifikat]] ist. Sollte ein Angreifer nun versuchen, ein gefälschtes [[Zertifikate|Zertifikat]] dem Nutzer zu unterbreiten, so wird der [[Browser|Webbrowser]] keine Daten senden und keine Informationen darstellen. Weitere Infos zu Public Key Pinning [[HTTP_Public_Key_Pinning|Public Key Pinning (HPKP)]].

### Consequence

Für kleine und mittelständische Unternehmen, die Zielgruppe von SIWECOS, ist dieser [[Header/DE|Header]] zwar einsetzbar, aber kein absolutes Muss. Wird dieser Header falsch konfiguriert, steht Ihre Website für die Benutzer unter Umständen für einen langen Zeitraum nicht zur Verfügung, und zwar solange bis die korrekten [[Zertifikate|Zertifikate]] verwendet werden oder das Ablaufdatum des zuvor gesendeten Headers erreicht ist.

### Solution_Tips

Das Setzen des [[Public-Key-Pins-Deaktiviert/DE|Public Key Pinning]] (HPKP) ist kein absolutes Muss und wird aktuell im Siwecos-Scanner nicht gewertet. Es ist ratsam, diese nicht oder nur nach Absprache mit einem Experten zu aktivieren.

Die [[Browser]] Mozilla, Firefox und Google Chrome richten sich nach dem [https://de.wikipedia.org/wiki/HTTP_Public_Key_Pinning RFC-7469-Standard] und ignorieren daher HPKP-[[Header/DE|Header]]. Wenn nur ein einziger Pin gesetzt ist, wird eine Fehlermeldung angezeigt. Damit die Pin-Validierung funktioniert, ist es also immer notwendig mindestens zwei gültige Public Keys bzw. einen Backup-Pin anzugeben. Interessierte sollten sich dazu an einen IT-Sicherheitsexperten oder Webentwickler wenden.

Weiterführende Informationen finden Sie im [https://www.zdnet.com/article/google-chrome-is-backing-away-from-public-key-pinning-and-heres-why/ Artikel von ZDNET]

'''HPKP aktivieren''' - Dieses Feature kann einfach aktiviert werden, indem ein Public-Key-Pins HTTP-[[Header/DE|Header]] beim Aufruf der Seite über [[HTTPS]] zurückgegeben wird. ([https://developer.mozilla.org/de/docs/Web/Security/Public_Key_Pinning Weitere Infos]).

 Public-Key-Pins: pin-sha256="base64=="; max-age=expireTime [; includeSubdomains][; report-uri="reportURI"]

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])
<!--pin-sha256="<HASH>"; pin-sha256="<HASH>"; max-age=2592000; includeSubDomains;-->

### Link

Public-Key-Pins-Deaktiviert

### Negative

[[Public-Key-Pins-Deaktiviert/DE/Background|Public-Key-Pinning]] nicht vorhanden (Das Ergebnis fließt nicht in die Bewertung ein).

### Positive

[[Public-Key-Pins-Deaktiviert/DE|Public-Key-Pinning]] ist aktiviert (HPKP wird derzeit nicht überprüft).

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## REFERRER_POLICY

### Headline

Überprüfung der Referrer Policy

### Category

Webserver

### Description

Eine gut gesetzte Referrer Policy '''schützt die Privatsphäre''' Ihrer Webseiten-Besucher, hat aber keinen ''direkten'' Einfluss auf die Sicherheit Ihrer Webseite.

### Background

Eine gut gesetzte Referrer Policy '''schützt die Privatsphäre''' Ihrer Webseiten-Besucher.

### Consequence

Eine fehlende oder falsch gesetzte Referrer Policy ermöglicht unerwünschten benutzeridentifizierenden Informationensabfluss.

### Solution_Tips

Mit dem Eintrag '''Referrer Policy''' im [[Header/DE|Header]] wird geregelt, welche der Referrer-Informationen, die im ''Referer Header'' gesendet wurden, in Anfragen aufgenommen werden sollen und welche nicht. Es gibt sehr viele verschiedene Optionen, die gesetzt werden können. Neben Firefox unterstützen Chrome und Opera bereits einige Optionen dieses Header-Eintrages. Aktuell handelt es sich bei diesen Header-Einträgen um einen [https://www.w3.org/TR/referrer-policy/ Empfehlungskandidaten des W3C vom 26.01.2017]. In dem zuvor verlinkten Dokument werden die einzelnen Möglichkeiten genau beschrieben. 

'''Anmerkung zur Schreibweise:''' Die korrekte englische Schreibweise lautet '''Referrer'''. Der ursprüngliche RFC ([https://tools.ietf.org/html/rfc2068#section-14.37 RFC 2068]) enthielt jedoch versehentlich die falsche Schreibweise ''Referer'' und erhebt diesen Wortlaut damit zum Standard innerhalb von HTTP. In anderen Standards wie im DOM wird die korrekte Schreibweise verwendet. Der Webbrowser setzt, wenn ein Referrer gesetzt ist, einen eigenen Header ein, der heißt dann z. B. `Referer: google.com`. Dort ist dann Referrer falsch geschrieben, aber laut Standard richtig.

Wir empfehlen die Einstellung des Referrer Policy Headers so restriktiv wie möglich zu gestalten, also z. B. "no-referrer" zu setzen.

== Beispiele ==

'''Referrer Policy Definition durch Server Header:'''
 # Referrer Policy
 Header set Referrer-Policy "no-referrer"

'''Referrer Policy Definition durch HTML-Code:'''
 <meta name="referrer" content="no-referrer" />

'''Anweisung:''' Der Wert `'''no-referrer'''` weist den Browser an, '''niemals''' ''Referer Header'' zu senden, die von Ihrer Site gestellt werden. Dazu gehören auch Links zu Seiten Ihrer eigenen Webseite.

{| class="wikitable" style="margin:auto;"
|- style="border: 4px solid #C31622; color:#000000; background-color:#f6f6f6;"
| 
Weitere nützliche Anweisungen können `'''same-origin'''`, `'''strict-origin'''` oder `'''origin-when-cross-origin'''` sein.
|}

Der Wert `'''same-origin'''` weist den Browser an, nur ''Referer Header'' zu senden, die von Ihrer Webseite gestellt werden. Wenn das Ziel eine andere [[Domain]] ist, werden keine Referrer-Informationen gesendet.

Der Wert `'''strict-origin'''` weist den Browser an, als ''Referer Header'' immer die Ursprungs-Domain anzugeben.

Der Wert `'''origin-when-cross-origin'''` weist den Browser an, nur dann die vollständige Referrer-URL zu senden, wenn Sie auf der selben [[Domain]] bleiben. Sobald die Domain über [[HTTPS]] verlassen wird oder eine anderer [[Domain]] angesprochen wird, wird nur die Quell-Domain gesendet.

Detaillierte Informationen und Beispiele (English) finden Sie bei [https://scotthelme.co.uk/a-new-security-header-referrer-policy/ Scott Helme].

### Link

Referrer-Policy

### Negative

Referrer Policy unsicher

### Positive

Referrer Policy sicher

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## SET_COOKIE

### Headline

Überprüfung von Set-Cookie

### Category

Webserver

### Description

Cookies sollten durch das Setzen des HttpOnly und Secure flags gesichert werden um zu verhindern, dass Dritte die Informationen abgreifen oder verändern können.

### Background

Überprüft ob Flags zur Cookie Sicherheit gesetzt sind.

### Consequence

Wenn Cookies nicht abgesichert werden, können sie über einen [[Man-in-the-middle]]-Angriff verändert oder abgegriffen werden.

### Solution_Tips

`httpOnly`-Flag setzen, damit das Cookie nicht über [[Javascript|JavaScript]] ausgelesen werden kann. Damit schützen Sie die Session-Informationen vor Auslesen und Diebstahl, denn wer das Cookie hat gilt als [[Authentifizierung|authentifiziert]]. 
`secure`-Flag setzen, damit das Cookie nicht über unverschlüsselte Verbindungen [[HTTP]] gesendet wird, sondern ausschließlich über [[HTTPS]].

### Link

Set-Cookie

### Negative

Cookies sind nicht gesichert.

### Positive

Cookies sind gesichert.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## STRICT_TRANSPORT_SECURITY

### Headline

Überprüfung des HSTS Schutzes

### Category

Webserver

### Description

Strict-Transport-Security ([[HTTP_Strict_Transport_Security|HSTS]]) stellt sicher, dass die Webseite für eine bestimmte Zeit lediglich über [[HTTPS]] gesicherte Verbindung aufgerufen werden kann. Der Webseitenbetreiber kann diesbezüglich u. a. definieren, wie lange der Zeitinterval ist und ob diese Regelung auch für [[Domain|Subdomains]] gelten soll.

### Background

Der [[HTTP_Strict_Transport_Security|HSTS]] Schutz ist inaktiv, die Kommunikation zwischen Ihrer Webseite und den Besuchern kann abgehört und manipuliert werden.

### Consequence

Aktuell ist Ihre Website nicht gegen Nutzung eines älteren [[SSL|TLS-Standards]] (Protokoll-Downgrade-Angriffe) und Cookie-Hijacking geschützt. Dies ermöglicht Angreifern die Kommunikation Ihrer Benutzer abzuhören und diese zu manipulieren. Mit Hilfe dieser Informationen könnte ein Angreifer weitere Attacken starten oder Ihren Nutzern ungewünschte Werbung und Schadcode zusenden. Die [[HTTP_Strict_Transport_Security|HTTP-Strict-Transport-Sicherheit]] ist eine hervorragende Funktion zur Unterstützung Ihrer Seite und stärkt Ihre Implementierung von [[SSL|TLS]], indem der Benutzeragent die Verwendung von [[HTTPS]] erzwingt.

### Solution_Tips

Wenn die Verbindung zu Ihrer Seite ist nicht verschlüsselt ist, kann sämtliche Kommunikation zwischen Ihrer Seite und den Benutzern abgehört und manipuliert werden.

max-age=63072000; includeSubdomains;
HTTP Strict Transport Security (HSTS) ist ein einfach zu integrierender Web-Security-Policy-Mechanismus.

 # HTTP Strict Transport Security (HSTS) aktivieren
 # Pflichtangabe: "max-age"
 # Optional: "includeSubDomains"</pre>
 '''Header set Strict-Transport-Security "max-age=31556926; includeSubDomains"'''

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

Keine-Verschluesselung-Gefunden

### Negative

HSTS Schutz Fehler

### Positive

Ihre Webseite ist ausschließlich über das sichere [[HTTPS|HTTPS-Protokoll]] erreichbar. Kommunikation zwischen Ihrer Webseite und den Besuchern kann nicht abgehört und manipuliert werden.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_CONTENT_TYPE_OPTIONS

### Headline

Überprüfung des X-Content-Type Headers

### Category

Webserver

### Description

Die [[X-Content-Type-Options-Schwachstelle/DE/Background|X-Content-Type-Options]] Einstellungen im [[Header/DE|Header]] verhindern, dass der [[Browser]] Dateien als etwas anderes interpretiert, als vom Inhaltstyp im [[HTTP|HTTP]]-[[Header/DE|Header]] deklariert wurde. Die Headereinstellungen sind hier nicht gesetzt.

### Background

Es existiert nur ein definierbarer Wert '''nosniff''', dieser verhindert, dass der Internet Explorer und Google Chrome unabhängig vom deklarierten Content-Type (z. B. text/html) nach weiteren möglichen MIME-Types suchen. Für Chrome gilt dies auch für das Herunterladen von Erweiterungen. Der [[Header/DE|Headereintrag]] reduziert die Belastung durch sog. [[Drive-by-Download|Drive-by-Download-Attacken]]. Webseiten, die den Upload von Dateien unterstützen und die, wenn deren Namen geschickt gewählt wurden, vom [[Browser]] als ausführbare Datei oder dynamische [[HTML|HTML-Datei]] behandelt werden, könnten damit Ihren Rechner oder andere mit Schadcode infizieren. Weitere Informationen zu '''X-Content-Type-Options''' finden Sie im Bericht von [https://www.golem.de/news/cross-site-scripting-javascript-code-in-bilder-einbetten-1411-110264-2.html Golem.de].

### Consequence

Einfach und ohne weitere Anpassungen zu implementieren. Verhindert Angriffe auf Nutzer des Internet Explorers.

### Solution_Tips

nosniff; 

'''Beispielcode einer .htaccess auf einem Apache Webserver'''

 <IfModule mod_headers.c>
   # prevent mime based attacks like drive-by download attacks, IE and Chrome
   '''Header set X-Content-Type-Options "nosniff"'''
 </IfModule>

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

X-Content-Type-Options-Schwachstelle

### Negative

X-Content-Type [[Header/DE|Header]] fehlt.

### Positive

Der [[Header/DE|HTTP-Header]] ist korrekt gesetzt.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_FRAME_OPTIONS

### Headline

Überprüfung der HTTP-Header X-Frame Optionen

### Category

Webserver

### Description

Das Setzen von '''X-Frame-Options''' hilft dabei, Angriffe über [[Framing-Mechanismen|Framing-Mechanismen]] zu unterbinden. Dies gewährleistet bspw., dass [[Clickjacking]]-Angriffe größtenteils gemildert werden können. Darüber hinaus werden [[Downgrading_Angriffe|Downgrading-Angriffe]] wie etwa im Internet Explorer minimiert.

### Background

Ob einem [[Browser]] erlaubt wird, eine Seite in einem ''frame'' oder ''iframe'' darzustellen, legt dieser [[Header/DE|Headereintrag]] fest. Damit können sog. [[Clickjacking|Clickjacking-Attacken]] vermieden werde, indem sichergestellt wird, dass die Webseite nicht in einer anderen Webseite eingebettet wird. Es gibt verschiedene Werte:
<poem>
'''DENY:''' Kein Rendering der Seite, wenn sie in einem ''frame'' oder ''iframe'' geladen wird.
'''SAMEORIGIN:''' Rendering der Seite erfolgt nur, wenn der ''frame'' oder ''iframe'' innerhalb Ihrer Domain ist.
'''ALLOW-FROM DOMAIN:''' Wird hierbei explizit eine Domain angegeben, werden keine anderen Inhalte von unbekannten Sourcen gerendert bzw. dargestellt.
</poem>

### Consequence

Verhindert z. B. [[Clickjacking]]-Angriffe. Einfach zu implementieren und keine weiteren Anpassungen auf der Website erforderlich.

### Solution_Tips

Wenn gemeldet wurde, dass im [[Header/DE|HTTP-Header]] die X-Frame Optionen nicht gesetzt sind, ist Ihre Webseite nicht ausreichend gegen [[Clickjacking|Clickjacking-Angriffe]] geschützt.

Im [[Header/DE|HTTP-Header]] X-Frame Optionen entsprechend den Bedürfnissen setzen. Die '''X-Frame-Options''' im [[HTTP]] Header kann verwendet werden, um zu bestimmen, ob ein aufrufender [[Browser]] die Zielseite in einem <frame>, <iframe> oder bspw. <object> rendern bzw. einbetten darf. Webseiten können diesen Header verwenden, um u. a. [[Clickjacking|Clickjacking-Angriffe]] abzuwehren, indem sie unterbinden, dass ihr Content in fremden Seiten eingebettet wird.

Mit dem HTTP-Header Befehl '''X-Frame-Options''' können moderne Webbrowser angewiesen werden, eine Seite nicht in einem Frame auf einer andere Website zu laden. Dafür muss der folgende Befehl in der htaccess-Datei gesetzt werden:

Header always append X-Frame-Options DENY

 Header always append X-Frame-Options DENY

Alternativ kann erlaubt werden, dass die Seite nur auf anderen Seiten der gleichen Domain eingebunden werden dürfen:

 Header always append X-Frame-Options SAMEORIGIN

Falls eine Website doch extern eingebunden werden muss, kann eine Domain angegeben werden:

 Header always append X-Frame-Options ALLOW-FROM botfrei.de

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

X-Frame-Options-Schwachstelle

### Negative

HTTP-[[Header/DE|Header]] X-Frame Optionen nicht gesetzt.

### Positive

Der [[Header/DE|Header]] ist korrekt gesetzt und verbessert den Schutz gegen Framing-Angriffe wie beispielsweise UI-Redressing bzw. Clickjacking.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## X_XSS_PROTECTION

### Headline

Überprüfung des X-XSS-Protection Headers

### Category

Webserver

### Description

Der [[Header/DE|HTTP-Header]] X-XSS-Protection definiert wie in [[Browser|Browsern]] eingebaute XSS-Filter konfiguriert werden. Eine Default-Installation kann eine unzureichende Konfiguration offenbaren.

### Background

Dieser [[Header/DE|Header]] aktiviert den in den meisten aktuellen [[Browser|Browsern]] (Internet Explorer, Chrome und Safari) eingebauten [[Cross-Site Scripting|Cross-Site Scripting-Schutz]] ([[XSS-Schwachstelle/DE|XSS]]). Zwar ist der Schutz standardmäßig aktiviert - daher ist dieser Header nur dazu da, den Filter ggfs. wieder zu aktivieren, falls der Benutzer ihn abgeschaltet hat. Zudem wird dieser Header nur ab dem IE 8+, Opera, Chrome und Safari unterstützt.

### Consequence

Verhindert reflektierte [[Cross-Site Scripting|XSS-Angriffe]]. Einfach zu implementieren und erfordert keine weiteren Anpassungen auf der Website.

### Solution_Tips

Wenn gemeldet wurde, dass Ihre Webseite wahrscheinlich nicht ausreichend gegen [[Cross-Site Scripting|XSS-Angriffe]] geschützt ist:

 1; mode=block

'''Beispielcode einer .htaccess auf einem Apache Webserver'''

  # Turn on XSS prevention tools, activated by default in IE and Chrome
  '''Header set X-XSS-Protection "1; mode=block"'''

Hier finden Sie ein Beispiel, wie eine .htaccess-Datei aussehen kann, um einen höheren Wert beim '''Header Scanner''' zu erzielen.
([[Htaccess/DE|.htaccess-Beispiel]])

### Link

XSS-Schwachstelle

### Negative

Der [[Cross-Site Scripting|Cross-Site Scripting-Schutz]] ([[XSS-Schwachstelle/DE|XSS]]) ist nicht aktiviert oder unzureichend konfiguriert.

### Positive

Der [[Cross-Site Scripting|Cross-Site Scripting-Schutz]] ([[XSS-Schwachstelle/DE|XSS]]) des [[Browser|Webbrowsers]] ist auf Ihrer Seite aktiviert.

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

## _RESULTS

### CSP_CORRECT

Der [[Header/DE|Header]] ist korrekt gesetzt und entspricht den Empfehlungen.

### CSP_DEFAULT_SRC_MISSING

Die Anweisung default-src fehlt.

### CSP_LEGACY_HEADER_SET

Der veraltete [[Header/DE|Header]] '':HEADER_NAME'' ist gesetzt. Der neue standardisierte [[Header/DE|Header]] ist ''Content-Security-Policy''.

### CSP_NO_UNSAFE_INCLUDED

Die Content-Security-Policy-Schwachstelle (CSP) enthält keine unsicheren (unsafe) Direktiven, ist möglicherweise jedoch nicht sicher eingestellt.

### CSP_UNSAFE_INCLUDED

Der [[Header/DE|Header]] ist unsicher gesetzt, da er `unsafe-inline`- oder `unsafe-eval`-Direktiven enthält.

### CT_CORRECT

Der [[Header/DE|Header]] ist korrekt gesetzt und entspricht den Empfehlungen.

### CT_HEADER_WITHOUT_CHARSET

Der [[Header/DE|Header]] ist ohne Zeichensatzangabe gesetzt und dadurch nicht sicher.

### CT_HEADER_WITH_CHARSET

Der [[Header/DE|Header]] ist korrekt gesetzt und beinhaltet eine Zeichensatz-Angabe.

### CT_META_TAG_SET

Der [[Header/DE|Header]] ist korrekt gesetzt und enthält jedoch keine Zeichensatz-Angabe oder folgt nicht den Empfehlungen. Gefunden wurde ":META".

### CT_META_TAG_SET_CORRECT

Die Angabe ":META" im HTML-[[Header/DE|Header]] ist korrekt gesetzt.

### CT_WRONG_CHARSET

Ein falscher oder ungültiger Zeichensatz wurde eingetragen. Die Konfiguration ist nicht sicher.

### DIRECTIVE_SET

Die Anweisung 'DIRECTIVE' ist gesetzt.

### EMPTY_DIRECTIVE

Die Anweisung ist ausdrücklich als leer gekennzeichnet.

### HEADER_ENCODING_ERROR

Der [[Header/DE|Header]] ''':HEADER_NAME''' enthält [[Ersetzungszeichen|nicht-verarbeitbare]] Zeichen.

### HEADER_NOT_SET

Der [[Header/DE|Header]] ist nicht gesetzt.

### HEADER_SET_MULTIPLE_TIMES

Der [[Header/DE|Header]] wurde mehrmals gesetzt.

### HPKP_LESS_15

Die [[Verschlüsselung|öffentlichen Schlüssel]] sind für weniger als 15 Tage [[HTTP_Public_Key_Pinning|gepinnt]].

### HPKP_MORE_15

Die [[Verschlüsselung|Schlüssel]] sind für mehr als 15 Tage [[HTTP_Public_Key_Pinning|gepinnt]].

### HPKP_REPORT_URI

Eine `report-uri` ist gesetzt.

### HSTS_LESS_6

Der Wert von `max-age` ist kleiner als 6 Monate.

### HSTS_MORE_6

Der Wert von `max-age` ist größer als 6 Monate.

### HSTS_PRELOAD

Die `preload`-Direktive ist gesetzt.

### HTTPONLY_FLAG_SET

Das HttpOnly Flag ist gesetzt.

### INCLUDE_SUBDOMAINS

`includeSubDomains` ist gesetzt.

### INVALID_HEADER

Die folgenden Elemente Ihres [[Header/DE|Headers]] sind ungültig:
:HEADER

### MAX_AGE_ERROR

Es trat ein Fehler beim Überprüfen der `max-age`-Angabe auf.

### NO_HTTPONLY_FLAG_SET

Das HttpOnly Flag ist nicht gesetzt.

### NO_HTTP_RESPONSE

Die angegebene [[URL]] lieferte keine Antwort.

### NO_SECURE_FLAG_SET

Das Secure Flag ist nicht gesetzt.

### SECURE_FLAG_SET

Das Secure Flag ist gesetzt.

### WRONG_DIRECTIVE_SET

Eine falsche oder unbekannte Anweisung ist gesetzt.

### XCTO_CORRECT

Der [[Header/DE|Header]] ist korrekt gesetzt und entspricht den Empfehlungen.

### XCTO_NOT_CORRECT

Der [[Header/DE|Header]] ist nicht korrekt gesetzt.

### XFO_CORRECT

Der [[Header/DE|Header]] ist korrekt gesetzt und entspricht den Empfehlungen.

### XFO_WILDCARDS

Der [[Header/DE|Header]] enthält Wildcard-Angaben (*) und ist daher nicht sicher konfiguriert.

### XXSS_BLOCK

Die `mode=block`-Direktive ist aktiviert.

### XXSS_CORRECT

Der [[Header/DE|Header]] ist korrekt gesetzt und entspricht den Empfehlungen.
