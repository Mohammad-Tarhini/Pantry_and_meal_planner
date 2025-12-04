<?php
namespace App\Services;

use App\AIServices\ChatGptService;
use App\Services\PantryItemServices;

class UseAIService
{
    protected $groqService;

    public function __construct()
    {
        $this->groqService = new \App\Services\GroqService();
    }

    public function getAIResponse($userMessage)
    {
        $response = $this->groqService->ask($userMessage);
        if (isempty($response['choices'][0]['message']['content'])) {
            return [
                    'success' => true,
                    'message' => 'AI response retrieved successfully',
                    'data' => $response['choices'][0]['message']['content']
                   ]; 
        }
        return [
        'success' => false,
        'message' => 'No valid response from AI',
        'data' => null
        ];
    }

    public function getRecipeFromGroq($userTxt, $gradientItem_string,$recipes_string, $repeat = 1, $errormsg = "")
    {
        if ($repeat > 3) {
            return [
                'success' => false,
                'message' => 'Exceeded maximum retry attempts for AI response',
                'data' => null
            ];
        }
    
        $text1 = "Using the following pantry items and try to use how have approuch expire_date: " . $gradientItem_string . 
                  "if those recipes exist please use themand also put new ".$recipes_string." if they  not exist put all news recipes ".
                  "take care also that tej number of member of this family is ".$familycount.
                 ", create a recipe based on this user request. If it does not exist, give me Recipe from you according to gradient: " . $userTxt . 
                 ". Return the recipe in JSON format with title, ingredients in array of contain the object of each gedient i should use, and instructions.";
    
        $text2 = $text1 . " I sent before this but there was an error: " . $errormsg . " Please ensure the response is in valid JSON format with the required fields: title, ingredients, and instructions.";
    
        $prompt = $repeat === 1 ? $text1 : $text2;
    
        $response = $this->getAIResponse($prompt);
    
        if (!$response['success']) {
            return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, $response['message']);
        }
    
        $recipeJson = $response['message'];
        $recipeData = json_decode($recipeJson, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, "Invalid JSON format received from AI");
        }
    
        if (isset($recipeData['title'], $recipeData['ingredients'], $recipeData['instructions'])) {
            return [
                'success' => true,
                'message' => 'Recipe created successfully',
                'data' => $recipeData
            ];
        } 
    
        return $this->getRecipeFromGroq($userTxt, $gradientItem_string, $repeat + 1, "Missing required recipe fields in AI response");
    }


    public function generateWeakPlaneAccordingToIngredientAndUserText(
        $userTxt, 
        $gradientItem_string, 
        $repeat = 1, 
        $errormsg = ""
    ) {
        //  Prevent infinite recursive loops
        if ($repeat > 3) {
            return [
                'success' => false,
                'message' => 'Exceeded maximum retry attempts for AI response',
                'data' => null
            ];
        }
    
        // ---------- PROMPT BUILDING ----------
        $text1 = "Using these items: $gradientItem_string, give me meals for the upcoming week.
        Format the response as an ARRAY OF OBJECTS with:
        - day_of_week
        - slot: ['breakfast', 'lunch', 'dinner']
        - recipe: { title, instructions, gradients: [ { item_name, quantity, unit_id } ] }
        
        Also, include ANOTHER ARRAY:
        - items I should buy (the missing gradients needed to complete all meals).";
    
        $text2 = $text1 . " Previous attempt failed with error: " . $errormsg;
    
        $prompt = ($repeat === 1) ? $text1 : $text2;
    
        // ---------- CALL AI ----------
        $response = $this->getAIResponse($prompt);
    
        if (!$response['success']) {
            return $this->generateWeakPlaneAccordingToIngredientAndUserText(
                $userTxt, 
                $gradientItem_string, 
                $repeat + 1, 
                $response['message']
            );
        }
    
        // ---------- JSON PARSING ----------
        $recipeJson = $response['message'];
        $recipeData = json_decode($recipeJson, true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->generateWeakPlaneAccordingToIngredientAndUserText(
                $userTxt,
                $gradientItem_string,
                $repeat + 1,
                "Invalid JSON format received from AI"
            );
        }
    
        // ---------- VALIDATION ----------
        if (!is_array($recipeData)) {
            return [
                'success' => false,
                'message' => 'AI returned non-array JSON',
                'data' => null
            ];
        }
    
        if (
            !isset($recipeData['week_plan']) ||
            !isset($recipeData['items_to_buy']) ||
            !is_array($recipeData['week_plan']) ||
            !is_array($recipeData['items_to_buy'])
        ) {
            return [
                'success' => false,
                'message' => 'Invalid AI structure. Expected week_plan and items_to_buy.',
                'data' => $recipeData
            ];
        }
        foreach ($recipeData['week_plan'] as $i => $plan) {
            if (
                !isset($plan['day_of_week']) ||
                !isset($plan['slot']) ||
                !isset($plan['recipe'])
            ) {
                return [
                    'success' => false,
                    'message' => "week_plan[$i] is missing required fields (day_of_week, slot, recipe)",
                    'data' => $plan
                ];
            }
        
            // validate recipe object
            $recipe = $plan['recipe'];
        
            if (
                !isset($recipe['title']) ||
                !isset($recipe['instructions']) ||
                !isset($recipe['gradients']) ||
                !is_array($recipe['gradients'])
            ) {
                return [
                    'success' => false,
                    'message' => "week_plan[$i].recipe structure is invalid",
                    'data' => $recipe
                ];
            }
        
            // validate each gradient inside recipe
            foreach ($recipe['gradients'] as $g => $grad) {
                if (
                    !isset($grad['item_name']) ||
                    !isset($grad['quantity']) ||
                    !isset($grad['unit_id'])
                ) {
                    return [
                        'success' => false,
                        'message' => "week_plan[$i].recipe.gradients[$g] is missing required fields",
                        'data' => $grad
                    ];
                }
            }
        }

    
        // ---------- SUCCESS ----------
        return [
            'success' => true,
            'message' => 'Weekly plan generated successfully',
            'data' => [
                'week_plan'   => $recipeData['week_plan'],
                'items_to_buy' => $recipeData['items_to_buy']
            ]
        ];
    }


}   


