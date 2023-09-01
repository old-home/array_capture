#!/usr/bin/env bash

USER_ID=${LOCAL_UID:-9001}
GROUP_ID=${LOCAL_GID:-9001}

echo "Starting with UID : $USER_ID, GID: $GROUP_ID"

groupadd -g "$GROUP_ID" docker
useradd -u "$USER_ID" -g "$GROUP_ID" docker
export HOME=/home/docker
mkdir /home/docker
chown docker: $HOME

exec /usr/sbin/gosu docker "$@"
