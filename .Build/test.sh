#!/bin/bash

set -euo pipefail
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

function requireComposer {
  # if vendor is not present run testFunction composerInstall
  if [ ! -d "$DOCKER_ROOT_PWD/Documentation/dummy-project/vendor" ]; then
    echo "Vendor directory not found, running composer install..."
    testFunction composerInstall
  fi
}
function requireTheNpmPackage {
  # if dist is not present run testFunction buildTheNpmPackage
  if [ ! -d "$DOCKER_ROOT_PWD/the-npm-package/dist" ]; then
    echo "the-npm-package/dist not found building it..."
    testFunction buildTheNpmPackage
  fi
}
function requireStorybookBuild {
  # if node_modules is not present run testFunction storybookBuild
  if [ ! -d "$DOCKER_ROOT_PWD/Documentation/dummy-project/node_modules" ]; then
    echo "Node modules directory not found, running storybook build..."
    testFunction storybookBuild
  fi
}
function requireAll {
  requireComposer
  requireTheNpmPackage
  requireStorybookBuild
}

function testFunction {
  key="$1"
  case ${key} in
     executeAll)
       testFunction composerInstall
       testFunction buildTheNpmPackage
       testFunction storybookBuild
       testFunction playwright
       return
       ;;
     composerInstall)
       rm -rf dummy-project/vendor/
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans typo3 su application -c "rm -f composer.lock && composer req typo3/cms-core:^${TYPO3_VERSION}"
       return
       ;;
     buildTheNpmPackage)
       requireComposer
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans --no-deps playwright su ubuntu -c 'cd ../../the-npm-package && npm ci && npm run build && npm run test'
       testFunction copyToDev
       return
       ;;
     storybookBuild)
       requireComposer
       requireTheNpmPackage
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans --no-deps playwright su ubuntu -c 'npm ci && npm run build-storybook'
       return
       ;;
     playwright)
       requireAll
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test ${@:2}"
       return
       ;;
     playwright:u)
       requireAll
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test -u ${@:2}"
       return
       ;;
     playwright:ui)
       requireAll
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c "npx playwright test --ui ${@:2}"
       return
       ;;
     playwright:open)
       requireAll
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm run storybook & sleep 1 ; npx playwright open http://localhost:8080/'
       return
       ;;
     playwright:codegen)
       requireAll
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run --rm --remove-orphans playwright su ubuntu -c 'npm run storybook & sleep 1 ; npx playwright codegen http://localhost:8080/'
       return
       ;;
     watchMode)
       bash -c "COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml run -T --rm --remove-orphans --no-deps playwright su ubuntu -c 'cd ../../the-npm-package && npm ci && ./node_modules/.bin/tsc --watch'" &
       TSC_PID=$!
       trap "kill $TSC_PID" SIGHUP SIGINT SIGQUIT SIGABRT
       watch 'bash test.sh copyToDev'
       return
       ;;
     copyToDev)
       rsync -av --delete --exclude-from=exclude-watchMode.txt ../ ../Documentation/dummy-project/vendor/andersundsehr/storybook/
       return
       ;;
     reset)
       DP_PATH="$DOCKER_ROOT_PWD/Documentation/dummy-project"
       rm -rf $DP_PATH/node_modules/ $DP_PATH/vendor/ $DP_PATH/var/ $DP_PATH/public/ $DP_PATH/storybook-static/ $DP_PATH/test-results/ $DP_PATH/playwright-report/ $DP_PATH/composer.lock $DP_PATH/.env
       return
       ;;
     docs)
       # we need to remove all symlinks from the Documentation folder as the documentation-renderer does not support them
       rm -rf $DOCKER_ROOT_PWD/Documentation/dummy-project/node_modules
       rm -rf $DOCKER_ROOT_PWD/Documentation/dummy-project/public
       docker run --rm -it --pull always \
         -v "$DOCKER_ROOT_PWD/Documentation:/project/Documentation" \
         -v "$DOCKER_ROOT_PWD/Documentation-GENERATED-temp:/project/Documentation-GENERATED-temp" \
         -p 5173:5173 ghcr.io/garvinhicking/typo3-documentation-browsersync:latest
       return
       ;;
     *)
       COMPOSE_PROJECT_NAME=testing-storybook docker compose -f test.docker-compose.yml "${@:1}"
       return
       ;;
  esac
}

testFunction "${@:1}"
