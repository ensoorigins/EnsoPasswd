<?php 

interface iEntity{
    public static function exists($filters);
    
    public static function insert($attributes);
    
    public static function editWhere($filters, $newAttributes);

    public static function delete($filters);
    
    public static function getWhere($filters, $attributes = null, $range = null);
    
    public static function getAll($attributes = null, $range = null);
}
