#!/bin/sh

## Deploy.sh
## @author Hargobind Khalsah (with Revisions by Joshua Canfield)
## @description deploy.sh should be in the root directory of your project with a chmod of +755. This script will initiate the deployment and push to BitBucket along with receiving new changes from the Remote Repository.

LOCAL_BRANCH="master"
LIVE_BRANCH="live"
REMOTE_NAME="deploy"

if [ "$(git symbolic-ref -q HEAD)" != "refs/heads/${LOCAL_BRANCH}" ]; then
  echo "Not on ${LOCAL_BRANCH}, not deploying"
  exit 1
else
  echo "Syncing and deploying"
  git push ${REMOTE_NAME} ${LOCAL_BRANCH}:master
  git pull ${REMOTE_NAME} ${LIVE_BRANCH}
  git push ${REMOTE_NAME} ${LOCAL_BRANCH}:master
fi