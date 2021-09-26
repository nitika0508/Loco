<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TransactionMapping extends Model
{
    protected $table = "parent_child_transactions_map";
    public $timestamps = false;

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function getChildId()
    {
        return $this->child_id;
    }

    public static function updateMapping($parentId, $childId)
    {
        self::createRecord($parentId, $childId);
        // get all childs of given child Id
        $childsOfGivenChild = self::getAllChildIdsOfParentId($childId);
        foreach ($childsOfGivenChild as $childIdRow){
            self::createRecord($parentId, $childIdRow);
        }
        $parentsOfGivenParent = self::getAllParentIdsOfChildId($parentId);
        foreach ($parentsOfGivenParent as $parentIdRow){
            self::createRecord($parentIdRow, $childId);
        }
    }

    public static function getAllChildIdsOfParentId($parentId)
    {
        $response = [];
        $childs = self::query()->where('parent_id', '=', $parentId)->get();
        foreach ($childs as $child){
            $response[] = $child->getChildId();
        }
        return $response;
    }

    public static function getAllParentIdsOfChildId($childId)
    {
        $response = [];
        $parents = self::query()->where('child_id', '=', $childId)->get();
        foreach ($parents as $parent){
            $response[] = $parent->getParentId();
        }
        return $response;
    }


    public static function createRecord($parentId, $childId)
    {
        $item = new self();
        $item->parent_id = $parentId;
        $item->child_id = $childId;
        $item->save();
    }
}