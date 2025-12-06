<?php

namespace App\Http\Controllers\ShoppingList;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTrait;
use App\Services\ShoppingListService;

class ShoppingListController extends Controller
{
    protected $shoppingListService;

    public function __construct(ShoppingListService $shoppingListService)
    {
        $this->shoppingListService = $shoppingListService;
    }

    public function postShopListFromClient(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User not authenticated", 401);
            }

            $data = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'nullable|numeric|min:0',
                'items.*.unit_id' => 'nullable|exists:units,id',
                'items.*.is_bought' => 'sometimes|boolean',
            ]);

            $result = $this->shoppingListService->postShopListFromClientService($data, $user);
            return ResponseTrait::success(data: $result);

        } catch (\Exception $e) {
            return ResponseTrait::error($e->getMessage(), 500);
        }
    }

    public function addItemForShopList(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User not authenticated", 401);
            }

            $data = $request->validate([
                'shopping_list_id' => 'required|exists:shopping_lists,id',
                'items' => 'required|array|min:1',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'nullable|numeric|min:0',
                'items.*.unit_id' => 'nullable|exists:units,id',
                'items.*.is_bought' => 'sometimes|boolean',
            ]);

            $result = $this->shoppingListService->addItemsToList($user, $data);
            return ResponseTrait::success(data: $result);

        } catch (\Exception $e) {
            return ResponseTrait::error($e->getMessage(), 500);
        }
    }

    public function updateShopList(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User not authenticated", 401);
            }

            $data = $request->validate([
                'shopping_list_id' => 'required|exists:shopping_lists,id',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer|exists:shopping_list_items,id',
                'items.*.name' => 'required|string|max:255',
                'items.*.quantity' => 'nullable|numeric|min:0',
                'items.*.unit_id' => 'nullable|exists:units,id',
                'items.*.is_bought' => 'sometimes|boolean',
            ]);

            $result = $this->shoppingListService->updateShopList($data, $user);
            return ResponseTrait::success(data: $result);

        } catch (\Exception $e) {
            return ResponseTrait::error($e->getMessage(), 500);
        }
    }

    public function getAllShopLists(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User not authenticated", 401);
            }

            $houseHolderIdForAdmin = $request->input('houseHolderIdForAdmin');
            $result = $this->shoppingListService->getAllShopLists($user, $houseHolderIdForAdmin);

            return ResponseTrait::success(data: $result);

        } catch (\Exception $e) {
            return ResponseTrait::error($e->getMessage(), 500);
        }
    }

    public function getShopListItems(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ResponseTrait::error("User not authenticated", 401);
            }

            $request->validate([
                'shopping_list_id' => 'required|exists:shopping_lists,id',
            ]);

            $shoppingListId = $request->input('shopping_list_id');
            $result = $this->shoppingListService->getAllItemsOfShopList($user, $shoppingListId);

            return ResponseTrait::success(data: $result);

        } catch (\Exception $e) {
            return ResponseTrait::error($e->getMessage(), 500);
        }
    }
}



// namespace App\Http\Controllers\ShoppingList;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class ShoppingListController extends Controller
// {
//     //
//     public function PostShopListFromClient(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("the user is authorized");
//             }
//             $data=$request->validate([
             
//                 'items' => 'required|array|min:1',
            
//                 'items.*.name' => 'required|string|max:255',
            
//                 'items.*.quantity' => 'nullable|numeric|min:0',
            
//                 'items.*.unit_id' => 'nullable|exists:units,id',
            
//                 'items.*.is_bought' => 'sometimes|boolean'
//             ]);
//             $result=$this->ShopingListService->PostShopListFromClientService($data,$user);

//             ResponseTrait::success(data:$result);


//         }catch(Exception $e){
//            return ResponseTrait::error($e);
//         }

//     }
//     public function AddItemForShopList(Request $request){

//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("user is not authenticated");
//             }
//             $data=$request->validate([
//                 'shopping_list_id'=>'required|exists:shopping_lists,id',
//                 'items' => 'required|array|min:1',
            
//                 'items.*.name' => 'required|string|max:255',
            
//                 'items.*.quantity' => 'nullable|numeric|min:0',
            
//                 'items.*.unit_id' => 'nullable|exists:units,id',
            
//                 'items.*.is_bought' => 'sometimes|boolean'                
//             ]);
//             $result=$this->SHopingListService->addItemsToList($user,$data);
//             return  ResponseTrait::success(data:$result);
//         }catch(Exception $e){
//             return ResponseTrait::error($e);
//         }
//     }
//     public function UpdateShopList(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("the user is authorized");
//             }
//             $data=$request->validate([
//                 'shopping_list_id'=>'required|exists:shopping_lists,id',
//                 'items' => 'required|array|min:1',
//                 'items.*.item_id'=>'required|int',
//                 'items.*.name' => 'required|string|max:255',
//                 'items.*.quantity' => 'nullable|numeric|min:0',
//                 'items.*.unit_id' => 'nullable|exists:units,id',
//                 'items.*.is_bought' => 'sometimes|boolean'                
//             ]);
//             $result=$this->ShoppingListService->updataShopList($data,$user);
//             return ResponseTrait::success($result);
//         }catch(Exception $e){
//             ResponseTrait::error($e);
//         }
//     }
//     // public function isBought(Request $request ){
//     //     try{
//     //         $user=Autho::user();
//     //         if(!$user){
//     //             return ResponseTrait::error("the user is authorized");
//     //         }
//     //     }catch(Exception $e){
//     //         ResponseTrait::error($e);
//     //     }
//     // }
//     public function GetAllShopList(Request $request ){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("the user is authorized");
//             }
//             $houseHolderIdForAdmin=$request->input('houseHolderIdForAdmin');
//             $result=$this->ShoppingListService->GetAllShopList($user,$houseHolderIdForAdmin);
//             return ResponseTrait::success(data:$result);
//         }catch(Exception $e){
//             ResponseTrait::error($e);
//         }
//     }
//     public function GetShopListItem(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("the user is authorized");
//             }
//             $shopListId=$request->input('shopListId');
//             $result=$this->ShoppingListService->GetAllItemsOfShopList($user,$shopListId);
//             return ResponseTrait::success(data:$result);
//         }catch(Exception $e){
//             ResponseTrait::error($e);
//         }
//     }


// }
