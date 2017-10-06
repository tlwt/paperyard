# files which have OCRed yet - will be moved to next folder
unocredFiles=$HOME/Archiv/10_System/10_scan

# files which have not been processed yet. PDFs with OCR, but no useful name
unnamedFiles=$HOME/Archiv/10_System/20_with_ocr

# files which have been renamed
renamedFiles=$HOME/Archiv/10_System/21_renamed_with_paperyard

# files which will be sorted
toSort=$HOME/Archiv/10_System/22_checked_and_to_be_archived


# set to true to use local modified app instead of current published version from github
localDevelopment=true

if $localDevelopment ; then
  echo local
  docker run --name ppyrd --rm \
      -v "$(pwd)/data/database:/data/database" \
      -v "$(pwd)/paperyard:/var/www/html/" \
      -v "$unocredFiles:/data/scan" \
      -v "$unnamedFiles:/data/inbox" \
      -v "$renamedFiles:/data/outbox" \
      -v "$toSort:/data/sort" \
      -p 80:80 \
      -i -t ppyrd_image
else
  echo from github
  docker run --name ppyrd --rm \
      -v "$(pwd)/data/database:/data/database" \
      -v "$unocredFiles:/data/scan" \
      -v "$unnamedFiles:/data/inbox" \
      -v "$renamedFiles:/data/outbox" \
      -v "$toSort:/data/sort" \
      -p 80:80 \
      -i -t ppyrd_image
fi
