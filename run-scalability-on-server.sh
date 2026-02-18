#!/usr/bin/env bash
# Run scalability setup (session index + Redis config) on the dev server via sshpass.
#
# Prereqs: sshpass installed (brew install sshpass). Set SSH password:
#   export SSHPASS='your_ssh_password'
# Then run from project root:
#   ./run-scalability-on-server.sh
#
# Target: dev server dh_rasa5k@iad1-shared-e1-18.dreamhost.com, path ~/diversiply.co/tkurydyfjkv/

set -e
SERVER="dh_rasa5k@iad1-shared-e1-18.dreamhost.com"
REMOTE_DIR="diversiply.co/tkurydyfjkv"

if ! command -v sshpass >/dev/null 2>&1; then
  echo "sshpass not found. Install with: brew install sshpass"
  exit 1
fi

if [ -z "${SSHPASS}" ]; then
  echo "Set your SSH password first: export SSHPASS='your_password'"
  exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
SETUP_PHP="$SCRIPT_DIR/run-scalability-setup.php"
if [ ! -f "$SETUP_PHP" ]; then
  echo "Run from project root (where run-scalability-setup.php is)."
  exit 1
fi

echo "Copying run-scalability-setup.php to $SERVER:$REMOTE_DIR ..."
sshpass -e scp -o StrictHostKeyChecking=accept-new "$SETUP_PHP" "$SERVER:~/$REMOTE_DIR/"

echo "Running scalability setup (--redis) on $SERVER in $REMOTE_DIR ..."
sshpass -e ssh -o StrictHostKeyChecking=accept-new "$SERVER" "cd ~/$REMOTE_DIR ; php run-scalability-setup.php --redis"
