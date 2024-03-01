<?php

namespace Database\Seeders;

use App\Models\MainSettings;
use App\Models\MsEntities;
use App\Models\MsEntityFields;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // MainSettings::create([
        //     "account_id" => "",
        //     "ms_token" => "",
        //     "app_id" => "",
        //     "login" => "",
        //     "password" => "",
        //     "is_activate" => ""
        // ]);

        $msEntities = [
            (object)[
                'keyword' => 'demand',
                'name_RU' => 'Отгрузка',
                'fields' => [
                    ['keyword' => 'agent', 'name_RU' => 'Имя контрагента', 'expand_filter' => 'agent'],
                    ['keyword' => 'name', 'name_RU' => 'Название документа', 'expand_filter' => null],
                    ['keyword' => 'organization', 'name_RU' => 'Организация', 'expand_filter' => 'organization'],
                    ['keyword' => 'deliveryPlannedMoment', 'name_RU' => 'Планируемая дата отгрузки', 'expand_filter' => null],
                    ['keyword' => 'salesChannel', 'name_RU' => 'Канал продаж', 'expand_filter' => 'salesChannel'],
                    ['keyword' => 'rate', 'name_RU' => 'Валюта', 'expand_filter' => "rate.currency"],
                    ['keyword' => 'store', 'name_RU' => 'Склад', 'expand_filter' => 'store'],
                    ['keyword' => 'contract', 'name_RU' => 'Договор', 'expand_filter' => 'contract'],
                    ['keyword' => 'project', 'name_RU' => 'Проект', 'expand_filter' => 'project'],
                    ['keyword' => 'shipmentAddress', 'name_RU' => 'Адрес доставки', 'expand_filter' => null],
                    ['keyword' => 'description', 'name_RU' => 'Комментарий', 'expand_filter' => null],
                    ['keyword' => 'state', 'name_RU' => 'Статус документа', 'expand_filter' => 'state'],
                    ['keyword' => 'sum', 'name_RU' => 'Общая сумма товаров', 'expand_filter' => null],
                    ['keyword' => 'positions', 'name_RU' => 'Список товаров', 'expand_filter' => 'positions.assortment'],
                ],
            ],
            // (object)[
            //     'keyword' => 'customerorder',
            //     'name_RU' => 'Заказ покупателя',
            //     'fields' => [
            //         ['keyword' => 'agent', 'name_RU' => 'Имя контрагента', 'expand_filter' => 'agent'],
            //         ['keyword' => 'name', 'name_RU' => 'Название документа', 'expand_filter' => null],
            //     ],
            // ],
        ];
        
        foreach ($msEntities as $entityItem) {
            $entity = MsEntities::create([
                'keyword' => $entityItem->keyword,
                'name_RU' => $entityItem->name_RU
            ]);
            
            $fields = [];
            foreach ($entityItem->fields as $field) {
                $fields[] = new MsEntityFields($field);
            }
            
            $entity->fields()->createMany($entityItem->fields);
        }
    }
}
