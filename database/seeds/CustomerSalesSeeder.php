<?php

use Illuminate\Database\Seeder;

class CustomerSalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customers = ['BRI', 'BJB', 'PT Timah', 'Allo Bank', 'PNM', 'Bank DKI', 'Jamkrindo Syariah', 'Askrindo'];
        foreach ($customers as $customer) {
            \App\MCustomer::firstOrCreate(['name' => $customer], ['status' => 1]);
        }

        $sales = ['Putri', 'Sigit', 'Wandi'];
        foreach ($sales as $s) {
            \App\MSales::firstOrCreate(['name' => $s], ['status' => 1]);
        }
    }
}
