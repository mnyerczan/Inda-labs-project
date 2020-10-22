<?php



require "Journalist.php";
require "JsonJournalistMigrationHandler.php";
require "BashHandler.php";


class Main
{

    
    private object $conn;
    private JsonJournalistMigrationHandler $jjmh;
    private BashHandler $bash;
    private PDO $pdo;



    public function __construct()
    {
        $this->conn = json_decode(file_get_contents("connect.json"));
        $this->jjmh = new JsonJournalistMigrationHandler();

    }


    public function index()
    {                                
       
        $this->bash = bashHandler::getInstance();

        $this->pdo = new PDO(
            "{$this->conn->sdn}:host={$this->conn->host};dbname={$this->conn->dbname};charset=utf8;",
            $this->conn->user, 
            $this->conn->password
        );
        

        while ($this->bash)
        {
            $input = $this->bash->read();
   

            switch($input->cmd)
            {
                case "insert":      $this->execInsert($input); break;
                case "update":      $this->execUpdate($input); break;
                case "select":      $this->execSelect($input); break;
                case "select-all":  $this->execSelectAll($input); break;
                case "exit":        $this->bash = null; break;
            }                            
  
        }

    }



    /**
     * Insert művelet végrehajtása külső Json fájlból. 
     * A fájl csak egyetlen újságíró objektumot írhat le.
     * 
     * 
     */

    private function execInsert($input)
    {
        $source = json_decode(file_get_contents($input->path));


        if (is_array($source))
        {
            foreach($source as $journalist)
            {
                $this->jjmh->addJournalist(
                    new Journalist(
                        $journalist->name,
                        $journalist->alias,
                        $journalist->group,
                    )
                );
            }
        }
        else
        {
      
            $this->jjmh->addJournalist(
                new Journalist(
                    $source->name,
                    $source->alias,
                    $source->group,                
                )
            );
        }

        try
        {
            $this->jjmh->export($this->pdo);
            $this->bash->successfulMsg("A feltöltés sikeres!");
            
            return true;
        }
        catch (PDOException $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }                            
        
    }




    private function execUpdate($input)
    {
      

        try
        {
            $this->jjmh->importByAlias($this->pdo, $input->alias);

            $this->jjmh->update(
                $this->pdo, 
                new Journalist(                    
                    $input->newName, 
                    $input->newAlias, 
                    $input->newGroup
                ),
                $input->alias
            );

            $this->bash->successfulMsg("A Módosítás sikeres!");

            return true;
        }        
        catch (PDOException $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
        catch (Exception $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
    
    }




    private function execSelect($input)
    {
        if(!is_dir("json"))
            mkdir("json");

        try
        {
            $journalist = $this->jjmh->importById($this->pdo, $input->id);

            $rand = random_int(1000000, 10000000);


            if(file_put_contents("json/".$rand.".json", $journalist->toJson()))
                $this->bash->successfulMsg("Kiírva a  ".__DIR__."/json/{$rand}.json fájlba.");
            else 
                throw new Exception("Jogosultság megtagadva az eredmény kiírásához");
        }
        catch (PDOException $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
        catch (Exception $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
    }






    private function execSelectAll($input)
    {
        if(!is_dir("json"))
            mkdir("json");

        try 
        {
            $journalists = $this->jjmh->importAll($this->pdo, $input->group);


            
            $outputStr = "[";
            for($i = 0; $i < count($journalists);)
            {               
                $outputStr.= $journalists[$i]->toJson();

                // Amíg nem érünk a tömm utolsó eleméhez, tegyük ki a vesszőt.
                if ($i++ < count($journalists) -1) 
                    $outputStr.=",";
                    
            }
            $outputStr .= "]";


            // Randomszám a fájl prefixeléséhez.
            $rand = random_int(1000000, 10000000);


            if(file_put_contents("json/".$rand.".json", $outputStr))
                $this->bash->successfulMsg("Kiírva a  ".__DIR__."/json/{$rand}.json fájlba.");
            else 
                throw new Exception("Jogosultság megtagadva az eredmény kiírásához");

        } 
        catch (PDOException $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
        catch (Exception $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
    }
}



(new Main())->index();
