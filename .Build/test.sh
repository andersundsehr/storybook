#!/bin/bash

#set -x

# set DOCKER_ROOT_PWD to parent directory of this script
export DOCKER_ROOT_PWD=$(dirname $(dirname $(realpath "$0")))

export APPLICATION_UID=$(id -u)

export COMPOSE_PROJECT_NAME=testing-storybook
export TYPO3_VERSION=${TYPO3_VERSION:-13.4.15}
# not implemented yet:
export STORYBOOK_VERSION=${STORYBOOK_VERSION:-9.0.0}

if [ -S /tmp/.X11-unix/X0 ]; then
  export X11_SOCKET=/tmp/.X11-unix
fi

function testFunction {
  key="$1"
  case ${key} in
     executeTests)
        testFunction composerInstall && \
        testFunction storybookBuild && \
        testFunction unitTests && \
        testFunction playwright
        return
        ;;
     install)
        testFunction composerInstall && \
        testFunction storybookBuild
        return
        ;;
     composerInstall)
        rm -rf dummy-project/vendor/
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans typo3 su application -c "rm -f composer.lock && composer req typo3/cms-core:^${TYPO3_VERSION}"
        return
        ;;
     storybookBuild)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm i && npm run build-storybook'
        return
        ;;
     unitTests)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'cd ../../the-npm-package && npm run test'
        return
        ;;
     playwright)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test ${@:2}"
        return
        ;;
     playwright:u)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test -u ${@:2}"
        return
        ;;
     playwright:ui)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test --ui ${@:2}"
        return
        ;;
     playwright:open)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm run storybook & sleep 1 ; npx playwright open http://localhost:8080/'
        return
        ;;
     playwright:codegen)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm run storybook & sleep 1 ; npx playwright codegen http://localhost:8080/'
        return
        ;;
     watchMode)
        watch "rsync -av --delete --filter=':- .gitignore' --exclude=Documentation --exclude=.git ../ ../Documentation/dummy-project/vendor/andersundsehr/storybook/"
        return
        ;;
     *)
        COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml "${@:1}"
        return
        ;;
  esac
}

# if vendor is not present run testFunction composerInstall
if [ ! -d "$DOCKER_ROOT_PWD/Documentation/dummy-project/vendor" ]; then
  echo "Vendor directory not found, running composer install..."
  testFunction composerInstall
fi

# if node_modules is not present run testFunction storybookBuild
if [ ! -d "$DOCKER_ROOT_PWD/Documentation/dummy-project/node_modules" ]; then
  echo "Node modules directory not found, running storybook build..."
  testFunction storybookBuild
fi

testFunction "${@:1}"
        exit $?
