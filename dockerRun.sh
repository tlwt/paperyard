# where do you store your documents
paperyardDocRoot=$HOME/Archiv/10_System

# files which have OCRed yet - will be moved to next folder
unocredFiles=$paperyardDocRoot/10_scan

# files which have not been processed yet. PDFs with OCR, but no useful name
unnamedFiles=$paperyardDocRoot/20_with_ocr

# files which have been renamed
renamedFiles=$paperyardDocRoot/21_renamed_with_paperyard

# files which will be sorted
toSort=$paperyardDocRoot/22_checked_and_to_be_archived

#where shall the database be stored?
database=$paperyardDocRoot/01_ppyrd

#which port shall be exposed?
port=80

# set to true to use local modified app instead of current published version from github
localDevelopment=false

if $localDevelopment ; then
  echo local
  docker run --name ppyrd --rm \
      -v "$(pwd)/paperyard:/var/www/html/" \
      -v "$database:/data/database" \
      -v "$unocredFiles:/data/scan" \
      -v "$unnamedFiles:/data/inbox" \
      -v "$renamedFiles:/data/outbox" \
      -v "$toSort:/data/sort" \
      -p "$port:80" \
      -i -t ppyrd_image
else
  echo from github
  docker run --name ppyrd --rm \
      -v "$database:/data/database" \
      -v "$unocredFiles:/data/scan" \
      -v "$unnamedFiles:/data/inbox" \
      -v "$renamedFiles:/data/outbox" \
      -v "$toSort:/data/sort" \
      -p "$port:80" \
      -i -t ppyrd_image
fi
