#!/usr/bin/env sh
if ! getent group ${APPLICATION_GID} >/dev/null 2>&1; then
    groupmod -g ${APPLICATION_GID} application
fi

if ! id ${APPLICATION_UID} >/dev/null 2>&1; then
    usermod -u ${APPLICATION_UID} application
fi
