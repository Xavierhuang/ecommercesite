#!/bin/bash
# Deploy this repo to dev: tkurydyfjkv.diversiply.co
# Run from project root: ./deploy-to-dev.sh
# Doc root: /home/dh_pyv57i/tkurydyfjkv.diversiply.co
#
# Password: use sshpass so you are not prompted 3 times.
#   Install: brew install sshpass (macOS) or apt install sshpass (Linux)
#   Option 1: export SSHPASS='yourpassword' before running, or
#   Option 2: create .deploy-secret (one line = password) and chmod 600 .deploy-secret

set -e
cd "$(dirname "$0")"

REMOTE="dh_pyv57i@iad1-shared-e1-18.dreamhost.com"
REMOTE_DIR="tkurydyfjkv.diversiply.co"

if command -v sshpass >/dev/null 2>&1 ; then
  if [ -z "${SSHPASS}" ] && [ -f ".deploy-secret" ]; then
    export SSHPASS="$(cat .deploy-secret)"
  fi
  if [ -n "${SSHPASS}" ]; then
    SSH_OPTS="-o StrictHostKeyChecking=accept-new -o ConnectTimeout=15 -o ServerAliveInterval=10 -o ServerAliveCountMax=3"
    SSH_CMD="sshpass -e ssh $SSH_OPTS"
    RSYNC_SSH="sshpass -e ssh $SSH_OPTS"
  else
    SSH_OPTS="-o ConnectTimeout=15 -o ServerAliveInterval=10 -o ServerAliveCountMax=3"
    SSH_CMD="ssh $SSH_OPTS"
    RSYNC_SSH="ssh $SSH_OPTS"
  fi
else
  SSH_OPTS="-o ConnectTimeout=15 -o ServerAliveInterval=10 -o ServerAliveCountMax=3"
  SSH_CMD="ssh $SSH_OPTS"
  RSYNC_SSH="ssh $SSH_OPTS"
fi

echo "Deploying to DEV: $REMOTE:$REMOTE_DIR"
if [ -n "${SSHPASS}" ]; then
  echo "Using sshpass (no password prompts)."
else
  echo "You may be prompted for the server password (install sshpass + set SSHPASS or .deploy-secret to avoid)."
fi
echo ""

rsync -avz --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'storage/logs/*' \
  --exclude 'bacha_iad1-mysql-e2-15a_dreamhost_com.sql' \
  --exclude '.dh-diag' \
  -e "$RSYNC_SSH" \
  ./ \
  "$REMOTE:$REMOTE_DIR/"

echo "Applying dev configs (tkurydyfjkv database)..."
$SSH_CMD "$REMOTE" "cp $REMOTE_DIR/deploy/tkurydyfjkv-config.php $REMOTE_DIR/config.php ; cp $REMOTE_DIR/deploy/tkurydyfjkv-admin-config.php $REMOTE_DIR/admin/config.php"

echo "Ensuring .htaccess, storage, and permissions..."
$SSH_CMD "$REMOTE" "cp $REMOTE_DIR/.htaccess.minimal $REMOTE_DIR/.htaccess 2>/dev/null || cp $REMOTE_DIR/.htaccess.dev-subdomain $REMOTE_DIR/.htaccess 2>/dev/null || cp $REMOTE_DIR/.htaccess.txt $REMOTE_DIR/.htaccess 2>/dev/null || true ; chmod 644 $REMOTE_DIR/.htaccess ; chmod 755 $REMOTE_DIR $REMOTE_DIR/admin $REMOTE_DIR/catalog $REMOTE_DIR/system ; chmod 751 ~ 2>/dev/null || true ; mkdir -p $REMOTE_DIR/storage/cache $REMOTE_DIR/storage/logs $REMOTE_DIR/storage/download $REMOTE_DIR/storage/upload $REMOTE_DIR/storage/session $REMOTE_DIR/storage/modification ; chmod -R 755 $REMOTE_DIR/storage"

echo ""
echo "Deploy finished. Test at: https://tkurydyfjkv.diversiply.co/"
