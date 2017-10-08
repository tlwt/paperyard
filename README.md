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

zuerst müssen die Pfadangaben in der Datei angepasst werden, damit Paperyard weiß wo es nach Dokumenten suchen soll und wohin die korrekt benannten Dokumente hinterlegt werden sollen:

      ./dockerRun.sh (oder im Falle von Windows in .bat umbennenen und dann ausführen)


# license

Copyright 2017 consider it GmbH

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
