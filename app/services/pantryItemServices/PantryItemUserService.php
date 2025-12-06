<?php


namespace App\Services;

use App\Models\PantryItem;
use App\Models\Household;
use App\Models\HouseholdMember;
use Exception;

class UserPantryService
{
    public function getPantryItems($user)
    {
        $member = HouseholdMember::where('user_id', $user->id)->first();
        $household = Household::where('user_id', $user->id)->first();

        if (!$member && !$household) {
            throw new Exception("User is not part of any household", 403);
        }

        if ($member) {
            $householdId = $member->household_id;
        } else {
            $householdId = $household->id;
        }

        return PantryItem::where('household_id', $householdId)->get();
    }
}


// class UserPantryService
// {
//     public function getPantryItems($user)
//     {
//         $member = HouseHolderMember::where('user_id', $user->id)->first();
//         $householder=Household::where('user_id',$user->id)->first();

//         if (!$member || !$householder) {
//             throw new \Exception("User is not a householder", 403);
//         }
//         if($member){
//             $houseHooldId=$member->household_id;
//         }
//         else if($householder){
//             $houseHooldId=$householder->household_id;
//         }

//         return PantryItem::where('household_id',$houseHooldId )->get();
//     }
// }


?>