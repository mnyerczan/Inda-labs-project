<?php


class JsonJournalistMigrationHandler
{

    private array   $journalists = [];
    private int     $counter     = 0;    



    /**
     * Új újságíró hozzáadása
     * 
     */

    public function addJournalist(Journalist $journalist)
    {
        $this->journalists[$this->counter++] = $journalist;

        return $this->counter;
    }



    /**
     * Újságíró importálása adatbázisból.
     * 
     * @param PDO $pdo
     * @param int $id Az újságíró azonosítója
     * 
     * @return Journalist     
     * 
     * @throws Exception
     *      
     */

    public function importById(PDO $pdo, int $id): Journalist
    {

        $sql = "SELECT * FROM `Journalist` WHERE `id` = :id";

        $smt = $pdo->prepare($sql);
        $smt->bindValue(":id", $id);  

        return $this->import($smt);
    }



    /**
     * Újságíró importálása adatbázisból. 
     * 
     * @param PDO $pdo
     * @param string $alias A keresett újságíró álnave
     * 
     * @return Journalist     
     * 
     * @throws Exception
     *      
     */

    public function importByAlias(PDO $pdo, string $alias): Journalist
    {

        $sql = "SELECT * FROM `Journalist` WHERE `alias` LIKE '-GJ-'";

        $smt = $pdo->prepare($sql);
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
     * @throws  PDOException, Exception
     *      
     */

    private function import(PDOStatement $smt): Journalist
    {
  
        $smt->execute();

        $journalist = $smt->fetch(PDO::FETCH_OBJ);

        if (!$journalist)
            throw new Exception("Nincs ilyen újságíró.");

        if ($smt->errorInfo()[0] != "00000")
            throw new Exception($smt->errorInfo()[2]);


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
     * @param PDO $pdo
     * @param string $group
     * 
     * @return array with Journalist objets
     * 
     * @throws PDOException
     *      
     */

    public function importAll(PDO $pdo, ?string $group = null): array
    {

        $returningArray = [];

        $sql = "SELECT * FROM `Journalist` WHERE `group` LIKE :group";
        $smt = $pdo->prepare($sql);
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
     * 
     * @param PDO $pdo
     * 
     * @return true ha a művelet sikeresen végre tud hajtódni.     
     * @throws PDOException
     * 
     * 
     */

    public function export(PDO $pdo)
    {
        $params = $this->createInsertStatement($pdo);
        $smt    = $pdo->prepare($params->sql);

        
        if (!$smt->execute($params->binds))
            throw new PDOException($smt->errorInfo()[2]);

    
        return true;
    }

    



    /**
     * Újságíró módosítása
     * 
     * @param PDO $pdo
     * @param Journalist $journalist
     * 
     * @return true ha a művelet sikeresen végrehajtódik
     * 
     * @throws PDOException
     * 
     */

    public function update(PDO $pdo, Journalist $journalist, string $alias)
    {
        $sql = "UPDATE `Journalist` SET `name` = :newName, `alias` = :newAlias, `group` = :newGroup WHERE `alias` = :alias";

        $smt = $pdo->prepare($sql);


        $binds = [
            ":alias" => $alias,
            ":newName"  => $journalist->name,
            ":newAlias" => $journalist->alias,
            ":newGroup" => $journalist->group
        ];


        if (!$smt->execute($binds))
            throw new PDOException($smt->errorInfo()[2]);

        return true;
        
    }







    /**
     * @return StdObject
     * 
     */

    private function createInsertStatement()
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
