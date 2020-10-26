<?php


namespace App;


use PDO;
use PDOStatement;
use PDOException;
use Exception;
use LengthException;

class JsonJournalistMigrationHandler
{
    private array   $journalists = [];
    private int     $counter     = 0; 
    private PDO     $pdo;   

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Új újságíró hozzáadása
     * 
     * @return int Újságírók száma
     * 
     */
    public function addJournalist(Journalist $journalist): int
    {

        $this->journalists[$this->counter++] = $journalist;

        return $this->counter;
    }

    /**
     * Újságíró importálása adatbázisból.
     * 
     * @param int $id Az újságíró azonosítója
     * 
     * @return Journalist     
     * 
     * @throws Exception
     *      
     */
    public function importById(int $id): Journalist
    {
        $sql = "SELECT * FROM `Journalist` WHERE `id` = :id";
        $smt = $this->pdo->prepare($sql);
        $smt->bindValue(":id", $id);  

        return $this->import($smt);
    }

    /**
     * Újságíró importálása adatbázisból. 
     * 
     * @param string $alias A keresett újságíró álnave
     * 
     * @return Journalist     
     * 
     * @throws Exception
     *      
     */
    public function importByAlias(string $alias): Journalist
    {
        $sql = "SELECT * FROM `Journalist` WHERE `alias` LIKE :alias";
        $smt = $this->pdo->prepare($sql);
        $smt->bindParam(":alias", $alias);        

        return $this->import($smt);
    }

    /** 
     * Helper függvény. Ha nincs meg a keresett újságíró, dob
     * egy kivételt.
     * 
     * @param PDOStatement $smt
     * 
     * @return Journalist     
     * 
     * @throws  PDOException, LengthException
     *      
     */
    private function import(PDOStatement $smt): Journalist
    {
        $smt->execute();
        $journalist = $smt->fetch(PDO::FETCH_OBJ);

        if (!$journalist)
            throw new LengthException();

        if ($smt->errorInfo()[0] != "00000")
            throw new PDOException($smt->errorInfo()[2]);

        return new Journalist(
            $journalist->name,
            $journalist->alias,
            $journalist->group,
            $journalist->id
        );
    }

    /**
     * Újságírók importálása adatbázisból.
     * 
     * @param string $group
     * @return array with Journalist objets
     * 
     * @throws PDOException
     *      
     */
    public function importAll(?string $group = null): array
    {
        $returningArray = [];
        $sql = "SELECT * FROM `Journalist` WHERE `group` LIKE :group";
        $smt = $this->pdo->prepare($sql);
        $smt->bindValue(":group", $group);
            
        if (!$smt->execute())        
            throw new PDOException($smt->errorInfo()[2]);        

        $result = $smt->fetchAll(PDO::FETCH_OBJ);
    
        foreach ($result as $journalist) 
        {
            array_push($returningArray, new Journalist(
                $journalist->name,
                $journalist->alias,
                $journalist->group,
                $journalist->id
            ));
        }
        return $returningArray;
    }

    /**
     * Újságíró(k) exportálása adatbázisba.
     * 
     * @throws PDOException  
     * 
     */
    public function export()
    {
        $params = $this->createInsertStatement();
        $smt    = $this->pdo->prepare($params->sql);
        
        if (!$smt->execute($params->binds))
            throw new PDOException($smt->errorInfo()[2]);

        $this->pour();
    }

    /**
     * Újságíró módosítása
     * 
     * @throws PDOException
     * 
     */
    public function update(string $alias)
    {
        $sql = "UPDATE `Journalist` SET `name` = :name, `alias` = :alias, `group` = :group WHERE `alias` = :oldAlias";
        $smt = $this->pdo->prepare($sql);

        $binds = [
            ":oldAlias" => $alias,
            ":name"  => $this->journalists[0]->name,
            ":alias" => $this->journalists[0]->alias,
            ":group" => $this->journalists[0]->group
        ];
     
        $smt->execute($binds);
        $this->pour();        
    }

    /**
     * Az objektum belső állapotának 
     * alaphelyzetbe állítása exportálás után.
     * 
     * 
     */
    private function pour()
    {

        $this->journalists  = [];
        $this->counter      = 0;
    }

    /**
     * Előállítja az sql utasítást és a hozzá tartozó paramétereket.
     * 
     * @return StdObject
     * 
     */
    private function createInsertStatement(): object
    {   
        $binds  = [];  
        $sql    = "";   

        for($i = 0; $i < $this->counter; $i++)
        {
            $sql.="INSERT INTO `Journalist`(`name`,`alias`,`group`) VALUES (:name_{$i}, :alias_{$i} ,:group_{$i});";

            $binds[":name_{$i}"] = $this->journalists[$i]->name;
            $binds[":alias_{$i}"] = $this->journalists[$i]->alias;
            $binds[":group_{$i}"] = $this->journalists[$i]->group;
        }        

        return (object)[
            "sql"   => $sql,
            "binds" => $binds
        ];
    }
}