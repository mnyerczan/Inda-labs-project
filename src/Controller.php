<?php


namespace App;


use PDO;
use PDOException;
use Exception;
use App\BashHandler;
use App\Journalist;
use App\JsonJournalistMigrationHandler as Migrate;
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


        $dbConnect = ".dbConnect.json";


        $conn = json_decode(file_get_contents($dbConnect));   


        try
        {

            // A PDO PDOException-t dob, ha a hibás paraméterek miatt nem képes megnyitni a 
            // kacsolatot.
            $this->pdo = new PDO(
                "{$conn->sdn}:host={$conn->host};dbname={$conn->dbname};charset=utf8;",
                $conn->user, 
                $conn->password
            );

            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        catch (PDOException $e)
        {

            $this->bash->errorMsg($e->getMessage()."  =>  \"{$dbConnect}\"");
            

            if (!is_file($dbConnect))
            {

                file_put_contents(
                    $dbConnect, 
                    "{\n\t\"user\":\"\",\n\t\"password\":\"\",\n\t\"host\":\"\",\n\t\"sdn\":\"\",\n\t\"dbname\":\"\"\n}"
                );
            }
                        

            $this->bash = null;

        }

    }



    public function index(): void
    {                                               

        while ($this->bash)
        {

            $this->input = $this->bash->read();
   

            switch($this->input->cmd)
            {

                case "import":      $this->execImport();    break;
                case "update":      $this->execUpdate();    break;
                case "select":      $this->execSelect();    break;
                case "select-all":  $this->execSelectAll(); break;
                case "exit":        $this->bash = null;     break;
            }                            
  
        }

    }



    /**
     * Import művelet végrehajtása külső Json fájlból. 
     * A fájl csak egyetlen újságíró objektumot írhat le.     
     * 
     * 
     * @return void
     */

    private function execImport(): void
    {
        
        try
        {            

            if (!$source = json_decode(file_get_contents($this->input->path)))
                throw new RuntimeException("Csak Json formátumú fájlt adhatsz meg!");


            // Beletesszük a kapott adatokat a jjmh objektumba
            $this->pushDataToJjmh($source);


            // Újságírók exportálása az adatbázisba
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
     * A módosítást irányító eljárás.
     * 
     * 
     * @return void
     */

    private function execUpdate(): void
    {      

        try
        {

            // Újságíró lekérése álnév alapján.
            $this->jjmh->importByAlias($this->pdo, $this->input->oldAlias);


            // Beletesszük a kapott adatokat a jjmh objektumba
            $this->pushDataToJjmh($this->input);


            $this->jjmh->update($this->pdo, $this->input->oldAlias);


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

            // Újságíró lekérése azonosító alapján.
            $journalist = $this->jjmh->importById($this->pdo, $this->input->id);


            // prefix az új fájlnak. év, hó, nap, óra, perc, másodperc
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

            // Összes újságíró lekérése csoport alapján.
            $journalists = $this->jjmh->importAll($this->pdo, $this->input->group);

            
            // A kapott tömböt json formátummá konvertálja 
            $outputStr = Journalist::assocToJson($journalists);


            // prefix az új fájlnak. év, hó, nap, óra, perc, másodperc
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

    

    /**
     * Megvizsgáljuk, hogy a beérkező adatszerkezet újságíró kompatibilis-e.
     * 
     * @param object $data
     * @throws RuntimeException
     * 
     */

    private function journalistValidator(object $data)
    {

        if (!(isset($data->name) && isset($data->name) && isset($data->name)))
                throw new RuntimeException("Az adatszerkezet nem megfelelő formátumú.");
                    
    }



    /**
     * Absztrakcó a kapott adatszerkezet jjmh felé átadáshoz.
     * 
     * @param mixed $data
     * 
     */

     private function pushDataToJjmh($data)
     {

        if (is_array($data))
        {                

            foreach($data as $journalist)
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

            $this->journalistValidator($data);

    
            $this->jjmh->addJournalist(
                new Journalist(
                    $data->name,
                    $data->alias,
                    $data->group,                
                )
            );
        }        
     }
}