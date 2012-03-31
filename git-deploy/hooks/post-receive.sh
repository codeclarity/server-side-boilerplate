#!/bin/sh

## post-receive.sh
## @author Hargobind Khalsah (with Revisions by Joshua Canfield)
## @description post-receive.sh should be in your remote GIT post-hooks directory with a chmod of +755.

cd "$(dirname $0)/.."
export GIT_DIR="."
export GIT_WORK_TREE=".."

if [ "$(git symbolic-ref -q HEAD)" != "refs/heads/live" ]; then
  echo "Not on live, not deploying"
  exit 1
elif ! ./hooks/sync; then
  echo "Sync failed"
  exit 1
elif ! git merge --ff-only "refs/heads/master"; then
  echo "New changes on live, not deploying"
  exit 1
else
  echo "Changes deployed"
  exit 0
fi