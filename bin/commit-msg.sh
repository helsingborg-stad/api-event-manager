#!/bin/sh
MESSAGE="\n✋ Aborting commit. Your commit message is invalid. Format must follow the Conventional Commits specification.\n👉 See https://www.conventionalcommits.org/en/v1.0.0/ \n" >&2

# Enforce conventional commit message format
if ! head -1 "$1" | grep -qE "^(feat|fix|chore|docs|test|style|refactor|perf|build|ci|revert|Merge)(\(.+?\))?: .{1,}$"; then
    echo $MESSAGE;
    exit 1;
fi