<?php
namespace App\Core;

// abstract signifie que la classe ne peut être instancier
abstract class Entity {

    /**
     * @param array $data Donées à sauvegarder 
     */
    public function __construct(array $data = [])
    { 
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate l'entité instancier
     *
     * @param array $data
     * @return void
     */
    protected function hydrate(array $data)
    {
        foreach ($data as $attr => $val) {
            $method = "set".ucfirst($attr);
            if (is_callable([$this, $method])) {
                $this->$method($val);
            }
        }
    }

    /**
     * Permet de récupérer les attributs d'un objet Entity sous forme de tableau
     *
     * @return array
     */
    public function iterate(): array
    {
        $propertys = [];
        foreach ($this as $attr => $value) {
            if (!empty($value)) {
                $propertys[$attr] = $value;
            }
        }
        return $propertys;
    }

    /**
     * Permet d'appeler une méthode depuis la vue avec un simple : $post->title; / au lieu de : $post->getTitle(); 
     *
     * @param string $key
     * @return void
     */
    public function __get($key)
    {
        $method = "get".ucfirst($key);
        $this->$key = $this->$method;
        return $this->$key;
    }

    /**
     * Vérifie si l'instance est nouvelle
     *
     * @return boolean
     */
    public function isNew(): bool
    {
        return empty($this->id);
    }

}