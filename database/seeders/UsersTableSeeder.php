<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

//Models:
use App\Models\Account;
use App\Models\Company;
use App\Models\CompanyAccount;
use App\Models\CompanySetting;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\Workplace;

class UsersTableSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        //Default Admin:
        $data_user = [
            'name' => 'Admin', 
            'surname' => 'Default', 
            'email' => 'admin@default.com', 
            'password' => bcrypt('12345678'),
            'nickname' => 'superadmin',
            'isAdmin' => 1,
            'status' => 1,
            'remember_token' => Str::random(10)
        ];
        $user = User::create($data_user)->assignRole('Super Admin');

        //Creamos empresa piloto:
        $data_company = [
            'name' => 'ACME',
            'slug' => 'acme',
            'nif' => '00000000A',
            'created_by' => 1,
            'updated_by' => 1
        ];
        $company = Company::create($data_company);

        //Creamos cuenta de la empresa:
        $account = Account::first();
        $data_account = [
            'company_id' => $company->id,
            'account_id' => $account->id,
            'start_date' => date('Y-m-d'),
            'end_date' => config('constants.UNDEFINED_DATE_')
        ];
        CompanyAccount::create($data_account);

        //Creamos centro de trabajo principal de la empresa:
        $data_workplace = [
            'name' => 'ACME Headquarters',
            'slug' => 'acme-headquarters',
            'company_id' => $company->id,
            'featured' => true
        ];
        Workplace::create($data_workplace);

        //Vínculo usuario - empresa:
        $data_user_company = [
            'user_id' => $user->id, 
            'company_id' => $company->id
        ];
        UserCompany::create($data_user_company);

        //Configuraciones de la empresa:
        $data_setting = [
            'company_id' => $company->id
        ];
        CompanySetting::create($data_setting);
    }
}
