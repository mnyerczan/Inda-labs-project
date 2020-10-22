<?php



class BashHandler
{


    private $commands = [
        "insert",
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
     * Segéd függvények a kiiratásokhoz.
     * 
     */

    public function prompt(){           echo "\e[1;32m$ \e[0m";}
    public function errorMsg($msg){     echo "\e[1;31m{$msg}\e[0m\n";}
    public function msg($msg){          echo "{$msg}\n";}
    public function successfulMsg($msg){echo "\e[42;30m{$msg}\e[0m\n";}
    





    /**
     * Parancs ellenőrzése és esetleges segítség nyújtása
     * 
     * @param string $cmd 
     * 
     * @return true|false
     * 
     */

    private function testCmd(string $cmd): bool
    {


        if (in_array($cmd, $this->commands))        
            return true;                       

    
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
        
        
        return false;
    }




    /**
     * 
     * Az insert commandhoz tartozó elérési út bekérése és vaidálása. A felhasználónak 3 lehetősége van,
     * amit meg is jelenítünk neki.
     * 
     * @return string|false
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


        return false;
    }
        

    /**
     * Azonosító bekérése a felhasználótól.
     * 
     * @return int|false
     */

    private function getId()
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
        
        return false;
    }



    /**
     * Csoport nevének bekérése. 
     * 
     * @return string 
     */

    private function getGroup()
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

            $this->msg("Kérlek válassz egyet az alábbi parancsok közül:\n[insert, update, select, select-all, exit]");

            $this->prompt();


            // Lehet, hogy a felhasználó ütött a bemeneti karakterláncba szóközt.
            // Ezért megpróbáljuk felbontani ezek mentén és csak az első elemet vizsgáljuk.
            $cmd = explode(" ",strtolower(readline()))[0];


            // Megvizsgáljuk, hogy a command valid-e.
            $cmdIsCorrect = $this->testCmd($cmd);
            

            if ($cmdIsCorrect)
            {   

                switch($cmd)
                {

                    case "select": 
                        {

                            return (object)[
                                "cmd" => "select",
                                "id"=> $this->getId()
                            ];

                        } break;

                    
                    case "insert": 
                        {

                            // Ha a kapott útvonal invalid, 
                            if( !$path = $this->getPath())
                                $cmdIsCorrect = false; 
                            else
                                return (object)[
                                    "cmd" => "insert",
                                    "path"=> $path
                                ];

                        } break;


                    case "update": 
                        {

                            // Bekérjük a az újságíró jelenlegi alias-át
                            $this->msg("Kérem az újságíró jelenlegi álnevét!");

                            $this->prompt();


                            $alias = readline(); 


                            // Bekérjük az újságíró új adatait
                            // név
                            $this->msg("Kérem az újságíró új nevét!");

                            $this->prompt();


                            $newName = readline();
                            
                            
                            // alias
                            $this->msg("Kérem az újságíró új álnevét!");

                            $this->prompt();


                            $newAlias = readline();   


                            // csoport
                            $this->msg("Kérem az újságíró új csoportját!");

                            $this->prompt();

                            
                            $newGroup = readline();   
                            

                            return (object)[
                                "cmd"       => "update",
                                "alias"     => $alias,
                                "newName"   => $newName,
                                "newAlias"  => $newAlias,
                                "newGroup"  => $newGroup,
                            ];  

                        } break;


                    case "select-all": return (object)[
                        "cmd"   => "select-all",
                        "group" => $this->getGroup()
                    ]; 


                    case "exit": 
                        return (object)[
                            "cmd" => $cmd
                        ];
                }                          
            }
        }        
    }
}
