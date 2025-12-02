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

    public function getRecipeFromGroq($userTxt, $gradientItem_string, $repeat = 1, $errormsg = "")
    {
        if ($repeat > 3) {
            return [
                'success' => false,
                'message' => 'Exceeded maximum retry attempts for AI response',
                'data' => null
            ];
        }
    
        $text1 = "Using the following pantry items and try to use how have approuch expire_date: " . $gradientItem_string . 
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

}   


