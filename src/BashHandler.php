<?php

namespace App\Console;

use InvalidArgumentException;

class BashHandler
{
    private static ?BashHandler $instance = null;
    private object $languageContainer;

    /**
     * Egyetlen példány lehet az osztályból a program életciklusa során.
     * 
     * @return BashHandler
     * 
     */
    public static function getInstance($langguageFilePath): BashHandler
    {
        if (self::$instance) 
            return self::$instance;        

        return self::$instance = new BashHandler($langguageFilePath);
    }

    public function __get($name)
    {
        if ($name == "languageContainer")
            return $this->languageContainer;
    }

    /**
     * BashHandler constructor.
     * @param $langguageFilePath
     *
     */
    private function __construct($langguageFilePath)
    {
        $this->languageContainer = new LanguageContener($langguageFilePath);

        $this->msg($this->languageContainer->languageSetUp["getLanguage"]);

        foreach ($this->languageContainer->languages as $language)
            $this->msg(" - ".$language);

        $this->prompt();
        $this->languageContainer->setUp(readline());
        // $this->languageContainer->setUp();

    }

    /**
     * Parancs ellenőrzése és esetleges segítség nyújtása
     * 
     * @param string $cmd 
     * @throws InvalidArgumentException
     * 
     */
    private function testCmd(string $cmd): void
    {
        foreach($this->languageContainer->commands as $type) {
            if ($cmd == $type->command) return;
        }

        if (preg_match("%{$cmd}%", $this->languageContainer->import->command))
            $this->errorMsg(
                $this->languageContainer->main->help."\"".
                $this->languageContainer->import->command."\""
            );
        elseif (preg_match("%{$cmd}%", $this->languageContainer->update->command))
            $this->errorMsg(
                $this->languageContainer->main->help."\"".
                $this->languageContainer->import->command."\""
            );
        elseif (preg_match("%{$cmd}%", $this->languageContainer->select->command))
            $this->errorMsg(
                $this->languageContainer->main->help."\"".
                $this->languageContainer->select->command."\"".
                $this->languageContainer->selectAll->command."\""
            );
        elseif (preg_match("%{$cmd}%", "-all"))
            $this->errorMsg(
                $this->languageContainer->main->help."\", \"".
                $this->languageContainer->selectAll->command."\""
            );
        elseif (preg_match("%{$cmd}%", $this->languageContainer->exit->command))
            $this->errorMsg(
                $this->languageContainer->main->help."\"".
                $this->languageContainer->exit->command."\""
            );
        else 
            $this->errorMsg($this->languageContainer->main->wrong."\"".$cmd."\"");

        throw new InvalidArgumentException();
    }

    /**
     * 
     * Az import commandhoz tartozó elérési út bekérése és vaidálása. A felhasználónak 3 lehetősége van,
     * amit meg is jelenítünk neki.
     * 
     * @return string|false
     * @throws InvalidArgumentException
     * 
     */
    private function getPath(): string
    {
        // Bekérjük a kért útvonalat
        $this->msg($this->languageContainer->import->get->path.__DIR__);

        for ($i=0; $i < 3; $i++) {
            $this->prompt();
            $path = strtolower(readline());            

            // Majd leteszteljük               
            if (is_file($path)) return $path; 

            $this->errorMsg($this->languageContainer->main->remainingAttempts.(2 - $i));
        }
        throw new InvalidArgumentException();
    }

    /**
     * Azonosító bekérése a felhasználótól.
     * 
     * @return int|false        
     * @throws InvalidArgumentException
     */
    private function getId(): int
    {
        for ($i=0; $i < 3; $i++) {
            $this->msg($this->languageContainer->select->get->id);
            $this->prompt();

            $id = readline();

            if (!is_numeric($id))
                $this->errorMsg($this->languageContainer->select->get->wrong.(2 - $i));
            else 
                return $id;                
        }            
        throw new InvalidArgumentException();
    }

    /**
     * Csoport nevének bekérése. 
     * 
     * @return string 
     */
    private function getGroup(): string
    {                
        $this->msg($this->languageContainer->selectAll->get->group);
        $this->prompt();
        $group = readline();

        // A readline ures stringet ad vissza,nem NULL értéket, ezért
        // nem használható a Null Coalescing operátor (??)
        return  $group == "" ? "%" : $group;
    }

    /**
     * Felhasználói interakciót menedzselő függvény. Visszaadja a kapott paramétereket, ha azok helyesek.
     * 
     * @return object Object of cmd commands
     * 
     */
    public function read(): object
    {
        $cmdIsCorrect = false;

        while ($cmdIsCorrect === false)
        {
            $this->msg($this->languageContainer->main->describe);
            $this->msg();
            $this->msg($this->languageContainer->import->message);
            $this->msg($this->languageContainer->update->message);
            $this->msg($this->languageContainer->select->message);
            $this->msg($this->languageContainer->selectAll->message);
            $this->msg($this->languageContainer->exit->message);
            $this->msg();
            $this->prompt();

            // Lehet, hogy a felhasználó ütött a bemeneti karakterláncba szóközt.
            // Ezért megpróbáljuk felbontani ezek mentén és csak az első elemet vizsgáljuk.
            $cmd = explode(" ",strtolower(readline()))[0];
            // $cmd = "import" ;
            try {
                // Megvizsgáljuk, hogy a command valid-e.
                $this->testCmd($cmd);

                switch($cmd)
                {                                        
                    case $this->languageContainer->import->command:{
                            // Ha a kapott útvonal invalid,                             
                            return (object) [
                                    "cmd" => "import",
                                    "path"=> $this->getPath()
                                ];
                        }

                    case $this->languageContainer->update->command:{
                            $output = [];
                            $output["cmd"] = "update";

                            // Bekérjük a az újságíró jelenlegi alias-át
                            $this->msg($this->languageContainer->update->get->newAlias);
                            $this->prompt();
                            $output["oldAlias"] = readline();

                            // Bekérjük az újságíró új adatait
                            // név
                            $this->msg($this->languageContainer->update->get->name);
                            $this->prompt();
                            $output["name"] = readline();

                            // alias
                            $this->msg($this->languageContainer->update->get->alias);
                            $this->prompt();
                            $output["alias"] = readline();   

                            // csoport
                            $this->msg($this->languageContainer->update->get->group);
                            $this->prompt();                            
                            $output["group"] = readline();   
                            
                            return (object)$output;
                        }

                    case $this->languageContainer->select->command:
                            return (object) [
                                    "cmd" => "select",
                                    "id"=> $this->getId()
                            ];

                    case $this->languageContainer->selectAll->command:
                            return (object) [
                                "cmd"   => "select-all",
                                "group" => $this->getGroup()
                            ];

                    case $this->languageContainer->exit->command:
                            return (object) [
                                "cmd" => $cmd
                            ];
                }
            }
            catch (InvalidArgumentException $e){}            
        }        
    }

    /**
     * Segéd függvények a kiiratásokhoz.
     * 
     */
    public function prompt()
    {
        echo "\e[1;32m$ \e[0m";
    }

    public function errorMsg($msg)
    {
        echo "\e[1;31m{$msg}\e[0m\n";
    }

    public function msg($msg = "")
    {
        echo "{$msg}\n";
    }

    public function successfulMsg($msg)
    {
        echo "\e[42;30m{$msg}\e[0m\n";
    }


}
