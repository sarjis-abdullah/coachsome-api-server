<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = "INSERT INTO `users` (`id`, `uuid`, `first_name`, `last_name`, `email`,`user_name`, `password`, `email_verified_at`, `verified`, `agree_to_terms`, `deleted_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'b8e14f37-8854-4f70-aff0-044e569729cc', 'Hafijur', 'Rahman', 'hafij.to@gmail.com','hafijur.rahman', '$2y$10\$I9NXvinOhfq9zvdwr2iQ2.ROouatizP7y4Bb9/H83ysiDNviIlXb2', NULL, 1, 1, NULL, NULL, '2019-12-23 08:51:33', '2019-12-23 08:51:55'),
(2, '0b184a02-ccde-493b-8025-626f9bea080c', 'Hafijur', 'Rahman', 'hafijur092@gmail.com','hafijur.rahman-1', NULL, NULL, 0, 1, NULL, NULL, '2019-12-23 09:19:29', '2019-12-23 09:19:29'),
(3, 'e4846fa6-0a35-4c24-9218-280391a798e8', 'Kasper', 'Tikjob', 'mail@kasper.nu','kasper.tikjob', '$2y$10\$aQUt7JndnTR3KpGMOlIZz.B1DZHroiH66p5UV7Q6mS8c3idERlnm.', NULL, 1, 1, NULL, NULL, '2019-12-23 10:30:51', '2019-12-23 10:40:06'),
(4, '9288e658-5aa3-4338-9f60-51b1226bc466', 'alexander', 'geertsen', 'alexandergeertsen@gmail.com','alexander.geertsen', '$2y$10\$KFRCxkctTsjBexrQqEf0LO.rrF98h4ujt/A5GpaWDoaAZkB2.cLyi', NULL, 1, 1, NULL, NULL, '2019-12-23 10:57:25', '2019-12-23 10:58:03'),
(5, '31506692-e3fb-4010-86cd-a5cf9a54436d', 'Martin', 'Johansson', 'martin@coachsome.com','martin.johansson', '$2y$10\$bBoZzo2ygSKcZiLRrHon/uQoIZtArKIrWyoNnJtl8qzshQkYeO/My', NULL, 1, 1, NULL, NULL, '2019-12-23 10:59:09', '2019-12-23 11:03:26'),
(6, '4cf02e68-c60b-4d58-825d-1ad08e2cdf84', 'Ehsan', 'Ahmed', 'ehsanahmedweb@gmail.com','ehsan.ahmed', '$2y$10\$SGhT95HXux9AD.iE9wp4veBkujyuro.QdZhwEtIbXT9q3oeangXBW', NULL, 1, 1, NULL, NULL, '2019-12-23 11:02:16', '2019-12-23 11:02:35'),
(7, '58091c33-ce5a-4f6c-a123-3f4d0379418e', 'Peter', 'Moller', 'petermoller12@hotmail.com','peter.moller', '$2y$10\$rE2.TjliNFwvKOOK9QQkqOBnTbw1TTXDoy55kQlA208vg6OubDdiC', NULL, 1, 1, NULL, NULL, '2019-12-23 11:02:58', '2019-12-23 11:03:18')";
        \DB::select($sql);
    }
}
