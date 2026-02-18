#!/usr/bin/env bash
# Deploy to dev (tkurydyfjkv) via rsync. Does not overwrite config.php or admin/config.php.
#
#   export SSHPASS='your_ssh_password'
#   ./deploy-dev.sh
#
# Requires: sshpass (brew install sshpass)

set -e
SERVER="dh_rasa5k@iad1-shared-e1-18.dreamhost.com"
REMOTE="diversiply.co/tkurydyfjkv"
LOCAL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if ! command -v sshpass >/dev/null 2>&1; then
  echo "sshpass not found. Install with: brew install sshpass"
  exit 1
fi

if [ -z "${SSHPASS}" ]; then
  echo "Set SSH password first: export SSHPASS='your_password'"
  exit 1
fi

echo "Deploying to $SERVER:$REMOTE (excluding config.php, admin/config.php) ..."
rsync -avz -e "sshpass -e ssh" \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'storage/logs/*' \
  --exclude 'config.php' \
  --exclude 'admin/config.php' \
  --exclude '.dh-diag' \
  "$LOCAL_DIR/" "$SERVER:~/$REMOTE/"

echo "Done. Test at https://tkurydyfjkv.diversiply.co/"
