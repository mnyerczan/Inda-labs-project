<?php


namespace App;


use InvalidArgumentException;


class BashHandler
{


    private $commands = [
        "import",
        "update",
        "select",
        "select-all",
        "exit"
    ];

    private static ?BashHandler $instance = null;




    /**
     * Egyetlen példány lehet az osztályból a program életciklusa során.
     * 
     * @return BashHandler
     * 
     */

    public static function getInstance(): BashHandler
    {

        if (self::$instance) 
            return self::$instance;
        

        return self::$instance = new BashHandler();
    }




    private function __construct()
    {

        $this->msg("Házi feladat");
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

        if (in_array($cmd, $this->commands)) return;                       

    
        if (preg_match("%{$cmd}%" ,$this->commands[0]))  
            $this->errorMsg("Rossz parancs! Talán erre gondoltál: \"{$this->commands[0]}\"");

        elseif (preg_match("%{$cmd}%" ,$this->commands[1]))  
            $this->errorMsg("Rossz parancs! Talán erre gondoltál: \"{$this->commands[1]}\"");

        elseif (preg_match("%{$cmd}%" ,$this->commands[2]))  
            $this->errorMsg("Rossz parancs! Talán erre gondoltál: \"{$this->commands[2]}\", \"{$this->commands[2]}\"");

        elseif (preg_match("%{$cmd}%" , "-all"))
            $this->errorMsg("Rossz parancs! Talán erre gondoltál: \"{$this->commands[3]}\"");

        else 
            $this->errorMsg("Nem létező parancs: \"$cmd\"");
        
        
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
        $this->msg("Kérem az elérendő fájl útvonalát, vagy nevét, ha ebben a mappában van: ".__DIR__);


        for ($i=0; $i < 3; $i++) 
        { 

            $this->prompt();


            $path = strtolower(readline());            


            // Majd leteszteljük               
            if (is_file($path)) return $path; 


            $this->errorMsg("Érvénytelen útvonal: \"{$path}\". Hátralévő próbálkozások száma: ".(2 - $i));

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

        for ($i=0; $i < 3; $i++) 
        { 

            $this->msg("Kérlek add meg a keresett újságíró azonosítóját!");

            $this->prompt();


            $id = readline();


            if (!is_numeric($id))
                $this->errorMsg("Kérlek számot adj meg: \"{$id}\". Hátralévő próbálkozások száma: ".(2 - $i));
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

        $this->msg("Kérlek adj meg a szűréshez egy csoportot!");        
        $this->msg("Ha csak nyomsz egy entert, a szűrés minden újságírót ki fog listázni");

        $this->prompt();
   

        $group = readline();


        // A readline ures stringet ad vissza,nem NULL értéket, ezért
        // nem használható a Null Coalescing operátor (??)
        return  $group == "" ? "%" : $group;
    }



    /**
     * Felhasználói interakciót menedzselő függvény. Visszaadja a kapott paramétereket, ha azok helyesek.
     * 
     * @return array Array of cmd commands
     * 
     */
    
    public function read(): object
    {

        $cmdIsCorrect = false;


        while ($cmdIsCorrect === false)
        {
            
            $this->msg("Kérlek válassz egyet az alábbi parancsok közül:");
            $this->msg();
            $this->msg(" import\t\tBe tudsz tölteni json formátumú fájlból egy újságírót,");
            $this->msg(" update\t\tAz újságíró adatait tudod módosítani álnevével való azonosításával,");
            $this->msg(" select\t\tEgy újságíró adatait tudod kiíratni az azonosítója alapján,");
            $this->msg(" select-all\tÚjságírók csoportját tudod kiíratni fájlba.");
            $this->msg("\t\t- Ha nem adsz meg csoportnevet, az összes újságíró kiírásra kerül");
            $this->msg(" exit\t\tKilépés a programból.");
            $this->msg();

            $this->prompt();


            // Lehet, hogy a felhasználó ütött a bemeneti karakterláncba szóközt.
            // Ezért megpróbáljuk felbontani ezek mentén és csak az első elemet vizsgáljuk.
            $cmd = explode(" ",strtolower(readline()))[0];


            try
            {

                // Megvizsgáljuk, hogy a command valid-e.
                $this->testCmd($cmd);


                switch($cmd)
                {
                                        
                    case "import": 
                        {

                            // Ha a kapott útvonal invalid,                             
                            return (object) [
                                    "cmd" => "import",
                                    "path"=> $this->getPath()
                                ];

                        }


                    case "update": 
                        {

                            $output = [];

                            $output["cmd"] = "update";


                            // Bekérjük a az újságíró jelenlegi alias-át
                            $this->msg("Kérem az újságíró jelenlegi álnevét!");

                            $this->prompt();


                            $output["alias"] = readline(); 


                            // Bekérjük az újságíró új adatait
                            // név
                            $this->msg("Kérem az újságíró új nevét!");

                            $this->prompt();


                            $output["newName"] = readline();
                            
                            
                            // alias
                            $this->msg("Kérem az újságíró új álnevét!");

                            $this->prompt();


                            $output["newAlias"] = readline();   


                            // csoport
                            $this->msg("Kérem az újságíró új csoportját!");

                            $this->prompt();

                            
                            $output["newGroup"] = readline();   
                            

                            return (object)$output;

                        }

                    
                    case "select":  
                            return (object) [
                                    "cmd" => "select",
                                    "id"=> $this->getId()
                            ];


                    case "select-all": 
                            return (object) [
                                "cmd"   => "select-all",
                                "group" => $this->getGroup()
                            ]; 


                    case "exit": 
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

    public function prompt() {echo "\e[1;32m$ \e[0m";}



    public function errorMsg($msg) {echo "\e[1;31m{$msg}\e[0m\n";}
    


    public function msg($msg = "") {echo "{$msg}\n";}
    

    
    public function successfulMsg($msg) {echo "\e[42;30m{$msg}\e[0m\n";}


}
