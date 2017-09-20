clear
# copying test data
#cp data/test*.pdf data/inbox/

docker run --name ppyrd --rm \
  -v "$(pwd)/app:/app" \
  -v "$(pwd)/data/database:/data/database" \
  -v "$HOME/Archiv/10_System/20_with_ocr:/data/inbox" \
  -v "$HOME/Archiv/10_System/21_renamed_with_paperyard:/data/outbox" \
  -v "$HOME/Archiv/10_System/22_checked_and_to_be_archived:/data/sort" \
  -i -t ppyrd_image
