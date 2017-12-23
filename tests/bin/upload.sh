#!/bin/sh

cd /home/travis/build/anspress/anspress/tests/_output/
rm .gitignore
rm .gitkeep

setup_git() {
  git config --global user.email "travis@travis-ci.org"
  git config --global user.name "Travis CI"
}

commit_website_files() {
  git init
  git checkout -b master
  git add -A
  git commit --message "Travis build: $TRAVIS_BUILD_NUMBER"
}

upload_files() {
  git remote add origin https://${GITHUB_TOKEN}@github.com/anspress/build-screenshots.git > /dev/null 2>&1
  git push -f --set-upstream origin master
}

setup_git
commit_website_files
upload_files