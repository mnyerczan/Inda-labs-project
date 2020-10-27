<?php

namespace App\Console;

use InvalidArgumentException;
use stdClass;

class LanguageContener
{
    // Alapértelmezet angol nyelvi beállítások

    // A nyelv bekérése
    private array $languageSetUp = [
        "languages" => [
            "englisg"
        ],
        "getLanguage" => "Please choose from the languages below: ",
    ];

    // Teljes fájl
    public ?object $fileContent;

    // A program neve
    private $messages;
    

    /**
     * OutputStrContainer constructor.
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->fileContent = json_decode(file_get_contents($filePath));
        $this->languages = array_merge($this->languageSetUp["languages"], array_keys(get_object_vars($this->fileContent)));
    }

    /**
     * @param object|null $language
     */
    public function setUp(?string $language = null)
    {
        // Behúzzuk az adatstruktúrát tartalmazó php-t és lementjük. Ez új.
        $this->messages = require "src/Language/dataStructure.php";

        if (array_key_exists($language, get_object_vars($this->fileContent)))
            $this->messages = (object)$this->reqursiveLoad($this->messages, $this->fileContent->$language);
        else
            $this->messages = $this->arrayToObject($this->messages);
        // .json file tartalmát kinullázzuk, már nem kell.
        $this->fileContent = null;
    }

    /**
     * Tömb konvertálása standard class-ra.
     * @param array $array
     * @return stdClass
     */
    function arrayToObject(array $array) {
        $obj = new stdClass;
        foreach($array as $k => $v) {
            if(strlen($k)) {
                if(is_array($v)) {
                    $obj->{$k} = $this->arrayToObject($v);
                } else {
                    $obj->{$k} = $v;
                }
            }
        }
        return $obj;
    }

    /**
     * Tömb értékeinek módosítása a választott nyelv alapján,
     * és konvertálása class-ra.
     * @param array $own
     * @param object $getted
     * @return array
     */
    public function reqursiveLoad(array &$own, ?object $getted)
    {
        foreach ($own as $key => $value) {
            // Mindenképpen be kell járni az egész tömböt, hogy minden altömböt objektummá
            // alakítson a függvény viszatérésénél.
            // Egyszerűbb lenne skippelni, ha eleve objektumokból állna.
            if (is_array($value)) {
                $own[$key] = $this->reqursiveLoad($value, @$getted->$key);
            }
            elseif (isset($getted->$key))
                $own[$key] = $getted->$key;

        }
        return (object)$own;
    }


    public function __get($name)
    {
        switch ($name) {
            case "io" : return $this->messages->io;
            case "main" : return $this->messages->main;
            case "import" : return $this->messages->commands->import;
            case "update" : return $this->messages->commands->update;
            case "select" : return $this->messages->commands->select;
            case "selectAll": return $this->messages->commands->selectAll;
            case "exit" : return $this->messages->commands->exit;
            case "languageSetUp" : return $this->languageSetUp;
            case "commands": return $this->messages->commands;
            default:
                throw new InvalidArgumentException("Invalid argument: ".$name);
        }
    }
}

