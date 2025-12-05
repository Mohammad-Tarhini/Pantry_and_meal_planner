<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    //
    public function EnterInvoice(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("this user is not authorized");
            }
            $data=$request->validate([
                'amount'=>'required|decimal',    
                'date'         => 'required|date',
                'category'     => 'required|string|max:255',
                'note'         => 'nullable|string|max:1000',
                'store'        => 'nullable|string|max:255',
                'receipt_url'  => 'nullable|url|max:2048',
                'items' => 'required|array|min:1',
                'items.*.expense_id' => 'required|integer|exists:expenses,id',
                'items.*.item_id'    => 'required|integer|exists:shopping_list_items,id',
                'items.*.expire_date'=>'required|date',
                'item.*.price'=>'required|decimal',
                'item.*.location'=>'required|string'
                
            ]);

        }catch(Exception $e){
            return ResponseTrait::error($e);
        }



    }
     
    
}
