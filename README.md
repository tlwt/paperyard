# PAPERYARD

ist ein Tool zur automatischen und regelbasierten Benennung von gescannten Dokumenten.

Paperyard kümmert sich nicht um die Texterkennung aber um die Erkennung von

* Datum
* Firma
* Betreff
* Empfänger


Docker container bauen. Im Hauptverzeichnis folgenden Befehl ausführen:

      docker build -t ppyrd_image . 


Programm starten. Im Hauptverzeichnis folgenden Befehl ausführen:

      docker run --name ppyrd --rm -v "$(pwd)/data:/data" -v "$(pwd)/app:/app" -i -t ppyrd_image