#!/usr/bin/env bash

set -eux

USER_ID=${USER_ID:-33}
GROUP_ID=${GROUP_ID:-33}

export APP_USER="www-data"
SUDO="sudo -E -u ${APP_USER}"

if [[ $(id ${APP_USER} > /dev/null 2>&1) != "${USER_ID}" ]]; then
    echo "replace user/group ids"
    deluser ${APP_USER} || true
    delgroup ${APP_USER} || true
    addgroup --gid ${GROUP_ID} ${APP_USER}
    adduser --system --uid ${USER_ID} --ingroup ${APP_USER} ${APP_USER}
    getent group ${GROUP_ID}
    getent passwd ${USER_ID}
fi

if [ -n "${*:-}" ]; then
    ${SUDO} ${*}
else
    php-fpm -F
fi
