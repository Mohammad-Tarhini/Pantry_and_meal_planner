<?php

class PantryItemServices
{
    //
    public static function GetAllPantryItemsForHouseHolder($householdId)
    {
        return PantryItem::where('household_id', $householdId)->get();
    }
    public static function AddPantryItem($data)
    {
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

    public static function UpdatePantryItem($itemId, $data)
    {
        $pantryItem = PantryItem::find($itemId);
        if ($pantryItem) {
            $pantryItem->update($data);
            return $pantryItem;
        }
        return "is not exist";
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