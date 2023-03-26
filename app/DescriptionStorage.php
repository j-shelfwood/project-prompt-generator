<?php

namespace App;

use Illuminate\Support\Facades\DB;

class DescriptionStorage
{
    public function saveOrUpdateDescription($projectId, $filePath, $description)
    {
        $projectId = DB::table('projects')->where('id', $projectId)->first()->id;

        DB::table('files')->updateOrInsert(
            ['path' => $filePath],
            ['project_id' => $projectId, 'description' => $description]
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
}
