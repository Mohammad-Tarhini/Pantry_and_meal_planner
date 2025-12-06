<?php
class ExpenseService{
    public static function EnterInvoiceService($data,$user){
        $householder=HouseHolder::where('user_id',$user->id)->first();
        if(!$householder){
            throw new \Exception ("is not house holder");
        }
        $expense=new Expense();
        $expense->amount=$data['amount'];
        $expense->date=$data['date'];
        $expense->category=$data['category'];
        $expense->note=$data['note'] ?? null;
        $expense->store=$data['store'] ?? null;
        $expense->receipt_url=$data['receipt_url'] ?? null;
        $expense->householder_id=$householder->id;

        if(!$expense->save()){
            throw new \Exception("The expense couldn't be saved");
        }

        foreach($data['items'] as $itemData){
            $shoppingListItem=ShoppingListItem::where('id',$itemData['item_id'])->first();
            if(!$shoppingListItem){
                throw new \Exception("Shopping list item not found");
            }
            // $shoppingListItem->expense_id=$expense->id;
            // $shoppingListItem->expire_date=$itemData['expire_date'];
            // $shoppingListItem->price=$itemData['price'];
            // $shoppingListItem->location=$itemData['location'];
            $expenseItem = new ExpenseItem();
            $expenseItem->expense_id  = $expense->id;
            $expenseItem->item_id     = $itemData['item_id'];
            $expenseItem->expire_date = $itemData['expire_date'];
            $expenseItem->price       = $itemData['price'];
            $expenseItem->location    = $itemData['location'];
            if (!$expenseItem->save()) {
                throw new \Exception("The expense item couldn't be saved");
            }
            
            $itemData = [
            'name'        => $item['name'],
            'quantity'    => $item['quantity'],
            'unit'        => $item['unit'],
            'expiry_date' => $item['expire_date'],
            'location'    => $item['location'],
            ];
            $result = $this->pantryItemService->AddPantryItem($itemData, $user);
            
            if (!$result) {
            return ResponseTrait::error("Failed to add item to pantry.");
            }
            $updateData = [
            'item_id' => $item['item_id'],
            'isbought' => true
             ];
             $result2 = $this->shopingListService->update($updateData, $user);
            if (!$result2) {
                return ResponseTrait::error("Failed to update shopping list.");
            }


        }

        return $expense;
    }
}




?>