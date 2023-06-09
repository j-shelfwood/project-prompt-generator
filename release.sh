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
  new_version="$major_version.$((minor_version + 1)).0"
else
  new_version="$major_version.$minor_version.$((patch_version + 1))"
fi


# Generate a new build using the new version
echo "Building new version: $new_version"
php prompt app:build --build-version=$new_version &
wait $!

# Ask the user if they want to continue with the release
echo "New version built successfully!"
read -p "Do you want to continue with the release? [y/n] " -n 1 -r

# If the user doesn't want to continue, exit the script
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
  echo
  echo "Release aborted!"
  exit 1
fi

# Commit the changes and create a git tag
git add .
git commit -m "Release $new_version"
git tag "$new_version"

# Push the changes and the new tag to the main branch
git push --progress origin main &
wait $!
echo "Changes pushed successfully!"
git push --progress origin "$new_version" &
wait $!
echo "Changes pushed to version branch"
git push --progress --tags &
wait $!
echo "Tag pushed successfully!"

# Ask the user if they want to publish the release
echo "Release pushed successfully!"
