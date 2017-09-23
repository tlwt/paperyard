# files which have not been processed yet. PDFs with OCR, but no useful name
unnamedFiles=$HOME/Archiv/10_System/20_with_ocr

# files which have been renamed
renamedFiles=$HOME/Archiv/10_System/21_renamed_with_paperyard

# files which will be sorted
toSort=$HOME/Archiv/10_System/22_checked_and_to_be_archived

# running the container
docker run --name ppyrd --rm \
  -v "$(pwd)/paperyard:/var/www/html/" \
  -v "$(pwd)/data/database:/data/database" \
  -v "$unnamedFiles:/data/inbox" \
  -v "$renamedFiles:/data/outbox" \
  -v "$toSort:/data/sort" \
  -p 80:80 \
  -i -t ppyrd_image
