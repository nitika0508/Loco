<?php


namespace App\Http\Controllers;


use App\Helper\ResponseGenerator;
use App\Models\Transaction;
use App\Models\TransactionMapping;
use Illuminate\Support\Facades\DB;
use Request;

class TransactionController extends Controller
{
    public function createTransaction($transactionId)
    {
        $request = Request::all();
        if(empty($transactionId)){
            return ResponseGenerator::failureResponse("Transaction Id can't be empty");
        }

        if(is_integer($transactionId)){
            return ResponseGenerator::failureResponse("Invalid Transaction Id");
        }

        $transactionIdExisting = Transaction::find($transactionId);
        if(!empty($transactionIdExisting)){
            return ResponseGenerator::failureResponse("Transaction Id Already Exists");
        }

        if(empty($request['type'])){
            return ResponseGenerator::failureResponse("Transaction type can't be empty");
        }
        if(empty($request['amount'])){
            return ResponseGenerator::failureResponse("Transaction amount can't be empty");
        }

        if(!is_float($request['amount']) && !is_integer($request['amount'])){
            return ResponseGenerator::failureResponse("Invalid Transaction amount");
        }
        if(!empty($request['parent_id'])){
            if(!is_integer($request['parent_id'])) {
                return ResponseGenerator::failureResponse("Invalid parent Id");
            }
            $parentIdExisting = Transaction::find($request['parent_id']);
            if(empty($parentIdExisting)){
                return ResponseGenerator::failureResponse("No transaction found with parent Id");
            }
        }

        if($request['parent_id'] == $transactionId){
            return ResponseGenerator::failureResponse("Transaction Id and parent id should be different");
        }

        $requestObj = new \stdClass();
        $requestObj->id = $transactionId;
        $requestObj->amount = $request['amount'];
        $requestObj->type = $request['type'];
        $requestObj->parentId = !empty($request['parent_id']) ? $request['parent_id'] : null;
        \DB::beginTransaction();
        try {
            Transaction::createRecord($requestObj);
            if (!empty($requestObj->parentId)) {
                TransactionMapping::updateMapping($requestObj->parentId, $transactionId);
            }
            \DB::commit();
        }catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            return ResponseGenerator::failureResponse("Failed to create Transaction");
        }
        return ResponseGenerator::successResponse();
    }

    public function getTransaction($transactionId)
    {
        if(empty($transactionId)){
            return ResponseGenerator::failureResponse("Transaction Id can't be empty");
        }

        if(is_integer($transactionId)){
            return ResponseGenerator::failureResponse("Invalid Transaction Id");
        }

        $transaction = Transaction::find($transactionId);
        if(empty($transaction)){
            return ResponseGenerator::failureResponse("Transaction Not Found");
        }
        return json_encode([
            'amount' => $transaction->getAmount(),
            'type' => $transaction->getType(),
            'parent_id' => $transaction->getParentId()
        ]);
    }

    public function getAllTransactionsOfType($type)
    {
        if(empty($type)){
            return ResponseGenerator::failureResponse("Type can't be empty");
        }
        return Transaction::getAllTransactionIdsOfType($type);
    }

    public function getSumTransaction($transactionId)
    {
        if(empty($transactionId)){
            return ResponseGenerator::failureResponse("Transaction Id can't be empty");
        }

        if(is_integer($transactionId)){
            return ResponseGenerator::failureResponse("Invalid Transaction Id");
        }

        $transaction = Transaction::find($transactionId);
        if(empty($transaction)){
            return ResponseGenerator::failureResponse("Transaction Not Found");
        }

        $sum = $transaction->getSumTransaction();
        return json_encode(['sum' => $sum]);
    }

}