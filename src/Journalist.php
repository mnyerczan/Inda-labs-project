<?php

/*
 * "Modell" osztály az újságírók tárolásáhot.
 * 
 * 
 * 
 */

namespace App;

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

    public function toJson()
    {
        
        return "{\"id\" : \"{$this->id}\",\"name\" : \"{$this->name}\",\"alias\" : \"{$this->alias}\",\"group\" : \"{$this->group}\"}";
    }


}