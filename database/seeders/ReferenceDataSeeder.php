<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['AF', 'Afghanistan'], ['AL', 'Albania'], ['DZ', 'Algeria'], ['AD', 'Andorra'],
            ['AO', 'Angola'], ['AR', 'Argentina'], ['AM', 'Armenia'], ['AU', 'Australia'],
            ['AT', 'Austria'], ['AZ', 'Azerbaijan'], ['BS', 'Bahamas'], ['BH', 'Bahrain'],
            ['BD', 'Bangladesh'], ['BB', 'Barbados'], ['BY', 'Belarus'], ['BE', 'Belgium'],
            ['BZ', 'Belize'], ['BJ', 'Benin'], ['BT', 'Bhutan'], ['BO', 'Bolivia'],
            ['BA', 'Bosnia and Herzegovina'], ['BW', 'Botswana'], ['BR', 'Brazil'],
            ['BN', 'Brunei'], ['BG', 'Bulgaria'], ['BF', 'Burkina Faso'], ['BI', 'Burundi'],
            ['KH', 'Cambodia'], ['CM', 'Cameroon'], ['CA', 'Canada'], ['CV', 'Cape Verde'],
            ['CL', 'Chile'], ['CN', 'China'], ['CO', 'Colombia'], ['CR', 'Costa Rica'],
            ['HR', 'Croatia'], ['CU', 'Cuba'], ['CY', 'Cyprus'], ['CZ', 'Czech Republic'],
            ['DK', 'Denmark'], ['DJ', 'Djibouti'], ['DO', 'Dominican Republic'],
            ['EC', 'Ecuador'], ['EG', 'Egypt'], ['SV', 'El Salvador'],
            ['EE', 'Estonia'], ['ET', 'Ethiopia'], ['FJ', 'Fiji'], ['FI', 'Finland'],
            ['FR', 'France'], ['GA', 'Gabon'], ['GM', 'Gambia'], ['GE', 'Georgia'],
            ['DE', 'Germany'], ['GH', 'Ghana'], ['GR', 'Greece'], ['GT', 'Guatemala'],
            ['GN', 'Guinea'], ['HT', 'Haiti'], ['HN', 'Honduras'], ['HK', 'Hong Kong'],
            ['HU', 'Hungary'], ['IS', 'Iceland'], ['IN', 'India'], ['ID', 'Indonesia'],
            ['IR', 'Iran'], ['IQ', 'Iraq'], ['IE', 'Ireland'], ['IL', 'Israel'],
            ['IT', 'Italy'], ['JM', 'Jamaica'], ['JP', 'Japan'], ['JO', 'Jordan'],
            ['KZ', 'Kazakhstan'], ['KE', 'Kenya'], ['KP', 'North Korea'], ['KR', 'South Korea'],
            ['KW', 'Kuwait'], ['KG', 'Kyrgyzstan'], ['LV', 'Latvia'], ['LB', 'Lebanon'],
            ['LY', 'Libya'], ['LT', 'Lithuania'], ['LU', 'Luxembourg'], ['MG', 'Madagascar'],
            ['MY', 'Malaysia'], ['MV', 'Maldives'], ['MT', 'Malta'], ['MU', 'Mauritius'],
            ['MX', 'Mexico'], ['MD', 'Moldova'], ['MC', 'Monaco'], ['MN', 'Mongolia'],
            ['MA', 'Morocco'], ['MM', 'Myanmar'], ['NA', 'Namibia'], ['NP', 'Nepal'],
            ['NL', 'Netherlands'], ['NZ', 'New Zealand'], ['NI', 'Nicaragua'], ['NG', 'Nigeria'],
            ['NO', 'Norway'], ['OM', 'Oman'], ['PK', 'Pakistan'], ['PS', 'Palestine'],
            ['PA', 'Panama'], ['PY', 'Paraguay'], ['PE', 'Peru'], ['PH', 'Philippines'],
            ['PL', 'Poland'], ['PT', 'Portugal'], ['QA', 'Qatar'], ['RO', 'Romania'],
            ['RU', 'Russia'], ['RW', 'Rwanda'], ['SA', 'Saudi Arabia'], ['SN', 'Senegal'],
            ['RS', 'Serbia'], ['SG', 'Singapore'], ['SK', 'Slovakia'], ['SI', 'Slovenia'],
            ['SO', 'Somalia'], ['ZA', 'South Africa'], ['ES', 'Spain'], ['LK', 'Sri Lanka'],
            ['SD', 'Sudan'], ['SE', 'Sweden'], ['CH', 'Switzerland'], ['SY', 'Syria'],
            ['TW', 'Taiwan'], ['TJ', 'Tajikistan'], ['TZ', 'Tanzania'], ['TH', 'Thailand'],
            ['TN', 'Tunisia'], ['TR', 'Turkey'], ['TM', 'Turkmenistan'], ['UG', 'Uganda'],
            ['UA', 'Ukraine'], ['AE', 'United Arab Emirates'], ['GB', 'United Kingdom'],
            ['US', 'United States'], ['UY', 'Uruguay'], ['UZ', 'Uzbekistan'],
            ['VE', 'Venezuela'], ['VN', 'Vietnam'], ['YE', 'Yemen'], ['ZM', 'Zambia'],
            ['ZW', 'Zimbabwe'],
        ];

        foreach ($countries as [$code, $name]) {
            DB::table('countries')->insert([
                'id' => Str::uuid()->toString(),
                'code' => $code,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $docCategories = [
            ['Expenses'], ['Sales'], ['Inventory'], ['Loss'],
        ];
        foreach ($docCategories as [$name]) {
            DB::table('document_categories')->insert([
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $docTypes = [
            ['Purchase', '100', 'Expenses', 1, 0],
            ['Sales', '200', 'Sales', 2, 0],
            ['Inventory Count', '300', 'Inventory', 1, 1],
            ['Refund', '220', 'Sales', 1, 0],
            ['Stock Return', '120', 'Expenses', 2, 0],
            ['Loss And Damage', '400', 'Loss', 2, 2],
            ['Proforma', '230', 'Sales', 0, 0],
        ];

        foreach ($docTypes as [$name, $code, $catName, $direction, $editor]) {
            $catId = DB::table('document_categories')->where('name', $catName)->value('id');
            DB::table('document_types')->insert([
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'code' => $code,
                'document_category_id' => $catId,
                'stock_direction' => $direction,
                'editor_type' => $editor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $paymentTypes = [
            ['Cash', 'cash', false, true, true, true, true],
            ['Card', 'card', false, true, false, true, true],
            ['Check', 'check', false, true, false, true, false],
        ];

        foreach ($paymentTypes as [$name, $code, $custReq, $fiscal, $slip, $change, $quick]) {
            DB::table('payment_types')->insert([
                'id' => Str::uuid()->toString(),
                'name' => $name,
                'code' => $code,
                'is_customer_required' => $custReq,
                'is_fiscal' => $fiscal,
                'is_slip_required' => $slip,
                'is_change_allowed' => $change,
                'is_quick_payment' => $quick,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo count($countries) . " countries, " . count($docCategories) . " doc categories, " . count($docTypes) . " doc types, " . count($paymentTypes) . " payment types seeded.\n";
    }
}
