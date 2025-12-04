<?php

class PantryItemServices
{
    //
    public function getPantryItem($request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
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
     public static function GetPantryItemById($itemId){
        $pantryItem = PantryItem::find($itemId);
        if ($pantryItem) {
            return $pantryItem;
        }
        return "is not exist";
     }
}








?>