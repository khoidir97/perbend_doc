<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordPegawai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pegawai:reset-passwords {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $hash = Hash::make($this->argument('password')); // hash sekali saja

        $affected = DB::table('pegawai')
            ->where('pgw_role', '!=', 0) // kecualikan admin kalau perlu
            ->update([
                'pgw_password' => $hash,
                // 'must_change_password' => true,
            ]);

        $this->info("$affected pegawai diperbarui.");
    }
}
