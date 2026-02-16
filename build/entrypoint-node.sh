#!/bin/bash
set -e

cd /app

needs_bootstrap=1
if [ -f package.json ] && node -e 'const p=require("/app/package.json"); process.exit(p?.scripts?.start ? 0 : 1)' ; then
  needs_bootstrap=0
fi

if [ "$needs_bootstrap" -eq 1 ]; then
  echo "Bootstrapping React app in /app..."
  TMP_APP_DIR="$(mktemp -d)"
  npx -y create-react-app "$TMP_APP_DIR" --use-npm --skip-git

  # Keep .gitkeep (if present) but reset all other files to avoid stale/broken partial scaffolds.
  find /app -mindepth 1 -maxdepth 1 ! -name '.gitkeep' -exec rm -rf {} +
  cp -r "$TMP_APP_DIR"/. /app/
  rm -rf "$TMP_APP_DIR"

  npm install
elif [ ! -d node_modules ]; then
  npm install
fi

npm start
