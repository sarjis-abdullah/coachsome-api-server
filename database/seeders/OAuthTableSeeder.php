<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OAuthTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = "INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
	        (1, NULL, 'Laravel Personal Access Client', 'ua2VMo078nxd1gj8RTLSDKA7HEk0PcUAH4Hcmr17', 'http://localhost', 1, 0, 0, '2019-12-24 05:34:50', '2019-12-24 05:34:50'),
	        (2, NULL, 'Laravel Password Grant Client', 'rMxQ7fM9fCjinPoJyfIpWUg1bRgODPPDYRcKMPkn', 'http://localhost', 0, 1, 0, '2019-12-24 05:34:51', '2019-12-24 05:34:51')";

        $sql2 = "INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
	        (1, 1, '2019-12-24 05:34:51', '2019-12-24 05:34:51')";

        \DB::select($sql);
        \DB::select($sql2);

    }
}
