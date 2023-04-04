<?php

namespace App\Handlers;

class PHPFileHandler extends AbstractFileHandler
{
    public function buildPrompt(): string
    {
        $content = $this->strippedContent();

        return "Convert the given PHP file to a single line compressed representation using the following format:

- Separate elements with a vertical bar (|)
- Use the following abbreviations:
  - 'src' for 'source'
  - 'Ent' for 'Entities'
  - 'e' for 'extends'
  - 'u' for 'uses'
  - 'n' for 'notes'
- Remove colons where they don't improve clarity (e.g., properties and method arguments)
- Combine related information (e.g., properties with similar types)
- Use custom shorthand notations for frequently used patterns (e.g., 'CRUD' for a typical set of Create, Read, Update, and Delete methods)

Example:
- Original PHP file:
  namespace App\Entities;
  use DateTime;
  class Person extends Model {
    public %name;
    public %age;
    public function greet() { return 'Hello, ' . %this->name; }:string
    public function setAge(%age) { %this->age = %age; }:boolean
  }
  Note: Age should be between 0 and 120. Name should be unique.

- Compressed representation:
  src/Ent/Person.php|App\Ent|Person|eModel|uDateTime|namestr|ageint|greet()s|setAge(ageint)b|n[Age 0-120;Name unique]

Please convert the provided PHP file using this format and return the compressed representation.
".$content;
    }
}
