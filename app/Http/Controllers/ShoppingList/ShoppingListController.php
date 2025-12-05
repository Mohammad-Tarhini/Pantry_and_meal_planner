<?php

namespace App\Http\Controllers\ShoppingList;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShoppingListController extends Controller
{
    //
    public function PostShopListFromClient(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("the user is authorized");
            }
            $data=$request->validate([
             
                'items' => 'required|array|min:1',
            
                'items.*.name' => 'required|string|max:255',
            
                'items.*.quantity' => 'nullable|numeric|min:0',
            
                'items.*.unit_id' => 'nullable|exists:units,id',
            
                'items.*.is_bought' => 'sometimes|boolean'
            ]);
            $result=$this->ShopingListService->PostShopListFromClientService($data,$user);

            ResponseTrait::success(data:$result);


        }catch(Exception $e){
           return ResponseTrait::error($e);
        }

    }
    public function AddItemForShopList(Request $request){

        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("user is not authenticated");
            }
            $data=$request->validate([
                'shopping_list_id'=>'required|exists:shopping_lists,id',
                'items' => 'required|array|min:1',
            
                'items.*.name' => 'required|string|max:255',
            
                'items.*.quantity' => 'nullable|numeric|min:0',
            
                'items.*.unit_id' => 'nullable|exists:units,id',
            
                'items.*.is_bought' => 'sometimes|boolean'                
            ]);
            $result=$this->SHopingListService->addItemsToList($user,$data);
            return  ResponseTrait::success(data:$result);
        }catch(Exception $e){
            return ResponseTrait::error($e);
        }
    }
    public function UpdateShopList(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("the user is authorized");
            }
            $data=$request->validate([
                'shopping_list_id'=>'required|exists:shopping_lists,id',
                'items' => 'required|array|min:1',
                'items.*.item_id'=>'required|int',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'nullable|numeric|min:0',
                'items.*.unit_id' => 'nullable|exists:units,id',
                'items.*.is_bought' => 'sometimes|boolean'                
            ]);
            $result=$this->ShoppingListService->updataShopList($data,$user);
            return ResponseTrait::success($result);
        }catch(Exception $e){
            ResponseTrait::error($e);
        }
    }
    // public function isBought(Request $request ){
    //     try{
    //         $user=Autho::user();
    //         if(!$user){
    //             return ResponseTrait::error("the user is authorized");
    //         }
    //     }catch(Exception $e){
    //         ResponseTrait::error($e);
    //     }
    // }
    public function GetAllShopList(Request $request ){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("the user is authorized");
            }
            $houseHolderIdForAdmin=$request->input('houseHolderIdForAdmin');
            $result=$this->ShoppingListService->GetAllShopList($user,$houseHolderIdForAdmin);
            return ResponseTrait::success(data:$result);
        }catch(Exception $e){
            ResponseTrait::error($e);
        }
    }
    public function GetShopListItem(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("the user is authorized");
            }
            $shopListId=$request->input('shopListId');
            $result=$this->ShoppingListService->GetAllItemsOfShopList($user,$shopListId);
            return ResponseTrait::success(data:$result);
        }catch(Exception $e){
            ResponseTrait::error($e);
        }
    }


}
