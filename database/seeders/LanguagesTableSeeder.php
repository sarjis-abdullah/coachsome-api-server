<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            ["id" => 1, "name" => "Afrikaans", "t_key" => "lang_afrikaans"],
            ["id" => 2, "name" => "Arabic", "t_key" => "lang_arabic"],
            ["id" => 3, "name" => "Bengali", "t_key" => "lang_bengali"],
            ["id" => 4, "name" => "Bulgarian", "t_key" => "lang_bulgarian"],
            ["id" => 5, "name" => "Catalan", "t_key" => "lang_catalan"],
            ["id" => 6, "name" => "Cantonese", "t_key" => "lang_cantonese"],
            ["id" => 7, "name" => "Croatian", "t_key" => "lang_croatian"],
            ["id" => 8, "name" => "Czech", "t_key" => "lang_czech"],
            ["id" => 9, "name" => "Danish", "t_key" => "lang_danish"],
            ["id" => 10, "name" => "Dutch", "t_key" => "lang_dutch"],
            ["id" => 11, "name" => "Lithuanian", "t_key" => "lang_lithuanian"],
            ["id" => 12, "name" => "Malay", "t_key" => "lang_malay"],
            ["id" => 13, "name" => "Malayalam", "t_key" => "lang_malayalam"],
            ["id" => 14, "name" => "Panjabi", "t_key" => "lang_panjabi"],
            ["id" => 15, "name" => "Tamil", "t_key" => "lang_tamil"],
            ["id" => 16, "name" => "English", "t_key" => "lang_english"],
            ["id" => 17, "name" => "Finnish", "t_key" => "lang_finnish"],
            ["id" => 18, "name" => "French", "t_key" => "lang_french"],
            ["id" => 19, "name" => "German", "t_key" => "lang_german"],
            ["id" => 20, "name" => "Greek", "t_key" => "lang_greek"],
            ["id" => 21, "name" => "Hebrew", "t_key" => "lang_Hebrew"],
            ["id" => 22, "name" => "Hindi", "t_key" => "lang_hindi"],
            ["id" => 23, "name" => "Hungarian", "t_key" => "lang_hungarian"],
            ["id" => 24, "name" => "Indonesian", "t_key" => "lang_indonesian"],
            ["id" => 25, "name" => "Italian", "t_key" => "lang_italian"],
            ["id" => 26, "name" => "Japanese", "t_key" => "lang_japanese"],
            ["id" => 27, "name" => "Javanese", "t_key" => "lang_javanese"],
            ["id" => 28, "name" => "Korean", "t_key" => "lang_korean"],
            ["id" => 29, "name" => "Norwegian", "t_key" => "lang_norwegian"],
            ["id" => 30, "name" => "Polish", "t_key" => "lang_polish"],
            ["id" => 31, "name" => "Portuguese", "t_key" => "lang_portuguese"],
            ["id" => 32, "name" => "Romanian", "t_key" => "lang_romanian"],
            ["id" => 33, "name" => "Russian", "t_key" => "lang_russian"],
            ["id" => 34, "name" => "Serbian", "t_key" => "lang_serbian"],
            ["id" => 35, "name" => "Slovak", "t_key" => "lang_slovak"],
            ["id" => 36, "name" => "Slovene", "t_key" => "lang_slovene"],
            ["id" => 37, "name" => "Spanish", "t_key" => "lang_spanish"],
            ["id" => 38, "name" => "Swedish", "t_key" => "lang_swedish"],
            ["id" => 39, "name" => "Telugu", "t_key" => "lang_telugu"],
            ["id" => 40, "name" => "Thai", "t_key" => "lang_thai"],
            ["id" => 41, "name" => "Turkish", "t_key" => "lang_turkish"],
            ["id" => 42, "name" => "Ukrainian", "t_key" => "lang_ukrainian"],
            ["id" => 43, "name" => "Vietnamese", "t_key" => "lang_vietnamese"],
            ["id" => 44, "name" => "Welsh", "t_key" => "lang_welsh"],
            ["id" => 45, "name" => "Signlanguage", "t_key" => "lang_signlanguage"],
            ["id" => 46, "name" => "Algerian", "t_key" => "lang_algerian"],
            ["id" => 47, "name" => "Aramaic", "t_key" => "lang_aramaic"],
            ["id" => 48, "name" => "Armenian", "t_key" => "lang_armenian"],
            ["id" => 49, "name" => "Berber", "t_key" => "lang_berber"],
            ["id" => 50, "name" => "Burmese", "t_key" => "lang_burmese"],
            ["id" => 51, "name" => "Bosnian", "t_key" => "lang_bosnian"],
            ["id" => 52, "name" => "Brazilian", "t_key" => "lang_brazilian"],
            ["id" => 53, "name" => "Bulgarian", "t_key" => "lang_bulgarian"],
            ["id" => 54, "name" => "Cypriot", "t_key" => "lang_cypriot"],
            ["id" => 55, "name" => "Corsica", "t_key" => "lang_corsica"],
            ["id" => 56, "name" => "Creole", "t_key" => "lang_creole"],
            ["id" => 57, "name" => "Scottish", "t_key" => "lang_scottish"],
            ["id" => 58, "name" => "Egyptian", "t_key" => "lang_egyptian"],
            ["id" => 59, "name" => "Esperanto", "t_key" => "lang_esperanto"],
            ["id" => 60, "name" => "Estonian", "t_key" => "lang_estonian"],
            ["id" => 61, "name" => "Finn", "t_key" => "lang_finn"],
            ["id" => 62, "name" => "Flemish", "t_key" => "lang_flemish"],
            ["id" => 63, "name" => "Georgian", "t_key" => "lang_georgian"],
            ["id" => 64, "name" => "Hawaiian", "t_key" => "lang_hawaiian"],
            ["id" => 65, "name" => "Indonesian", "t_key" => "lang_indonesian"],
            ["id" => 66, "name" => "Inuit", "t_key" => "lang_inuit"],
            ["id" => 67, "name" => "Irish", "t_key" => "lang_irish"],
            ["id" => 68, "name" => "Icelandic", "t_key" => "lang_icelandic"],
            ["id" => 69, "name" => "Latin", "t_key" => "lang_iatin"],
            ["id" => 70, "name" => "Mandarin", "t_key" => "lang_mandarin"],
            ["id" => 71, "name" => "Nepalese", "t_key" => "lang_nepalese"],
            ["id" => 72, "name" => "Sanskrit", "t_key" => "lang_sanskrit"],
            ["id" => 73, "name" => "Tagalog", "t_key" => "lang_tagalog"],
            ["id" => 74, "name" => "Tahitian", "t_key" => "lang_tahitian"],
            ["id" => 75, "name" => "Tibetan", "t_key" => "lang_tibetan"],
            ["id" => 76, "name" => "Gypsy", "t_key" => "lang_gypsy"],
            ["id" => 77, "name" => "Wu", "t_key" => "lang_wu"],
        ];

        // Language
        \App\Entities\Language::insert($languages);

        // Translation
        if(!\App\Entities\Translation::where('group', 'language')->first()){
            foreach ($languages as $language) {
                $t = new \App\Entities\Translation();
                $t->status = 1;
                $t->group = 'language';
                $t->page_name = 'Profile';
                $t->gl_key = $language['t_key'];
                $t->en_value = $language['name'];
                $t->save();
            }
        }

    }
}
