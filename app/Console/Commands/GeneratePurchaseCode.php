<?php

namespace App\Console\Commands;

use App\Models\PurchaseCode;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePurchaseCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate:purchase-code';

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
        $code = strtoupper(Str::random(16));
        PurchaseCode::create(['code' => $code]);
        $this->info("Code généré : $code");
    }
}
