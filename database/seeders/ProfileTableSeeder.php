<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProfileTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = "INSERT INTO `profiles` (`id`, `user_id`, `image`, `profile_name`, `about_me`, `mobile_no`, `mobile_code`, `birth_day`, `personalized_url`, `social_acc_fb_link`, `social_acc_twitter_link`, `social_acc_instagram_link`, `tag_list_id`, `category_list_id`, `language_list_id`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Hafijur Rahman', NULL, NULL, NULL, NULL, 'https://dev.coachsome.com/Hafijur.Rahman', NULL, NULL, NULL, NULL, NULL, NULL, '2019-12-23 08:51:33', '2019-12-23 08:51:33'),
(2, 2, NULL, 'Hafijur Rahman', NULL, NULL, NULL, NULL, 'https://dev.coachsome.com/Hafijur.Rahman', NULL, NULL, NULL, NULL, NULL, NULL, '2019-12-23 09:19:29', '2019-12-23 09:19:29'),
(3, 3, 'id_3_1577101566.png', 'Kasper Tikjob', '<p>Hejsa</p>', '', '', NULL, 'https://dev.coachsome.com/Kasper.Tikjob', '', '', '', '[]', '[]', '[]', '2019-12-23 10:30:51', '2019-12-23 10:46:06'),
(4, 4, NULL, 'alexander geertsen', '', '', '', '1987-05-30', 'https://dev.coachsome.com/alexander.geertsen', '', '', '', '[]', '[10]', '[]', '2019-12-23 10:57:25', '2019-12-23 11:10:06'),
(5, 5, 'id_5_1577102651.png', 'Martin Johansson', '', '', '', NULL, 'https://dev.coachsome.com/Martin.Johansson', '', '', '', '[]', '[]', '[]', '2019-12-23 10:59:09', '2019-12-23 11:04:19'),
(6, 6, 'id_6_1577102768.png', 'Ehsan Ahmed', '', '0000000000', '+45', '2019-12-17', 'https://dev.coachsome.com/Ehsan.Ahmed', '', '', '', '[1,2,3]', '[2,3,4]', '[1,2]', '2019-12-23 11:02:16', '2019-12-23 11:06:54'),
(7, 7, 'id_7_1577103209.png', 'Peter Møller', '<p>Mit navn er Peter Møller. Jeg er 25 år og fuldtidsprofessionel basketballspiller for Svendborg Rabbits samt indeover det danske a-landshold. Jeg er født i København og opvokset i forstaden Værløse, det meste af mit liv. Hele min basketball opvækst kommer fra Værløse Basketball Klub, hvor jeg spillede fra jeg var 8-18år. I gymnasietiden gik jeg på Falkonergårdens Gymnasium på Frederiksberg. Efter min gymnasietid tog jeg 4 år til USA, hvor jeg spillede for Liberty University &amp; Metropolitan State University of Denver. Her tog jeg samtidigt min bachelor i International Business. I min tid i USA begyndte jeg at læse rigtigt mange bøger, som gjorde, at jeg lærte rigtigt mange nye ting på kort tid. Jeg blev helt besat af at lære nye ting og en helt ny verden åbnede sig for mig. Jeg fandt gennem læsning ud af, at jeg havde en stor interesse for iværksætteri. Det blev klart for mig, at jeg ville bruge de næste mange år på at tage stor risiko i mit liv og kaste mig ud i diverse iværksætterprojekter. Derfor har jeg de sidste 2 år brugt på at bruge alt min \"fritid\" på iværksætteri, når jeg ikke spiller basketball. </p>', '50186028', '+45', '1994-03-31', 'https://dev.coachsome.com/petermoller', 'https://www.facebook.com/petermoller.net', 'https://twitter.com/peter_moller12', 'https://www.instagram.com/petermoller12/', '[]', '[4]', '[1,2]', '2019-12-23 11:02:58', '2019-12-23 12:21:42')";
        \DB::select($sql);
    }
}
