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

# Create GitHub release
echo "🚀 Creating GitHub release..."
gh release create "$TAG" \
    --title "Version $VERSION" \
    --notes "## What's New in Version $VERSION

- Enhanced folder management functionality
- Improved admin interface and user experience
- Better error handling and security
- Performance optimizations

## Requirements

- WordPress 5.8+
- PHP 7.4+

## Installation

Download and install via WordPress admin or upload manually."

echo "✅ Release created successfully!"
echo "🔗 View release at: https://github.com/mateitudor/wp-folders/releases" 