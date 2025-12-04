<?php

class PantryItemServices
{
    //
    public function getPantryItem($request)
    {
        $user = Auth::user();
        $admin=Admin::where('user_id',$user->id)->first();
        if ($admin) {
            return $this->adminService->getPantryItems($request);
        }

        return $this->userService->getPantryItems($user);
    } 

    public static function AddPantryItemService($data,$user)
    {
        $householder=HouseHolder::where('user_id',$user->user_id);
        if(!$householder){
            throw new \Exception ("is not house holder");
        }
        $unit = Unit::where('name', $data["unit"])->first();
        if (!$unit) {
            throw new \Exception("No such unit exists");
        }
    
        $unitId = $unit->id;
    
        $pantryItem = new PantryItem();
        $pantryItem->name = $data["name"];
        $pantryItem->quantity = $data["quantity"];
        $pantryItem->unit_id = $unitId;
        $pantryItem->expire_date = $data["expiry_date"];
        $pantryItem->location = $data["location"];
    
        if ($pantryItem->save()) {
            return $pantryItem;
        }
    
        throw new \Exception("The data couldn't be saved");
    }

    public  function UpdatePantryItemService($itemId, $data)
    {
        $pantryItem = PantryItem::find($itemId);
        if ($pantryItem) {
            $pantryItem->update($data);
            return $pantryItem;
        }
        throw  new \Exception( "is not exist");
    }
    public static function DeletePantryItem($itemId)
    {
        $pantryItem = PantryItem::find($itemId);
        if ($pantryItem) {
            $pantryItem->delete();
            return "Deleted successfully";
        }
        return "is not exist";
    }
     public static function GetPantryItemByIdService($itemId,$user){
        $admin=Admin::where('user_id',$user->id);
        $householder=Householder::where('user_id',$user->id);
        $member=HouseHolderMember::where('user_id',$user->id);
        if(!$admin && !$householder && !$member){
             throw  new \Exception( "user is not  ");
        }
        $pantryItem = PantryItem::find($itemId);
        if (!$pantryItem) {
           throw  new \Exception( "the item not found ");
        }

        if($member){
            $householderId=$member->householder_id;
        }
        if($householder){
            $householderId=$householder->id;
        }
        if($pantryItem->householder_id !== $householder_id){
            throw new \Exception("this item is not for you");
        }
        return $pantryItem;
    
     }
}








?>