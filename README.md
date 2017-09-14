# PAPERYARD

ist ein Tool zur automatischen und regelbasierten Benennung von gescannten Dokumenten.

Paperyard kümmert sich nicht um die Texterkennung aber um die Erkennung von

* Datum
* Firma
* Betreff
* Empfänger

Ausgehend von einem Dateinamen in folgemden Format:

    ddatum - ffirma - bbetreff (wwer) [tags_][Beleg]_Sachlicher_Brief_Rechtschreibung

wird basierend auf den in der data/paperyard.sqlite hinterlegen Regeln folgende Datei:

    20090220 - Elektroschrott - Reklamation (wwer) [tags_][Beleg]_Sachlicher_Brief_Rechtschreibung.pdf


## Example

Das Programm wirft auf der Shell kurze Statusmeldungen aus:

    company: Elektroschrott has rating 30
    subject: Reklamation has rating 30
    new name: 20090220 - Elektroschrott - Reklamation (wwer) [tags_][Beleg]_Sachlicher_Brief_Rechtschreibung.pdf

Detailierte Logs sind in der Datenbank /data/paperyard.sqlite vorhanden

## build

Docker container bauen. Im Hauptverzeichnis folgenden Befehl ausführen:

      docker build -t ppyrd_image . 

## run

Programm starten. Im Hauptverzeichnis folgenden Befehl ausführen:

      docker run --name ppyrd --rm -v "$(pwd)/data:/data" -v "$(pwd)/app:/app" -i -t ppyrd_image