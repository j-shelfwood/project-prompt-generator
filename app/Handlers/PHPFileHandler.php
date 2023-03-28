<?php

namespace App\Handlers;

class PHPFileHandler extends AbstractFileHandler
{
    public function buildPrompt(): string
    {
        // Your implementation to describe PHP files and return a text prompt to send to openAI
        // Strip ALL newlines from content
        $content = preg_replace('/\s+/', '', $this->content);
        // Remove opening <?php tag
        $content = preg_replace('/^<\?php/', '', $content);
        // Remove newline characters and escape single quotes
        $content = str_replace(["\r", "\n", "'"], ['', '', "\'"], $content);

        return "[INSTRUCTIONS]Create a concise description of a Laravel project's PHP class, focusing on the domain logic and unique or custom methods relevant to the class's functionality, while avoiding explanations of default Laravel functionality. Use the provided format and strictly adhere to it for the response: file_path|namespace|classname|extends:parent_class|uses:trait1,trait2,external_class1|property1:type1|property2:type2|method1:returntype1(arg1:type1,arg2:type2)|method2:returntype2(arg1:type1)|notes:[note1;note2;...][FILE_TO_DESCRIBE]{$content}";
    }
}
