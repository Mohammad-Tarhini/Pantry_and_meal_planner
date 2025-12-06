<?php

namespace App\Services;

use App\Models\PantryItem;
use App\Models\Unit;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\Admin;
use Illuminate\Http\Request;
use Exception;

class PantryItemService
{
    protected $adminService;
    protected $userService;

    public function __construct(AdminPantryService $adminService, UserPantryService $userService)
    {
        $this->adminService = $adminService;
        $this->userService = $userService;
    }

    /**
     * Route to admin or user service depending on user role.
     */
    public function getPantryItems(Request $request, $user)
    {
        $isAdmin = Admin::where('user_id', $user->id)->exists();
        if ($isAdmin) {
            return $this->adminService->getPantryItemsAdmin($request);
        }

        return $this->userService->getPantryItems($user);
    }

    /**
     * Add a pantry item for the household of the user.
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return PantryItem
     * @throws Exception
     */
    public function addPantryItem(array $data, $user)
    {
        $householdId = $this->resolveHouseholdIdForUser($user);

        $unit = Unit::where('name', $data['unit'])->first();
        if (!$unit) {
            throw new Exception("No such unit exists");
        }

        $pantryItem = new PantryItem();
        $pantryItem->name = $data['name'];
        $pantryItem->quantity = $data['quantity'];
        $pantryItem->unit_id = $unit->id;
        $pantryItem->expire_date = $data['expiry_date'] ?? null;
        $pantryItem->location = $data['location'] ?? null;
        $pantryItem->household_id = $householdId;

        if ($pantryItem->save()) {
            return $pantryItem;
        }

        throw new Exception("The data couldn't be saved");
    }

    /**
     * Update a pantry item (must belong to user's household or user is admin).
     */
    public function updatePantryItem($itemId, array $data, $user)
    {
        $item = $this->checkIfItemBelongsToUser($itemId, $user);

        if (isset($data['unit'])) {
            $unit = Unit::where('name', $data['unit'])->first();
            if (!$unit) {
                throw new Exception("No such unit exists");
            }
            $data['unit_id'] = $unit->id;
            unset($data['unit']);
        }

        $item->fill($data);
        $item->save();

        return $item;
    }

    public function deletePantryItem($itemId, $user)
    {
        $item = $this->checkIfItemBelongsToUser($itemId, $user);
        $item->delete();
        return true;
    }

    public function getPantryItemById($itemId, $user)
    {
        $item = $this->checkIfItemBelongsToUser($itemId, $user);
        return $item;
    }

    /* -------------------- Helpers -------------------- */

    protected function resolveHouseholdIdForUser($user)
    {
        $household = Household::where('user_id', $user->id)->first();
        if ($household) {
            return $household->id;
        }

        $member = HouseholdMember::where('user_id', $user->id)->first();
        if ($member) {
            return $member->household_id;
        }

        throw new Exception("User does not belong to a household");
    }

    /**
     * Ensure the item belongs to the same household as the user, or user is admin.
     *
     * @throws Exception
     */
    protected function checkIfItemBelongsToUser($itemId, $user)
    {
        $pantryItem = PantryItem::find($itemId);
        if (!$pantryItem) {
            throw new Exception("Pantry item not found");
        }

        $isAdmin = Admin::where('user_id', $user->id)->exists();
        if ($isAdmin) {
            return $pantryItem;
        }

        $householdId = $this->resolveHouseholdIdForUser($user);

        if ($pantryItem->household_id !== $householdId) {
            throw new Exception("This item does not belong to your household");
        }

        return $pantryItem;
    }
}


// class PantryItemServices
// {
//     //
//     public function getPantryItem($request)
//     {
//         $user = Auth::user();
//         $admin=Admin::where('user_id',$user->id)->first();
//         if ($admin) {
//             return $this->adminService->getPantryItems($request);
//         }

//         return $this->userService->getPantryItems($user);
//     } 

//     public static function AddPantryItemService($data,$user)
//     {
//         $householder=HouseHolder::where('user_id',$user->user_id);
//         if(!$householder){
//             throw new \Exception ("is not house holder");
//         }
//         $unit = Unit::where('name', $data["unit"])->first();
//         if (!$unit) {
//             throw new \Exception("No such unit exists");
//         }
    
//         $unitId = $unit->id;
    
//         $pantryItem = new PantryItem();
//         $pantryItem->name = $data["name"];
//         $pantryItem->quantity = $data["quantity"];
//         $pantryItem->unit_id = $unitId;
//         $pantryItem->expire_date = $data["expiry_date"];
//         $pantryItem->location = $data["location"];
    
//         if ($pantryItem->save()) {
//             return $pantryItem;
//         }
    
//         throw new \Exception("The data couldn't be saved");
//     }

//     public  function UpdatePantryItemService($itemId, $data,$user)
//     {
//         $item=$this->CheckIfItemIsForThisUser($itemId,$user);
//         if ($item) {
//             $item->update($data);
//             return $item;
//         }
//         throw  new \Exception( "is not exist");
//     }
//     public static function DeletePantryItemService($itemId,$user)
//     {
//         $item=$this->CheckIfItemIsForThisUser($itemId,$user);
//         if ($item) {
//             $item->delete();
//             return $tiem ;
//         }
//         throw new \Exception( "is not exist");
//     }
//      public static function GetPantryItemByIdService($itemId,$user){
//         $item=$this->CheckIfItemIsForThisUser($itemId,$user);
//         return $item;

//      }
//      function CheckIfItemIsForThisUser($itemId,$user){
//         $admin=Admin::where('user_id',$user->id);
//         $householder=Householder::where('user_id',$user->id);
//         $member=HouseHolderMember::where('user_id',$user->id);
//         if(!$admin && !$householder && !$member){
//              throw  new \Exception( "user is not  ");
//         }
//         $pantryItem = PantryItem::find($itemId);
//         if (!$pantryItem) {
//            throw  new \Exception( "the item not found ");
//         }

//         if($member){
//             $householderId=$member->householder_id;
//         }
//         if($householder){
//             $householderId=$householder->id;
//         }
//         if($pantryItem->householder_id !== $householder_id){
//             throw new \Exception("this item is not for you");
//         }
//         return $pantryItem;
//      }
// }








?>