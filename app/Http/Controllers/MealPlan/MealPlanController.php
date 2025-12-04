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
                'text'=>'require|string'
            ]);
            $text=$request->input('text');
            $result=$this->MealPlanService->GenerateMealByAIForWeak();
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

    
    
}
