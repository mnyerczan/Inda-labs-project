<?php


return [
    /*
    * A project neve
    */    
    "title" => "Homework",

    /**
     * egyéb
     * 
     * 
     */   
    "main" => [
        "describe" => "Please choose one of the following commands: ",
        "help" => "Bad command! Maybe that's what you meant: ",
        "wrong" => "Command does not exist: ",
        "remainingAttempts" => "Number of attempts remaining:"
    ],

    /**
     * A irás/olvasás műveletek feedbackjei
     * 
     */
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

    /**
     * Parancsok
     * és felhasználói dialóguok.
     */
    "commands" => [
        /**
         * Adatbázisba insertálás és üzenetei
         */
        "import" => [
            "command" => "import",
            "message" => " import\t\tYou can load a journalist from a json format file",
            "get" => [
                "path" => "Please provide the path or name of the file to be accessed if it is in this folder:",
                "wrong" => "Invalid route: "
            ]
        ],
        /**
         * Adatbázis módosítás
         */
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
        /**
         * Egy újságíró lekérdezése.
         */
        "select" => [
            "command" => "select",
            "message" => " select\t\tYou can list the details of a journalist based on your ID",
            "get" => [
                "id" => "Please enter the ID of the journalist you are looking for.",
                "wrong" => "Please enter a number! Number of attempts remaining: "
            ]
        ],
        /**
         * Csoportra szűkíthető lekérdezés.
         */        
        "selectAll" => [
            "command" => "select-all",
            "message" => " select-all\tYou can write a group of journalists to a file.\n\t\t\t- If you do not enter a group name, all journalists will be listed.",
            "get" => [
                "group" => "Please enter a group to filter. If you just press an enter, the filter will list all journalists",
            ],
        ],
        /**
         * Kilépés a programból.
         */
        "exit" => [
            "command" => "exit",
            "message" => " exit\t\tExit from application.",
        ]
    ]
];