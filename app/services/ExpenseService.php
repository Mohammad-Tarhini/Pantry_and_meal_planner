<?php
class ExpenseService{

    public function EnterInvoiceService($date,$user){
        


        $expense=new Expense();
        

        foreach($data[$items] as $item){
            $expenseItem=new ExpenseItem();

            $itemData={
                'name':$data['name'],
                'quantity';$data['quantity'].
                'unit';$data['unit'],
                'expiry_date':$data['expire_date'],
                'location':$data['loction']

            }

            // 'name' => 'required|string',
            // 'quantity' => 'required|int',
            // 'unit' => 'required|exists:units,name',
            // 'expiry_date' => 'required|date',
            // 'location' => 'required|string',
        }

    }
}




?>