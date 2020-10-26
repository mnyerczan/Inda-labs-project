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
    private $messages = [
        "title" => "Homework",
        "main" => [
            "describe" => "Please choose one of the following commands: ",
            "help" => "Bad command! Maybe that's what you meant: ",
            "wrong" => "Command does not exist: ",
            "remainingAttempts" => "Number of attempts remaining:"
        ],
        "io" => [
            "file" => [
                "writeDenied" => "Access denied to write file: ",
                "writeSuccess" => "Printed to the following file:",
                "wrongMimeType" => "You can only specify a file in Json format!"
            ],
            "database" => [
                "modifySuccess" => "Change successful!",
                "importSuccess" => "Upload successful!" ,
                "userNotFound" => "There is no such journalist in the system"
            ]
        ],
        "commands" => [
            "import" => [
                "command" => "import",
                "message" => " import\t\tYou can load a journalist from a json format file",
                "get" => [
                    "path" => "Please provide the path or name of the file to be accessed if it is in this folder:",
                    "wrong" => "Invalid route: "
                ]
            ],
            "update" => [
                "command" => "update",
                "message" => " update\t\tYou can change the journalist's information by identifying it with your pseudonym",
                "get" => [
                    "newAlias" => "Please give the current alias of journalist!",
                    "name" => "Please give the new name of journalist!",
                    "alias" => "Please give the new alias of journalist!",
                    "group" => "Please give the new group of journalist"
                ]
            ],
            "select" => [
                "command" => "select",
                "message" => " select\t\tYou can list the details of a journalist based on your ID",
                "get" => [
                    "id" => "Please enter the ID of the journalist you are looking for.",
                    "wrong" => "Please enter a number! Number of attempts remaining: "
                ]
            ],
            "selectAll" => [
                "command" => "select-all",
                "message" => " select-all\tYou can write a group of journalists to a file.\n\t\t\t- If you do not enter a group name, all journalists will be listed.",
                "get" => [
                    "group" => "Please enter a group to filter. If you just press an enter, the filter will list all journalists",
                ],
            ],
            "exit" => [
                "command" => "exit",
                "message" => " exit\t\tExit from application.",
            ]
        ]
    ];

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
        if (array_key_exists($language, $this->fileContent))
            $this->messages = (object)$this->reqursiveLoad($this->messages, $this->fileContent->$language);
        else
            $this->messages = $this->arrayToObject($this->messages);

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
    public function reqursiveLoad(array &$own, object $getted)
    {
        foreach ($own as $key => $value) {
            if (array_key_exists($key, get_object_vars($getted))) {
                if (is_array($value))
                    $own[$key] = $this->reqursiveLoad($value, $getted->$key);
                else
                    $own[$key] = $getted->$key;
            }
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

