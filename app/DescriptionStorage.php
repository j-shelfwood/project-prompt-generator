<?php

namespace App;

use Illuminate\Support\Facades\DB;

class DescriptionStorage
{
    public function saveOrUpdateDescription($file, $description)
    {
        DB::table('file_descriptions')->updateOrInsert(
            ['file_path' => $file],
            ['description' => $description]
        );
    }

    public function getFileDescriptions()
    {
        return DB::table('file_descriptions')->get();
    }

    public function getFilePathsInDatabase()
    {
        return DB::table('file_descriptions')->pluck('file_path')->toArray();
    }
}
