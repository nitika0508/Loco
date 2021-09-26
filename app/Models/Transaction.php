<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = "loco_transactions";
    public $timestamps = false;

    public function getId()
    {
        return $this->id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getParentId()
    {
        return $this->parent_id;
    }

    public function childs()
    {
        return $this->hasMany('App\Models\TransactionMapping','parent_id','id');
    }

    public static function createRecord($requestObj)
    {
        $transaction = new self();
        $transaction->id = $requestObj->id;
        $transaction->amount = $requestObj->amount;
        $transaction->type = $requestObj->type;
        $transaction->parent_id = $requestObj->parentId;
        $transaction->save();
    }

    public function getAllChildIds()
    {
        $response = [];
        $childs = $this->childs;
        foreach ($childs as $child){
            $response[] = $child->getChildId();
        }
        return $response;
    }

    public static function getAllTransactionIdsOfType($type)
    {
        $response = [];
        $transactions = self::query()->where('type', '=', $type)->get();
        foreach ($transactions as $transaction){
            $response[] = $transaction->getId();
        }
        return $response;
    }

    public function getSumTransaction()
    {
        $response = $this->getAmount();
        $childsOfTransactionId = $this->getAllChildIds();
        $sumofAllChilds = 0;
        if(!empty($childsOfTransactionId)) {
            $sumofAllChilds = $this->getSumOfTransactionIds($childsOfTransactionId);
        }
        $response = $response + $sumofAllChilds;
        return $response;
    }

    public function getSumOfTransactionIds($transactionIds)
    {
        $response = 0;
        $transactions = self::query()->whereIn('id', $transactionIds)->get();
        foreach ($transactions as $transaction){
            $response += $transaction->getAmount();
        }
        return $response;
    }

}