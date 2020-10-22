<?php

/*
 * Modell osztály az újságírókhoz.
 * 
 * 
 * 
 */



class Journalist
{
    // Felvételnél a user nem szokott azonosítót adni, ezért
    // deklarálom nullable int-nek.    
    private ?int $id;

    private string 
                $name,
                $alias,
                $group;



    public function __construct($name, $alias, $group, $id = null)
    {   

        $this->id       = $id;
        $this->name     = $name;
        $this->alias    = $alias;
        $this->group    = $group;
    }    


    /**
     * Ha a kért property nem létezik dob egy UnexpectedValueException -t.
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



    public function toJson()
    {
        
        return "{\"id\" : \"{$this->id}\",\"name\" : \"{$this->name}\",\"alias\" : \"{$this->alias}\",\"group\" : \"{$this->group}\"}";
    }


}