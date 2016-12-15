# Hackmanit HeaderChecker

### Entwicklungsstatus

- [ ] Allgemein
    - [X] Feature: Arbeite im Hintergrund / Warteschlange
    - [X] Feature: Cache HTTP-Responses
    - [ ] Code coverage / PHP Unit testing
    - [ ] Improve Readme.md
    - [ ] Generate combined Docker image
    - [ ] Manage GitHub Repo
    - [ ] Manage Docker build on push to GitHub

- [ ] Crawler implementieren
    - [X] Feature: doNotCrawl
    - [X] Feature: Proxy-Support
    - [X] Feature: ignore TLS Certificate Errors
    - [X] Feature: Selecting HTML-Tags to check for URLs
    - [X] Feature: Limit the URLs to test
    - [ ] Refactor Code
    - [ ] Improve Comments
    
- [ ] Reports implementieren
    - [ ] Feature: Download Report


****************    


#### Redis Speicher
Redis ist ein komfortabler KEY-VALUE Speicher.

Besonders wegen seiner Performance gegenüber einer klassischen SQL-Datenbank wird dieser Speicher eingesetzt.
Aber auch, da keine Ergebnisse dauerhaft gespeichert werden.

Es wird daher nicht mit Eloquent gearbeitet.


#### URL-Crawling
Es wird zunächtst die übergebene URL nach weiteren URLs durchsucht.
Alle HTTP-Responses werden in Redis zwischengespeichert, damit mehrfache Anfragen vermieden werden.


#### Background jobs
Unbedingt die Anzahl der Versuche angeben, sonst Endlosschleife:
`--tries=3`


#### Scan von meta-Tags
Voerst nicht implementiert. Macht das Sinn? Im OWASP ZAP sind die Fälle `location` und `refresh` enthalten.
Der Crawler folgt den URLs bei redirect via `location` sowieso.
`refresh` leitet i.d.R. auf interne Seite weiter, die ohnehin gefunden werden sollte, bei externer Seite nicht beachtet.