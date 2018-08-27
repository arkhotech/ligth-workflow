<?php

use Illuminate\Database\Seeder;

class TraySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
         //
        DB::table('trays')->insert([
            array("tray_name" => "Bandeja 1",
                "description" => "Bandeja de prueba" ),
            array("tray_name" => "Bandeja 2",
                "description" => "Bandeha 2")
        ]);
        
        DB::table('role_tray')->insert([
                array('role_id'=> 1, "tray_id" => 1),
                array('role_id'=> 1, "tray_id" => 2)
                ]);
    }
}
