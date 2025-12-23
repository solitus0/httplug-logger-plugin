#!/usr/bin/env python3

import subprocess
import re
import sys
from pathlib import Path

VERSION_FILE = Path('.version')


def run(cmd):
    subprocess.run(cmd, check=True)


def read_version() -> str:
    if not VERSION_FILE.exists():
        raise FileNotFoundError(".version file not found")
    version = VERSION_FILE.read_text().strip()
    # Accept with or without "v"
    m = re.match(r"^v?(\d+)\.(\d+)\.(\d+)$", version)
    if not m:
        raise ValueError(f"Invalid version format: {version}")
    return f"{m.group(1)}.{m.group(2)}.{m.group(3)}"  # bare version


def bump_version(version: str, part: str) -> str:
    major, minor, patch = map(int, version.split('.'))

    if part == "major":
        return f"{major + 1}.0.0"
    elif part == "minor":
        return f"{major}.{minor + 1}.0"
    elif part == "patch":
        return f"{major}.{minor}.{patch + 1}"
    else:
        raise ValueError("Invalid bump part. Use: major, minor, or patch")


def write_version(version: str):
    VERSION_FILE.write_text(version + '\n')


def git_commit_and_push(new_version: str):
    run(['git', 'add', str(VERSION_FILE)])
    run(['git', 'commit', '-m', f'chore(release): {new_version}'])
    run(['git', 'push', 'origin', 'HEAD'])


def git_tag_and_push(version: str):
    tag_name = f"v{version}"  # Composer convention
    run(['git', 'tag', '-a', tag_name, '-m', f'Release {version}'])
    run(['git', 'push', 'origin', tag_name])


def main():
    part = sys.argv[1] if len(sys.argv) > 1 else "patch"  # default patch
    current_version = read_version()
    new_version = bump_version(current_version, part)
    write_version(new_version)
    git_commit_and_push(new_version)
    git_tag_and_push(new_version)
    print(f"Bumped {part} version: {current_version} → {new_version}, tagged v{new_version}.")


if __name__ == "__main__":
    main()
