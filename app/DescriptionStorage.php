<?php

namespace App;

use Illuminate\Support\Facades\DB;

class DescriptionStorage
{
    public function saveOrUpdateDescription($projectId, $filePath, $description, $contentHash)
    {
        $projectId = DB::table('projects')->where('id', $projectId)->first()->id;

        DB::table('files')->updateOrInsert(
            ['path' => $filePath],
            ['project_id' => $projectId, 'description' => $description, 'content_hash' => $contentHash]
        );
    }

    public function getFileDescriptions($projectPath)
    {
        $projectId = DB::table('projects')->where('path', $projectPath)->first()->id;

        return DB::table('files')->where('project_id', $projectId)->get()->toArray();
    }

    public function getFilePathsInDatabase()
    {
        return DB::table('files')->pluck('path')->toArray();
    }

    public function isProjectDescribed($projectPath)
    {
        $project = DB::table('projects')->where('path', $projectPath)->first();

        if (! $project) {
            return false;
        }

        $files = DB::table('files')->where('project_id', $project->id)->whereNull('description')->count();

        return $files === 0;
    }

    public static function getRawCode($projectId)
    {
        $project = DB::table('projects')->where('id', $projectId)->first();
        $files =
            (new FileAnalyzer($project->path))
                ->getFilesToDescribe()
                ->map(function ($file) {
                    return [
                        'content' => file_get_contents($file),
                        'path' => $file,
                    ];
                })->toArray();

        $rawCode = '';

        foreach ($files as $file) {
            $rawCode .= "[{$file['path']}]=>{$file['content']}";
        }

        // Remove newlines
        $rawCode = preg_replace('/\s+/', ' ', $rawCode);

        return $rawCode;
    }

    public function getFileContentHash($filePath)
    {
        $file = DB::table('files')->where('path', $filePath)->first();

        return $file ? $file->content_hash : null;
    }
}
