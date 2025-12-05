<?php
class MealPlanService
{
    function HelperGenerateMealByAIForWeak($text,$user,$recipes=null){
        $householderId=$this->GetHouseHolderId($user);
       $familyCount = HouseholdMember::where('household_id', $householderId)->count() + 1; 
       $items=encode($this->PantryItemUserService->getPantryItems($user)->get());
       if($sendRecipes)
       $AIResponse=$this-> UseAIService->generateWeakPlaneAccordingToIngredientAndUserText($text,$items,$familycount,$recipes);
       return $AIResponse;
    }
    function GetHouseHolderId($user){
        $member=HouseHolderMember::where('user_id',$user->id);
       $householder=Householder::where('user_id',$user->id);
       if($member){
        $householderId=$member->householder_id;
       }
       else if($householder){
        $householderId=$householder->id;
       }
       if($householderId){
        return $householderId;
       }
       throw throw new \Exception( "there is problem");
    }
    function GenerateMealByAIForWeakWithoutCheckTheExistRecipes($text,$user){
       return  HelperGenerateMealByAIForWeak($text,$user);
    }
    function GenerateMealByAIForWeakWithRecipes($text,$user){
        $householder_id=$this->GetHouseHolderId($user);
        $recipes=encode(Recipe::where('householder_id',$householder_id)->get());
        return  HelperGenerateMealByAIForWeak($text,$user,$recipes);
    }

    public function SaveMealPlaneService($data, $user){
        $admin=Admin::where('user_id',$user->id);
        $member=HouseHolderMember::where('user_id',$user->id);
        $householder=Household::where('user_id',$user->id);

        if($admin){
            throw new \Exception("the admin can not save");
        }
        else if($householder){
            $houseHolder_id=$householder->id;
        }
        else if($member){
            $houseHolder_id=$member->householder_id;
        }

        $mealPlan=new MealPlan();

        $mealPlane->household_id=$houseHolder_id;
        $mealPlane->week_start=$data['week_start'];
        if(!$mealPlane->save()){
            throw new \Exception("the error in saveing on database ");
        }

        foreach ($data['week_plan'] as $item) {
            $mealPlaneItem=new MealPlanItem();
            $mealPlaneItem->meal_plan_id=$mealPlan->id;
            $mealPlaneItem->recipe_id=$recipe_id;
            $mealPlaneItem->day_of_week=$day_of_week;
            $mealPlaneItem->slot=$slot;
            if(!$mealplaneItem->save()){
                throw new \Excption("the error on saveing Item");
            }
        }
        return $mealPlan;

    

    }

    public function GetAllWeaksMealsPlanService($user,$houseHolderIdForAdmin){
        $admin=Admin::where('user_id',$user->id);
        $member=HouseHolderMember::where('user_id',$user->id);
        $householder=Household::where('user_id',$user->id);
        if($admin){
            if($houseHolderIdForAdmin){
                $houseHolderId=$houseHolderIdForAdmin;
            }else{
                $weaksmeals=MealPlan::All();
                if(!$weaksmeals){
                    throw \Exception("no Meals for admin");
                }
                return $weaksmeals;
            }
        }
        else if($member){
            $houseHolderId=$member->householder_id;
        }
        else if($householder){
            $houseHolderId=$householder->id;
        }
        else{
            throw \Exception("sorry , how are you ");
        }
        $weaksmeals=MealPlan::Where('householder_id',$houseHolderId);
        if(!$weaksmeals){
            throw \Exception("no data  fro the meals ") ;
        }
        return $weaksmeals;

    }
    public function GetMealsOfWeak($user,$meal_plan_id){
        $admin=Admin::where('user_id',$user->id);
        $member=HouseHolderMember::where('user_id',$user->id);
        $householder=Household::where('user_id',$user->id);
        if($admin){
            if($houseHolderIdForAdmin){
                $houseHolderId=$houseHolderIdForAdmin;
            }else{
                $meals=MealPlan::All();
                if(!$meals){
                    throw \Exception("no Meals for admin");
                }
                return $meals;
            }
        }
        else if($member){
            $houseHolderId=$member->householder_id;
        }
        else if($householder){
            $houseHolderId=$householder->id;
        }
        else{
            throw \Exception("sorry , who are you ");
        }
        $meals=MealPlanItem::Where('householder_id',$houseHolderId)
                            ->where('meal_plan_id',$meal_plan_id)->get();
        if(!$meals){
            throw \Exception("no data  about the meals ") ;
        }
        return $meals;
    }





}











?>