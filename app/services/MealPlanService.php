<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Household;
use App\Models\HouseholdMember;
use App\Models\MealPlan;
use App\Models\MealPlanItem;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * MealPlanService
 *
 * Responsibilities:
 *  - Generate weekly meal plans (with or without user's saved recipes) using an AI service
 *  - Persist meal plans and items to DB (transactional)
 *  - Provide retrieval methods for meal plans and items with role-based access
 *
 * Dependencies:
 *  - UseAIService / AiService: must provide methods used below
 *  - PantryItemService: must provide getPantryItems(User) returning collection/array
 */
class MealPlanService
{
    protected $aiService;
    protected $pantryItemService;

    public function __construct(UseAIService $aiService, PantryItemService $pantryItemService)
    {
        $this->aiService = $aiService;
        $this->pantryItemService = $pantryItemService;
    }

    /* -----------------------------------------------------------------
     | AI generation
     |----------------------------------------------------------------- */

    /**
     * Generate a weekly meal plan using AI (no saved recipes included).
     *
     * @param string $text
     * @param \App\Models\User $user
     * @return array
     * @throws Exception
     */
    public function generateMealByAI(string $text, $user): array
    {
        $householdId = $this->getHouseholdIdFromUser($user);
        $familyCount = $this->getFamilyCount($householdId);

        $pantryItems = $this->pantryItemService->getPantryItems($user);
        $pantryPayload = $this->formatPantryForAi($pantryItems);

        // Build string (or structured payload) for AI prompt
        $pantryJson = json_encode($pantryPayload);

        $response = $this->aiService->generateWeakPlaneAccordingToIngredientAndUserText(
            $text,
            $pantryJson
        );

        if (!isset($response['success']) || $response['success'] !== true) {
            throw new Exception($response['message'] ?? 'AI generation failed');
        }

        return $response['data'];
    }

    /**
     * Generate a weekly meal plan using AI and include user's saved recipes in the prompt.
     *
     * @param string $text
     * @param \App\Models\User $user
     * @return array
     * @throws Exception
     */
    public function generateMealByAIWithRecipes(string $text, $user): array
    {
        $householdId = $this->getHouseholdIdFromUser($user);

        $recipes = Recipe::where('household_id', $householdId)
            ->get(['id', 'title', 'ingredients', 'instructions']); // adjust fields to your model
        $recipesJson = $recipes->toJson();

        $pantryItems = $this->pantryItemService->getPantryItems($user);
        $pantryPayload = $this->formatPantryForAi($pantryItems);
        $pantryJson = json_encode($pantryPayload);

        $response = $this->aiService->generateWeakPlaneAccordingToIngredientAndUserText(
            $text,
            $pantryJson . "\nRECIPES:" . $recipesJson
        );

        if (!isset($response['success']) || $response['success'] !== true) {
            throw new Exception($response['message'] ?? 'AI generation with recipes failed');
        }

        return $response['data'];
    }

    /* -----------------------------------------------------------------
     | Persistence
     |----------------------------------------------------------------- */

    /**
     * Persist a meal plan (week) and its items inside a DB transaction.
     *
     * Expected $data:
     *  - household_id
     *  - week_start (date string)
     *  - week_plan: array of { day_of_week, slot, recipe_id }
     *
     * @param array $data
     * @param \App\Models\User $user
     * @return MealPlan
     * @throws Exception
     */
    public function saveMealPlan(array $data, $user): MealPlan
    {
        // authorize
        $this->authorizeHouseholdAction($user, (int)$data['household_id']);

        // basic service-level sanity checks
        if (empty($data['week_plan']) || !is_array($data['week_plan'])) {
            throw new Exception('Invalid week_plan payload');
        }

        return DB::transaction(function () use ($data) {
            $mealPlan = new MealPlan();
            $mealPlan->household_id = $data['household_id'];
            $mealPlan->week_start = $data['week_start'];
            $mealPlan->save();

            foreach ($data['week_plan'] as $idx => $entry) {
                // minimal validation
                if (!isset($entry['recipe_id'], $entry['day_of_week'], $entry['slot'])) {
                    throw new Exception("week_plan[$idx] missing required fields");
                }

                $mealItem = new MealPlanItem();
                $mealItem->meal_plan_id = $mealPlan->id;
                $mealItem->recipe_id = $entry['recipe_id'];
                $mealItem->day_of_week = (int)$entry['day_of_week'];
                $mealItem->slot = $entry['slot'];
                $mealItem->save();
            }

            return $mealPlan->fresh();
        });
    }

    /* -----------------------------------------------------------------
     | Retrieval
     |----------------------------------------------------------------- */

    /**
     * Return all weeks' meal plans that the user may view.
     * Admins get all or by household id; non-admins get their household only.
     *
     * @param \App\Models\User $user
     * @param int|null $householdIdForAdmin
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getAllWeeksMealsPlan($user, ?int $householdIdForAdmin = null)
    {
        if ($this->isAdmin($user)) {
            if ($householdIdForAdmin) {
                return MealPlan::where('household_id', $householdIdForAdmin)->get();
            }
            return MealPlan::all();
        }

        $householdId = $this->getHouseholdIdFromUser($user);

        return MealPlan::where('household_id', $householdId)->get();
    }

    /**
     * Get all MealPlanItem records for a meal plan if the user is authorized to view them.
     *
     * @param \App\Models\User $user
     * @param int $mealPlanId
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws Exception
     */
    public function getMealsOfWeek($user, int $mealPlanId)
    {
        $mealPlan = MealPlan::find($mealPlanId);
        if (!$mealPlan) {
            throw new Exception('Meal plan not found');
        }

        if (!$this->isAdmin($user)) {
            $userHouseholdId = $this->getHouseholdIdFromUser($user);
            if ($mealPlan->household_id !== $userHouseholdId) {
                throw new Exception('Unauthorized to view this meal plan');
            }
        }

        return MealPlanItem::where('meal_plan_id', $mealPlanId)
            ->with('recipe') // requires relation on MealPlanItem model
            ->get();
    }

    /* -----------------------------------------------------------------
     | Helpers
     |----------------------------------------------------------------- */

    protected function getFamilyCount(int $householdId): int
    {
        return HouseholdMember::where('household_id', $householdId)->count() + 1;
    }

    /**
     * Resolve the household id for the given user.
     *
     * @param \App\Models\User $user
     * @return int
     * @throws Exception
     */
    protected function getHouseholdIdFromUser($user): int
    {
        $household = Household::where('user_id', $user->id)->first();
        if ($household) {
            return $household->id;
        }

        $member = HouseholdMember::where('user_id', $user->id)->first();
        if ($member) {
            return $member->household_id;
        }

        throw new Exception('User does not belong to any household');
    }

    protected function isAdmin($user): bool
    {
        return Admin::where('user_id', $user->id)->exists();
    }

    protected function authorizeHouseholdAction($user, int $householdId): void
    {
        if ($this->isAdmin($user)) {
            return;
        }

        $userHouseholdId = $this->getHouseholdIdFromUser($user);
        if ($userHouseholdId !== $householdId) {
            throw new Exception('Unauthorized to modify this household');
        }
    }

    /**
     * Convert pantry items collection to simple array for AI prompts.
     *
     * @param \Illuminate\Support\Collection|array $pantryItems
     * @return array
     */
    protected function formatPantryForAi($pantryItems): array
    {
        return collect($pantryItems)->map(function ($p) {
            // accept either array or model
            return [
                'name' => $p['name'] ?? ($p->name ?? null),
                'quantity' => $p['quantity'] ?? ($p->quantity ?? null),
                'unit' => $p['unit'] ?? ($p->unit ?? null),
            ];
        })->values()->toArray();
    }
}

// class MealPlanService
// {
//     function HelperGenerateMealByAIForWeak($text,$user,$recipes=null){
//         $householderId=$this->GetHouseHolderId($user);
//        $familyCount = HouseholdMember::where('household_id', $householderId)->count() + 1; 
//        $items=encode($this->PantryItemUserService->getPantryItems($user)->get());
//        if($sendRecipes)
//        $AIResponse=$this-> UseAIService->generateWeakPlaneAccordingToIngredientAndUserText($text,$items,$familycount,$recipes);
//        return $AIResponse;
//     }
//     function GetHouseHolderId($user){
//         $member=HouseHolderMember::where('user_id',$user->id);
//        $householder=Householder::where('user_id',$user->id);
//        if($member){
//         $householderId=$member->householder_id;
//        }
//        else if($householder){
//         $householderId=$householder->id;
//        }
//        if($householderId){
//         return $householderId;
//        }
//        throw throw new \Exception( "there is problem");
//     }
//     function GenerateMealByAIForWeakWithoutCheckTheExistRecipes($text,$user){
//        return  HelperGenerateMealByAIForWeak($text,$user);
//     }
//     function GenerateMealByAIForWeakWithRecipes($text,$user){
//         $householder_id=$this->GetHouseHolderId($user);
//         $recipes=encode(Recipe::where('householder_id',$householder_id)->get());
//         return  HelperGenerateMealByAIForWeak($text,$user,$recipes);
//     }

//     public function SaveMealPlaneService($data, $user){
//         $admin=Admin::where('user_id',$user->id);
//         $member=HouseHolderMember::where('user_id',$user->id);
//         $householder=Household::where('user_id',$user->id);

//         if($admin){
//             throw new \Exception("the admin can not save");
//         }
//         else if($householder){
//             $houseHolder_id=$householder->id;
//         }
//         else if($member){
//             $houseHolder_id=$member->householder_id;
//         }

//         $mealPlan=new MealPlan();

//         $mealPlane->household_id=$houseHolder_id;
//         $mealPlane->week_start=$data['week_start'];
//         if(!$mealPlane->save()){
//             throw new \Exception("the error in saveing on database ");
//         }

//         foreach ($data['week_plan'] as $item) {
//             $mealPlaneItem=new MealPlanItem();
//             $mealPlaneItem->meal_plan_id=$mealPlan->id;
//             $mealPlaneItem->recipe_id=$recipe_id;
//             $mealPlaneItem->day_of_week=$day_of_week;
//             $mealPlaneItem->slot=$slot;
//             if(!$mealplaneItem->save()){
//                 throw new \Excption("the error on saveing Item");
//             }
//         }
//         return $mealPlan;

    

//     }

//     public function GetAllWeaksMealsPlanService($user,$houseHolderIdForAdmin){
//         $admin=Admin::where('user_id',$user->id);
//         $member=HouseHolderMember::where('user_id',$user->id);
//         $householder=Household::where('user_id',$user->id);
//         if($admin){
//             if($houseHolderIdForAdmin){
//                 $houseHolderId=$houseHolderIdForAdmin;
//             }else{
//                 $weaksmeals=MealPlan::All();
//                 if(!$weaksmeals){
//                     throw \Exception("no Meals for admin");
//                 }
//                 return $weaksmeals;
//             }
//         }
//         else if($member){
//             $houseHolderId=$member->householder_id;
//         }
//         else if($householder){
//             $houseHolderId=$householder->id;
//         }
//         else{
//             throw \Exception("sorry , how are you ");
//         }
//         $weaksmeals=MealPlan::Where('householder_id',$houseHolderId);
//         if(!$weaksmeals){
//             throw \Exception("no data  fro the meals ") ;
//         }
//         return $weaksmeals;

//     }
//     public function GetMealsOfWeak($user,$meal_plan_id){
//         $admin=Admin::where('user_id',$user->id);
//         $member=HouseHolderMember::where('user_id',$user->id);
//         $householder=Household::where('user_id',$user->id);
//         if($admin){
//             if($houseHolderIdForAdmin){
//                 $houseHolderId=$houseHolderIdForAdmin;
//             }else{
//                 $meals=MealPlan::All();
//                 if(!$meals){
//                     throw \Exception("no Meals for admin");
//                 }
//                 return $meals;
//             }
//         }
//         else if($member){
//             $houseHolderId=$member->householder_id;
//         }
//         else if($householder){
//             $houseHolderId=$householder->id;
//         }
//         else{
//             throw \Exception("sorry , who are you ");
//         }
//         $meals=MealPlanItem::Where('householder_id',$houseHolderId)
//                             ->where('meal_plan_id',$meal_plan_id)->get();
//         if(!$meals){
//             throw \Exception("no data  about the meals ") ;
//         }
//         return $meals;
//     }





// }











?>