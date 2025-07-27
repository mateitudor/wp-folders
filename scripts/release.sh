#!/bin/bash

# Release script for Folders WordPress Plugin
# Usage: ./scripts/release.sh 2.9.1

if [ -z "$1" ]; then
    echo "Usage: ./scripts/release.sh <version>"
    echo "Example: ./scripts/release.sh 2.9.1"
    exit 1
fi

VERSION=$1
TAG="v$VERSION"

echo "🚀 Creating release for version $VERSION..."

# Update version in folders.php header comment
echo "📝 Updating version in folders.php..."
sed -i '' "s/Version: [0-9.]*/Version: $VERSION/" folders.php

# Update version in readme.txt if it exists
if [ -f "readme.txt" ]; then
    echo "📝 Updating version in readme.txt..."
    sed -i '' "s/Stable tag: [0-9.]*/Stable tag: $VERSION/" readme.txt
fi

# Commit changes
echo "💾 Committing version changes..."
git add .
git commit -m "Version $VERSION"

# Push to main
echo "📤 Pushing to main..."
git push origin main

# Create and push tag
echo "🏷️  Creating tag $TAG..."
git tag $TAG
git push origin $TAG

# Get commit messages since last tag
echo "📝 Getting commit messages..."
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null | head -1)
if [ -z "$LAST_TAG" ]; then
    COMMITS=$(git log --oneline --no-merges)
else
    COMMITS=$(git log --oneline --no-merges ${LAST_TAG}..HEAD)
fi

# Create release notes
RELEASE_NOTES="## What's New in Version $VERSION

### Changes in this release:

$COMMITS

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Installation

Download and install via WordPress admin or upload manually."

# Create GitHub release
echo "🚀 Creating GitHub release..."
gh release create "$TAG" \
    --title "Version $VERSION" \
    --notes "$RELEASE_NOTES"

echo "✅ Release created successfully!"
echo "🔗 View release at: https://github.com/mateitudor/wp-folders/releases" 