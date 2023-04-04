#!/bin/bash

# Get the current version from the git tag
current_version=$(git describe --tags --abbrev=0)

# Extract major, minor, and patch versions
IFS='.' read -ra version_parts <<< "$current_version"
major_version="${version_parts[0]}"
minor_version="${version_parts[1]}"
patch_version="${version_parts[2]}"

# Ask the user for the version update type
echo "Current version: $current_version"
PS3="Select version update type: "
select opt in "Main Version" "Minor Version" "Subversion"; do
  case $opt in
    "Main Version")
      version_update_type=$opt
      break
      ;;
    "Minor Version")
      version_update_type=$opt
      break
      ;;
    "Subversion")
      version_update_type=$opt
      break
      ;;
  esac
done

# Calculate the new version
new_version=""
if [ "$version_update_type" = "Main Version" ]; then
  new_version="$((major_version + 1)).0.0"
elif [ "$version_update_type" = "Minor Version" ]; then
  new_version="$major_version.$minor_version.$((patch_version + 1))"
else
  new_version="$major_version.$((minor_version + 1)).0"
fi

# Generate a new build using the new version
echo "Building new version: $new_version"
php prompt app:build -v "$new_version" &
wait $!

# Ask the user if they want to continue with the release
echo "New version built successfully!"
read -p "Do you want to continue with the release? [y/n] " -n 1 -r

# Commit the changes and create a git tag
git add .
git commit -m "Release $new_version"
git tag "$new_version"

# Push the changes and the new tag to the main branch
git push origin main &
wait $!
git push origin "$new_version" &
wait $!
git push --tags &
wait $!

# Ask the user if they want to publish the release
echo "Release pushed successfully!"
