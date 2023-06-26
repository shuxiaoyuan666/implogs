<?php
/**
 *......................我佛慈悲......................
 *                       _oo0oo_
 *                      o8888888o
 *                      88" . "88
 *                      (| -_- |)
 *                      0\  =  /0
 *                    ___/`---'\___
 *                  .' \\|     |// '.
 *                 / \\|||  :  |||// \
 *                / _||||| -卍-|||||- \
 *               |   | \\\  -  /// |   |
 *               | \_|  ''\---/''  |_/ |
 *               \  .-\__  '-'  ___/-. /
 *             ___'. .'  /--.--\  `. .'___
 *          ."" '<  `.___\_<|>_/___.' >' "".
 *         | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *         \  \ `_.   \_ __\ /__ _/   .-` /  /
 *     =====`-.____`.___ \_____/___.-`___.-'=====
 *                       `=---='
 *
 *..................佛祖开光 ,永无BUG...................
 *
 * Description: imp日志中间件
 * Author: Shuxiaoyuan
 * Email: sxy@shuxiaoyuan.com
 * DateTime: 2020/1/17 15:43
 */

namespace Shuxiaoyuan666\Implogs\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ImpRequestLogMiddleware
{
    public function handle($request, Closure $next)
    {
        $start_memory = memory_get_usage();

        try {
            // 如果需要拆分，可以用前置(ImpBeforeRequestLogMiddleware)、后置(ImpAfterRequestLogMiddleware)中间件来实现
            $input = [
                'header' => $request->headers->all() ?? [],
                'body'   => $request->all() ?? [],
            ];

            // 设置唯一请求ID
            if (defined('REQUEST_UNIQUE_UUID')) {
                $request_id = constant('REQUEST_UNIQUE_UUID');
            } else {
                $request_id = uniqid(md5(microtime(true) . mt_rand(100000, 999999)), true);
                define('REQUEST_UNIQUE_UUID', $request_id);
            }

            $user_token = $request->header(config('implog.request_log.user_token'), '游客，未登录或无token用户');

            $data = [
                'user_token'   => $user_token,
                'request_id'   => $request_id,
                'start_time'   => constant('LARAVEL_START'),
                'start_memory' => $start_memory,
                'path'         => $request->path(),
                'uri'          => $request->getRequestUri(),
                'method'       => $request->method(),
                'ip'           => json_encode($request->getClientIps(), JSON_UNESCAPED_UNICODE),
                'input'        => json_encode($input, JSON_UNESCAPED_UNICODE),
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            $response = $next($request);

            $data['end_time']   = microtime(true);
            $data['run_time']   = $data['end_time'] - $data['start_time'];
            $data['output']     = json_encode(
                [
                    'status'   => $response->getStatusCode() ?: 0,
                    'response' => $response->getContent() ?: ''
                ],
                JSON_UNESCAPED_UNICODE);
            $data['updated_at'] = date('Y-m-d H:i:s');

            $host = $request->getHost();

            // 避开部分域名，比如心跳检测等
            if (in_array($host, config('implog.request_log.ignore_host'))) {
                return $response;
            }

            // 避开部分路由，正则
            $path = $request->path();

            $data['end_memory'] = memory_get_usage();
            $data['max_memory'] = memory_get_peak_usage();
            Redis::rpush(config('implog.request_log.redis_key'), json_encode($data, JSON_UNESCAPED_UNICODE));

            return $response;
        } catch (\Exception $exception) {
            Log::error('请求日志入Redis失败', [
                'message' => $exception->getMessage(),
                'data'    => $data,
            ]);
        }

        return $response;
    }
}
