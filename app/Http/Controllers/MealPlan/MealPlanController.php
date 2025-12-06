<?php

namespace App\Http\Controllers\MealPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MealPlanService;
use App\Traits\ResponseTrait;

class MealPlanController extends Controller
{
    use ResponseTrait;

    protected $mealPlanService;

    public function __construct(MealPlanService $mealPlanService)
    {
        $this->mealPlanService = $mealPlanService;
    }

    public function createWeekMealByAi(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'text'           => 'required|string',
                'use_old_recipe' => 'sometimes|boolean'
            ]);

            $text = $validated['text'];
            $useOldRecipe = $validated['use_old_recipe'] ?? false;

            if ($useOldRecipe) {
                $result = $this->mealPlanService->generateMealByAIWithRecipes($text, $user);
            } else {
                $result = $this->mealPlanService->generateMealByAI($text, $user);
            }

            return $this->success($result);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function saveMealsForWeek(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'household_id' => 'required|exists:households,id',
                'week_start'   => 'required|date',

                'week_plan'                     => 'required|array',
                'week_plan.*.day_of_week'       => 'required|integer|min:0|max:6',
                'week_plan.*.slot'              => 'required|in:breakfast,lunch,dinner',
                'week_plan.*.recipe_id'         => 'required|exists:recipes,id',
            ]);

            $result = $this->mealPlanService->saveMealPlan($validated, $user);

            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function getMeals(Request $request)
    {
        try {
            $user = Auth::user();
            $mealPlanId = $request->input('meal_plan_id');

            $result = $this->mealPlanService->getMealsOfWeek($user, $mealPlanId);

            return $this->success($result);

        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}

// namespace App\Http\Controllers\MealPlan;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class MealPlanController extends Controller
// {
//     //
//     public function CreateWeakMealByAi(Request $request){
//         try{
//             $user=Autho::user();
//             $request->validate([
//                 'text'=>'require|string',
//                 'UseOldRecipe'=>'sometime|boolean'
//             ]);
//             $text=$request->input('text');
//             $useOldRecipe=$request->input('UseOldRecipe');
//             if($useOldRecipe){
//                  $result=$this->MealPlanService->GenerateMealByAIForWeakWithRecipes($text,$user);
//             }else{
//                 $result=$this->MealPlanService->GenerateMealByAIForWeak($text,$user);
//             }
            
//             if($result['success']){
//                  return ResponseTrait::success($result);
//             }
//             else{
//                 return ResponseTrait::error($result);
//             }
//         }catch(Exception $e){
//             return ResponseTrait::error($e);
//         }
       

//     }
//     public function saveMealsForWeak(Request $request){
//         try{
//             $user=Autho::user();
//             if(!$user){
//                 return ResponseTrait::error("is not authorized");
//             }
    
//             $validated = $request->validate([
//                 'household_id' => 'required|exists:households,id',
//                 'week_start' => 'required|date',
//                 'week_plan' => 'required|array',
    
//                 'week_plan.*.day_of_week' => 'required|integer|min(0)|max(6)',
//                 'week_plan.*.slot' => 'required|in:breakfast,lunch,dinner',
//                 'week_plan.*.recipe_id' => 'required|exists:recipes,id',
//             ]);
//             $result=$this->MealPlanService->SaveMealPlaneService($validated,$user);
//             return ResponseTrait::success(data:$result);
//         }catch(Exception $e){
//             return ResponseTrait::error($e);
//         }

//     }
//     public function getMeals(Request $request){
//         try{
//             $user=Autho::user();
            
//             $meal_plan_id=$request->input ('meal_plan_id');
//             $result=$this->MealPlanService->GetMealsOfWeak($user,$meal_plan_id);
//             return ResponseTrait::success(data:$result);
//         }catch(Exception $e){
//             return ResponseTrait::error($e);
//         }
//     }
//     // public function DeleteWeakPlanMeals(Request $request){

//     // }
//     // public function DeleteMealPlan(){

//     // }


    
    
// }
