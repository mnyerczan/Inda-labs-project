<?php


namespace App;


use PDO;
use PDOException;
use Exception;
use App\BashHandler;
use App\Journalist;
use App\JsonJournalistMigrationHandler as Migrate;
use DomainException;
use LengthException;
use RuntimeException;

class Controller
{

    

    private Migrate      $jjmh;
    private ?BashHandler $bash;
    private PDO          $pdo;
    private object       $input;



    public function __construct()
    {
                     
        $this->jjmh = new Migrate();
        $this->bash = bashHandler::getInstance();


        $conn = json_decode(file_get_contents(".connect.json"));   

        $this->pdo = new PDO(
            "{$conn->sdn}:host={$conn->host};dbname={$conn->dbname};charset=utf8;",
            $conn->user, 
            $conn->password
        );
    }



    public function index(): void
    {                                               

        while ($this->bash)
        {

            $this->input = $this->bash->read();
   

            switch($this->input->cmd)
            {

                case "insert":      $this->execInsert();      break;
                case "update":      $this->execUpdate();      break;
                case "select":      $this->execSelect();      break;
                case "select-all":  $this->execSelectAll();   break;
                case "exit":        $this->bash = null;             break;
            }                            
  
        }

    }



    /**
     * Insert művelet végrehajtása külső Json fájlból. 
     * A fájl csak egyetlen újságíró objektumot írhat le.     
     * 
     * 
     * @return void
     */

    private function execInsert(): void
    {
        
        try
        {            

            if (!$source = json_decode(file_get_contents($this->input->path)))
                throw new RuntimeException("Csak Json formátumú fájlt adhatsz meg!");


            if (is_array($source))
            {                

                foreach($source as $journalist)
                {

                    $this->journalistValidator($journalist);


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

                $this->journalistValidator($source);

        
                $this->jjmh->addJournalist(
                    new Journalist(
                        $source->name,
                        $source->alias,
                        $source->group,                
                    )
                );
            }


            $this->jjmh->export($this->pdo);
            $this->bash->successfulMsg("A feltöltés sikeres!");
   
        }

        catch (PDOException $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }           
        
        catch (RuntimeException $r)
        {

            $this->bash->errorMsg("Hiba: ".$r->getMessage());
        }        
        
    }




    /**
     * Megvizsgáljuk, hogy a beérkező adatszerkezet újságíró kompatibilis-e.
     * 
     * 
     * @throws RuntimeException
     * 
     */

    private function journalistValidator($data)
    {

        if (gettype($data) == "object")
        {

            if (!(isset($data->name) && isset($data->name) && isset($data->name)))
                throw new RuntimeException("Az adatszerkezet nem megfelelő formátumú.");
            

            return;
        }
        
        
        if (gettype($data) == "array")
        {

            if (!(isset($data["name"]) && isset($data["name"]) && isset($data["name"])))            
                throw new RuntimeException("Az adatszerkezet nem megfelelő formátumú.");
            
            
            return;
        }            

                
        throw new RuntimeException("Az adatszerkezet nem megfelelő formátumú.");

    }




    /**
     * A módosítást irányító eljárás.
     * 
     * 
     * @return void
     */

    private function execUpdate(): void
    {      

        try
        {

            $this->jjmh->importByAlias($this->pdo, $this->input->alias);


            $this->jjmh->update(
                $this->pdo, 
                new Journalist(                    
                    $this->input->newName, 
                    $this->input->newAlias, 
                    $this->input->newGroup
                ),
                $this->input->alias
            );


            $this->bash->successfulMsg("A Módosítás sikeres!");

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



    /**
     * Az egyes újságírók lekérdezését irányító eljárás.
     * 
     * 
     * @return void
     */

    private function execSelect(): void
    {

        if(!is_dir("json")) mkdir("json");


        try
        {

            $journalist = $this->jjmh->importById($this->pdo, $this->input->id);

            $prefix = date("ymdHis", time());


            // Ha a PHP Warning funkció nincs kikapcsolva az .ini-ben, kiírja a
            // hibát és a verem hivásokat az elsődleges kimenetre...
            //
            // Program.php(7)
            //
            if(file_put_contents("json/".$prefix.".json", $journalist->toJson()))
                $this->bash->successfulMsg("Kiírva a  ".__DIR__."/json/{$prefix}.json fájlba.");

            else 
                throw new Exception("Jogosultság megtagadva: ".(get_current_user()));

        }

        catch (PDOException $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }

        catch (LengthException $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }

        catch (Exception $e)
        {
            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
    }




    /**
     * Az összes, vagy csoportos újságíri lekérdezéseket irányító eljárás.
     * 
     * @param object $this->input
     * 
     * @return void
     */

    private function execSelectAll(): void
    {

        if(!is_dir("json")) mkdir("json");


        try 
        {

            $journalists = $this->jjmh->importAll($this->pdo, $this->input->group);

            
            $outputStr = Journalist::assocToJson($journalists);


            // Refix a fájl prefixeléséhez.
            $prefix = date("ymdHis", time());


            // Ha a PHP Warning funkció nincs kikapcsolva az .ini-ben, kiírja a
            // hibát és a verem hivásokat az elsődleges kimenetre...
            //
            // Program.php(7)
            //
            if( file_put_contents("json/".$prefix.".json", $outputStr))
                $this->bash->successfulMsg("Kiírva a  ".__DIR__."/json/{$prefix}.json fájlba.");
                
            else 
                throw new Exception("Jogosultság megtagadva: ".(get_current_user()));
                
        } 

        catch (PDOException $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }

        catch (LengthException $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }

        catch (Exception $e)
        {

            $this->bash->errorMsg("Hiba: ".$e->getMessage());
        }
    }
    
}