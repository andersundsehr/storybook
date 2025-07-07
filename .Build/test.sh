#!/bin/bash

# set PWD to parent directory of this script
export PWD=$(dirname $(dirname $(realpath "$0")))

export COMPOSE_PROJECT_NAME=testing-storybook

#if [ -S /tmp/.X11-unix/X0 ]; then
  export X11_SOCKET=/tmp/.X11-unix
#fi

# if vendor is not present run testFunction composerInstall
if [ ! -d "$PWD/.Build/dummy-project/vendor" ]; then
  echo "Vendor directory not found, running composer install..."
  testFunction composerInstall
fi

# if node_modules is not present run testFunction storybookBuild
if [ ! -d "$PWD/.Build/dummy-project/node_modules" ]; then
  echo "Node modules directory not found, running storybook build..."
  testFunction storybookBuild
fi

function testFunction {
  key="$1"
  case ${key} in
     executeTests)
        testFunction composerInstall && \
        testFunction storybookBuild && \
        testFunction playwright
        return
        ;;
     install)
        testFunction composerInstall && \
        testFunction storybookBuild
        return
        ;;
     composerInstall)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans typo3 su application -c 'composer update'
        return
        ;;
     storybookBuild)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm i && npm run build-storybook'
        return
        ;;
     playwright)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npx playwright test'
        return
        ;;
     playwright:ui)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npx playwright test --ui'
        return
        ;;
     playwright:open)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm run storybook & sleep 1 ; npx playwright open http://localhost:8080/'
        return
        ;;
     watchMode)
        watch 'rsync -av --exclude=.Build ../ dummy-project/vendor/andersundsehr/storybook/'
        return
        ;;
     *)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml "${@:1}"
        return
        ;;
  esac
}

testFunction "${@:1}"
        exit $?
