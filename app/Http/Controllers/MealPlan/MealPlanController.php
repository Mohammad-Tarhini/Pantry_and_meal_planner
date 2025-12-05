<?php

namespace App\Http\Controllers\MealPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    //
    public function CreateWeakMealByAi(Request $request){
        try{
            $user=Autho::user();
            $request->validate([
                'text'=>'require|string',
                'UseOldRecipe'=>'sometime|boolean'
            ]);
            $text=$request->input('text');
            $useOldRecipe->input('UseOldRecipe');
            if($useOldRecipe){
                 $result=$this->MealPlanService->GenerateMealByAIForWeakWithRecipes($text,$user);
            }else{
                $result=$this->MealPlanService->GenerateMealByAIForWeak($text,$user);
            }
            
            if($result['success']){
                 return ResponseTrait::success($result);
            }
            else{
                return ResponseTrait::error($result);
            }
        }catch(Exception $e){
            return ResponseTrait::error($e);
        }
       

    }
    public function saveMealsForWeak(Request $request){
        try{
            $user=Autho::user();
            if(!$user){
                return ResponseTrait::error("is not authorized");
            }
    
            $validated = $request->validate([
                'household_id' => 'required|exists:households,id',
                'week_start' => 'required|date',
                'week_plan' => 'required|array',
    
                'week_plan.*.day_of_week' => 'required|integer|min(0)|max(6)',
                'week_plan.*.slot' => 'required|in:breakfast,lunch,dinner',
                'week_plan.*.recipe_id' => 'required|exists:recipes,id',
            ]);
            $result=$this->MealPlanService->SaveMealPlaneService($validated,$user);
            return ResponseTrait::success(data:$result);
        }catch(Exception $e){
            return ResponseTrait::error($e);
        }

    }
    public function getMeals(Request $request){
        try{
            $user=Autho::user();
            
        }catch(Exception $e){
            return ResponseTrait::error($e);
        }
    }


    
    
}
