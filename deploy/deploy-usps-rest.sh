#!/bin/bash
# Upload USPS REST OpenCart extension files to a remote server over SSH/rsync.
# Usage:
#   export SSH_TARGET="user@your-server.com"
#   export REMOTE_ROOT="/path/to/opencart"   # directory containing admin/, catalog/, system/
#   bash deploy/deploy-usps-rest.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." ; pwd)"
if [ -z "${SSH_TARGET:-}" ] || [ -z "${REMOTE_ROOT:-}" ]; then
	echo "Set SSH_TARGET (e.g. user@host) and REMOTE_ROOT (OpenCart root on server)." >&2
	exit 1
fi
cd "$ROOT"
rsync -avzR \
	./system/library/usps_rest.php \
	./catalog/model/extension/shipping/usps_rest.php \
	./catalog/language/en-gb/extension/shipping/usps_rest.php \
	./admin/controller/extension/shipping/usps_rest.php \
	./admin/language/en-gb/extension/shipping/usps_rest.php \
	./admin/view/template/extension/shipping/usps_rest.twig \
	"${SSH_TARGET}:${REMOTE_ROOT}/"
echo "Done. On the server, clear cache: rm -rf storage/cache/*"
echo "Then Admin: Extensions -> Extensions -> Shipping -> USPS (REST / OAuth v3)."
