<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\Admin\TaskController as TaskAdminController;

// Import your controllers
use App\Http\Controllers\Autho\AuthController;
use App\Http\Controllers\Expense\ExpenseController;
use App\Http\Controllers\Householder\HouseholderController;
use App\Http\Controllers\MealPlan\MealPlanController;
use App\Http\Controllers\pantryItem\PantryItemController;
use App\Http\Controllers\Recipe\RecipeController;
use App\Http\Controllers\ShoppingList\ShoppingListController;


// Versioning (v1 API)
Route::group(["prefix" => "v0.1", "middleware" => "auth:api"], function () {



    // === Expense Routes ===
    Route::group(["prefix" => "expense"], function () {
        Route::get("/", [ExpenseController::class, "index"]);
        Route::post("/", [ExpenseController::class, "store"]);
        Route::put("/{id}", [ExpenseController::class, "update"]);
        Route::delete("/{id}", [ExpenseController::class, "destroy"]);
    });

    // === Householder Routes ===
    Route::group(["prefix" => "householder"], function () {
        Route::get("/", [HouseholderController::class, "index"]);
        Route::post("/", [HouseholderController::class, "store"]);
    });

    // === Meal Plan Routes ===
    Route::group(["prefix" => "meal-plan"], function () {
        Route::get("/", [MealPlanController::class, "index"]);
        Route::post("/", [MealPlanController::class, "store"]);
    });

    // === Pantry Item Routes ===
    Route::group(["prefix" => "pantry"], function () {
        Route::get("/", [PantryItemController::class, "index"]);
        Route::post("/", [PantryItemController::class, "store"]);
    });

    // === Recipes Routes ===
    Route::group(["prefix" => "recipe"], function () {
        Route::get("/", [RecipeController::class, "index"]);
        Route::post("/", [RecipeController::class, "store"]);
    });

    // === Shopping List Routes ===
    Route::group(["prefix" => "shopping"], function () {
        Route::get("/", [ShoppingListController::class, "index"]);
        Route::post("/", [ShoppingListController::class, "store"]);
    });

});
    Route::post("/login", [AuthController::class, "login"]);
    Route::post("/register", [AuthController::class, "register"]);
    Route::post("/logout", [AuthController::class, "logout"]);
