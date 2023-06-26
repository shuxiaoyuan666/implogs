<?php

namespace Shuxiaoyuan666\Implogs\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class ImpRequestLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ImpRequestLog {number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '外部请求本系统日志';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1M');

        $key = config('implog.request_log.max_write_number');

        if (!$default_len = $this->argument('number')) {
            $default_len = config('implog.request_log.read_number');
        }

        $len = Redis::llen($key);

        if (!$len) {
            return 'success';
        }

        if ($len >= $default_len) {
            $trim_len = $default_len;
        } else {
            $trim_len = $len;
        }

        $dataResponse = Redis::pipeline(function ($pipeline) use ($key, $trim_len) {
            for ($i = 0; $i < $trim_len; $i++) {
                $pipeline->lpop($key);
            }
        });

        foreach ($dataResponse as &$value) {
            $value = json_decode($value, true);
        }

        $table_name = 'imp_request_logs_' . date('Ym');
        $this->createTable($table_name);

        try {
            DB::table($table_name)->insert($dataResponse);
        } catch (\Exception $exception) {
            Log::error('请求日志入库失败', [
                'message' => $exception->getMessage(),
                'data'    => $dataResponse,
            ]);
        }

        return 'success';
    }

    public function createTable($table_name)
    {
        if (Schema::hasTable($table_name)) {
            return true;
        }

        Schema::create($table_name, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('user_token')->nullable()->comment('登录用户token');
            $table->string('request_id', 60)->nullable()->comment('请求唯一标识符');
            $table->string('start_time', 15)->nullable()->comment('开始时间');
            $table->string('end_time', 15)->nullable()->comment('结束时间');
            $table->string('run_time', 10)->nullable()->comment('运行时间');
            $table->string('start_memory', 15)->nullable()->comment('开始分配内存');
            $table->string('end_memory', 15)->nullable()->comment('结束分配内存');
            $table->string('max_memory', 15)->nullable()->comment('最大分配内存');
            $table->text('ip')->nullable()->comment('IP');
            $table->text('path')->nullable()->comment('请求路径');
            $table->string('method', 10)->nullable()->comment('请求方法');
            $table->text('uri')->nullable()->comment('请求uri');
            $table->json('input')->nullable()->comment('入参');
            $table->json('output')->nullable()->comment('出参');
            $table->timestamps();

            $table->unique('request_id');
        });

        return true;
    }
}
