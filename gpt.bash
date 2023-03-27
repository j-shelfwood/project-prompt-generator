#!/bin/bash

# 1. Add content_hash column to files table
php prompt make:migration add_content_hash_to_files_table --table=files

# 2. Update DescriptionStorage to save or update the content hash along with the file description
sed -i '' 's/public function saveOrUpdateDescription(/public function saveOrUpdateDescriptionWithHash(/g' app/DescriptionStorage.php
sed -i '' 's/$description)/$description, $contentHash)/g' app/DescriptionStorage.php
cat <<'EOF' >> app/DescriptionStorage.php
public function saveOrUpdateDescription($projectId, $filePath, $description)
{
    $contentHash = $this->getContentHash($filePath);
    $this->saveOrUpdateDescriptionWithHash($projectId, $filePath, $description, $contentHash);
}

private function getContentHash($filePath)
{
    return md5_file($filePath);
}
EOF

# 3. Modify GeneratePromptCommand to check for content changes before proceeding
sed -i '' 's/$remainingFiles as $file) {/$remainingFiles as $file) {\n            $fileContentHash = $descriptionStorage->getFileContentHash($file);\n            $currentContentHash = md5_file($file);\n            if ($fileContentHash === $currentContentHash) {\n                continue;\n            }/g' app/Commands/GeneratePromptCommand.php
cat <<'EOF' >> app/DescriptionStorage.php
public function getFileContentHash($filePath)
{
    $file = DB::table('files')->where('path', $filePath)->first();
    return $file ? $file->content_hash : null;
}
EOF

# 4. Add a helper function to compute the content hash of a given file
sed -i '' 's/protected $directory;/protected $directory;\n\n    private function getContentHash($fileContents)\n    {\n        return md5($fileContents);\n    }\n/g' app/FileAnalyzer.php
sed -i '' 's/$fileContents);/$fileContents), $this->getContentHash($fileContents));/g' app/OpenAIDescriber.php

echo "Changes have been made successfully."
