<?php

namespace App\Services;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\Household;
use App\Models\HouseHolderMember;
use App\Models\Admin;
use Exception;
use Illuminate\Support\Facades\DB;

class ShoppingListService
{
    // Get householder id based on user role
    public function checkUserRoleAndGetHouseHolderId($user)
    {
        $householder = Household::where('user_id', $user->id)->first();
        $member = HouseHolderMember::where('user_id', $user->id)->first();

        if ($householder) {
            return $householder->id;
        } elseif ($member) {
            return $member->household_id;
        } else {
            throw new Exception("User does not have permission to manage shopping lists");
        }
    }

    public function postShopListFromClientService(array $data, $user)
    {
        $admin = Admin::where('user_id', $user->id)->first();
        if ($admin) {
            throw new Exception("Admins cannot create shopping lists");
        }

        $houseHolderId = $this->checkUserRoleAndGetHouseHolderId($user);

        DB::beginTransaction();
        try {
            $shoppingList = new ShoppingList();
            $shoppingList->household_id = $houseHolderId;
            $shoppingList->save();

            foreach ($data['items'] as $item) {
                $shoppingListItem = new ShoppingListItem();
                $shoppingListItem->shopping_list_id = $shoppingList->id;
                $shoppingListItem->name = $item['name'];
                $shoppingListItem->quantity = $item['quantity'] ?? 0;
                $shoppingListItem->unit_id = $item['unit_id'] ?? null;
                $shoppingListItem->is_bought = $item['is_bought'] ?? false;
                $shoppingListItem->save();
            }

            DB::commit();
            return $shoppingList->load('items');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addItemsToList($user, $data)
    {
        $houseHolderId = $this->checkUserRoleAndGetHouseHolderId($user);

        $shoppingList = ShoppingList::findOrFail($data['shopping_list_id']);
        if ($shoppingList->household_id !== $houseHolderId) {
            throw new Exception("This shopping list does not belong to the user");
        }

        $addedItems = [];
        foreach ($data['items'] as $item) {
            $shoppingListItem = new ShoppingListItem();
            $shoppingListItem->shopping_list_id = $shoppingList->id;
            $shoppingListItem->name = $item['name'];
            $shoppingListItem->quantity = $item['quantity'] ?? 0;
            $shoppingListItem->unit_id = $item['unit_id'] ?? null;
            $shoppingListItem->is_bought = $item['is_bought'] ?? false;
            $shoppingListItem->save();
            $addedItems[] = $shoppingListItem;
        }

        return $addedItems;
    }

    public function updateShopList($data, $user)
    {
        $houseHolderId = $this->checkUserRoleAndGetHouseHolderId($user);

        foreach ($data['items'] as $itemData) {
            $item = ShoppingListItem::findOrFail($itemData['id']);
            $shoppingList = $item->shoppingList;

            if ($shoppingList->household_id !== $houseHolderId) {
                throw new Exception("This item does not belong to the user");
            }

            $item->update([
                'name' => $itemData['name'],
                'quantity' => $itemData['quantity'] ?? 0,
                'unit_id' => $itemData['unit_id'] ?? null,
                'is_bought' => $itemData['is_bought'] ?? false,
            ]);
        }

        return "Items updated successfully";
    }

    public function getAllShopLists($user, $houseHolderIdForAdmin = null)
    {
        $admin = Admin::where('user_id', $user->id)->first();

        if ($admin) {
            if ($houseHolderIdForAdmin) {
                return ShoppingList::where('household_id', $houseHolderIdForAdmin)->with('items')->get();
            }
            return ShoppingList::with('items')->get();
        }

        $houseHolderId = $this->checkUserRoleAndGetHouseHolderId($user);
        return ShoppingList::where('household_id', $houseHolderId)->with('items')->get();
    }

    public function getAllItemsOfShopList($user, $shoppingListId)
    {
        $houseHolderId = $this->checkUserRoleAndGetHouseHolderId($user);

        $shoppingList = ShoppingList::findOrFail($shoppingListId);
        if ($shoppingList->household_id !== $houseHolderId) {
            throw new Exception("This shopping list does not belong to the user");
        }

        return $shoppingList->items()->get();
    }
}

// class ShopingListService{
//     public function CheckUserRoleAndGetHouseHolderId($user){
//         $householder=Household::where('user_id',$user->id);
//         $member=HouseHolderMember::where('userid',$user->id);
//         if($householder){
//             $houseHolderId=$householder->id;
//         }
//         else if($member){
//             $houseHolderId=$member->houseHolder_id;
//         }
//         else{
//             throw \Exception ("the user is not have role that allow him to add items");
//         }
//         return $houseHolderId;

//     }

//     function PostShopListFromClientService($validatedata, $user){
//         $admin=Admin::where('user_id',$user->id);
        
//         if($admin){
//             throw new Exception("the admin can nt post data");
//         }
//         $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
//         if(!$houseHolderId){
//             throw Exception("errorre");
//         }
//         $shoppingList=new ShoppingList();
//         $shoppingList->houseHolder_id=$houseHolderId;
//         if(!$shoppingList->save()){
//             throw Exception("there is error on saving data ");
//         }

//         foreach($validatedata['items'] as $item){
//             $shoppListItem=new ShoppingListItem();
//             $shoppListItem->shopping_list_id=$shoppingList->id;
//             $shoppListItem->name=$item['name'];
//             $shoppListItem->quantity=$item['quantity'];
//             $shoppListItem->unit_id=$item['unit_id'];
//             $shoppListItem->is_bought=$item['is_bought'];
//             if($shoppingListItem->save()){
//                 throw Exception("the eror in save the items ");
//             }

//         }
//         return $shoppingList;

//     }
//     public function addItemsToList($user,$data){
//         $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
//         if(!$houseHolderId){
//             throw Exception("errorre");
//         }
//         $addShoppingListItems=[];
//         foreach($data['items'] as $item){
//             $shoppListItem=new ShoppingListItem();
//             $shoppListItem->$data['shopping_list_id'];
//             $shoppListItem->name=$item['name'];
//             $shoppListItem->quantity=$item['quantity'];
//             $shoppListItem->unit_id=$item['unit_id'];
//             $shoppListItem->is_bought=$item['is_bought'];
//             if($shoppingListItem->save()){
//                 throw Exception("the eror in save the items ");
//             }
//             $addShoppingListItems=[$shoppListItem];
//         } 
//         return $addShoppingListItems;
//     }
//     public function updataShopList($data,$user){
//         $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
//         if(!$houseHolderId){
//             throw Exception("errorre");
//         }
//         foreach($data['items'] as $data){
//             $item = ShoppingListItem::findOrFail($id);
//             $item->name = $data['name'];
//             $item->quantity = $data['quantity'];
//             $item->unit_id = $data['unit_id'];
//             $item->is_bought = $data['is_bought'];   
//             $item->save(); 
//         }
//         return "item updated correctly";  
//     }
//     public function GetAllShopList($user,$houseHolderIdForAdmin){
//         $admin=Admin::Where('user_id',$user->id);
//         if($admin){ 
//             if($houseHolderIdForAdmin){
//                 $houseHolderId=$houseHolderIdForAdmin;
//             }else{
//                 $shoplist=ShoppingList::All()->get();
//                 return $shopList;
//             }
//         }
//         $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
//         if(!$houseHolderId){
//             throw Exception("errorre");
//         }
        
//         $shopList=ShoppingList::where('householder_id',$houseHolderId);
//         if(!$shopList){
//             throw Exception("no shopping Lists ");
//         }
//         return $shopList;
//     }
//     public function GetAllItemsOfShopList($user,$shopListId){
//         $admin=Admin::Where('user_id',$user->id);
//         if($admin){ 
//             if($houseHolderIdForAdmin){
//                 $houseHolderId=$houseHolderIdForAdmin;
//             }else{
//                 $shoplist=ShoppingListItem::All()->get();
//                 return $shopList;
//             }
//         }
//         $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
//         if(!$houseHolderId){
//             throw Exception("errorre");
//         }
//         $shoppingList=ShoppingList::where('id',$shopListId);
//         if($shoppingList->householder_id !== $houseHolderId){
//             throw Exception("this shopping lsit is not for this user  ");
//         }
//         $ItemOfShopList=ShoppingListItem::where('shopping_list_id',$shopListId);
//         if(!$ItemOfShopList){
//             throw Exception("no item on this shopping list");
//         }
//         return $ItemOfShopList;

//     }
// }










?>