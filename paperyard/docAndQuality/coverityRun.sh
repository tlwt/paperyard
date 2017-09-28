#!/bin/bash
/cov-analysis-linux/bin/cov-build --dir /cov-int --no-command --fs-capture-search /www/backend
tar czvf /ppyrd.tgz /cov-int
curl --form token=-oTLrr-4-FvxiEFj2SFjTw \
  --form email=mail@tillwitt.de \
  --form file=@/ppyrd.tgz \
  --form version="0.1" \
  --form description="Paperyard" \
  https://scan.coverity.com/builds?project=tlwt%2Fpaperyard
rm -rf /cov-int
rm /ppyrd.tgz
