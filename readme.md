# Hackmanit HeaderChecker

### Entwicklungsstatus

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