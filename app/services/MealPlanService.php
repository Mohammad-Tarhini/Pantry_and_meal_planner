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
    function GenerateMealByAIForWeakWithoutRecipes($text,$user){
       return  HelperGenerateMealByAIForWeak($text,$user);
    }
    function GenerateMealByAIForWeakWithRecipes($text,$user){
        $householder_id=$this->GetHouseHolderId($user);
        $recipes=encode(Recipe::where('householder_id',$householder_id)->get());
        return  HelperGenerateMealByAIForWeak($text,$user,$recipes);

    }
}











?>