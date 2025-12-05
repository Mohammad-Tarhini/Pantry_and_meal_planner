<?php

class ShopingListService{

    function PostShopListFromClientService($validatedata, $user){
        $admin=Admin::where('user_id',$user->id);
        $householder=Household::where('user_id',$user->id);
        $member=HouseHolderMember::where('userid',$user->id);

        if($admin){
            throw new Exception("the admin can nt post data");
        }
        if($householder){
            $houseHolderId=$householder->id;
        }
        if($member){
            $houseHolderId=$member->houseHolder_id;
        }
        $shoppingList=new ShoppingList();
        $shoppingList->houseHolder_id=$houseHolderId;
        if(!$shoppingList->save()){
            throw Exception("there is error on saving data ");
        }

        foreach($validatedata['items'] as $item){
            $shoppListItem=new ShoppingListItem();
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
    public function updataShopList(){
        
    }
}










?>