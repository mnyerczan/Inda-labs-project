<?php

/*
 * "Modell" osztály az újságírók tárolásáhot.
 * 
 * 
 * 
 */

namespace App;


use InvalidArgumentException;
use UnexpectedValueException;


class Journalist
{

    // Felvételnél a usernek nincsen azonosítója, ezért
    // deklarálom nullable int-nek.    
    private ?int $id;

    private string 
                $name,
                $alias,
                $group;


    /**
     *  
     */

    public function __construct(string $name, string $alias, string $group, int $id = null)
    {   

        $this->id       = $id;
        $this->name     = $name;
        $this->alias    = $alias;
        $this->group    = $group;
    }    



    /**
     * Ha a kért property nem létezik dob egy UnexpectedValueException-t.
     * 
     * @throws UnexpectedValueException
     */

    public function __get($name)
    {

        switch($name)
        {

            case "id":      return $this->id; break;
            case "name":    return $this->name; break;
            case "alias":   return $this->alias; break;
            case "group":   return $this->group; break; 
            default:
                throw new UnexpectedValueException("Nincs ilyen property");
        }
    }




    /**
     * Json formátumban adja vissza az belső objetum állapotát.
     * 
     * @return string Json format
     */

    public function toJson(): string
    {
        
        return "{\"id\":\"{$this->id}\",\"name\":\"{$this->name}\",\"alias\":\"{$this->alias}\",\"group\":\"{$this->group}\"}";
    }



    /**
     * Staticus függvény Újságírókat tartalmazó tömb
     * Json formátummá konvertálásához.
     * 
     * @param array   $journalists Array of Journalists
     * @return string
     * @throws InvalidArgumentException
     */

     public static function assocToJson(array $journalists): string
     {            

        $json = "[";


        for($i = 0; $i < count($journalists);)
        {     

            if (get_class($journalists[$i]) !== "App\Journalist")
                throw new InvalidArgumentException("You can only convert an array of journalists!");

            $json.= $journalists[$i]->toJson();


            // Amíg nem érünk a tömm utolsó eleméhez, kitesszük a vesszőt az
            // objektumok közé.
            if ($i++ < count($journalists) -1) 
                $json.=",";
                
        }


        $json .= "]";


        return $json;
    }
}