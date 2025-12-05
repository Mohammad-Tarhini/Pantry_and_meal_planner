<?php

class ShopingListService{
    public function CheckUserRoleAndGetHouseHolderId($user){
        $householder=Household::where('user_id',$user->id);
        $member=HouseHolderMember::where('userid',$user->id);
        if($householder){
            $houseHolderId=$householder->id;
        }
        else if($member){
            $houseHolderId=$member->houseHolder_id;
        }
        else{
            throw \Exception ("the user is not have role that allow him to add items");
        }
        return $houseHolderId;

    }

    function PostShopListFromClientService($validatedata, $user){
        $admin=Admin::where('user_id',$user->id);
        
        if($admin){
            throw new Exception("the admin can nt post data");
        }
        $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
        if(!$houseHolderId){
            throw Exception("errorre");
        }
        $shoppingList=new ShoppingList();
        $shoppingList->houseHolder_id=$houseHolderId;
        if(!$shoppingList->save()){
            throw Exception("there is error on saving data ");
        }

        foreach($validatedata['items'] as $item){
            $shoppListItem=new ShoppingListItem();
            $shoppListItem->shopping_list_id=$shoppingList->id;
            $shoppListItem->name=$item['name'];
            $shoppListItem->quantity=$item['quantity'];
            $shoppListItem->unit_id=$item['unit_id'];
            $shoppListItem->is_bought=$item['is_bought'];
            if($shoppingListItem->save()){
                throw Exception("the eror in save the items ");
            }

        }
        return $shoppingList;

    }
    public function addItemsToList($user,$data){
        $houseHolderId=CheckUserRoleAndGetHouseHolderId($user);
        if(!$houseHolderId){
            throw Exception("errorre");
        }
        $addShoppingListItems=[];
        foreach($data['items'] as $item){
            $shoppListItem=new ShoppingListItem();
            $shoppListItem->$data['shopping_list_id'];
            $shoppListItem->name=$item['name'];
            $shoppListItem->quantity=$item['quantity'];
            $shoppListItem->unit_id=$item['unit_id'];
            $shoppListItem->is_bought=$item['is_bought'];
            if($shoppingListItem->save()){
                throw Exception("the eror in save the items ");
            }
            $addShoppingListItems=[$shoppListItem];
        } 
        return $addShoppingListItems;
    }
    public function updataShopList(){

    }
}










?>