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
 * Description: imp日志配置
 * Author: Shuxiaoyuan
 * Email: sxy@shuxiaoyuan.com
 * Website: https://shuxiaoyuan.com
 * DateTime: 2023/6/25 16:25
 */

return [
    /**
     * 需要记录的日志种类
     */
    'logs_type'   => [
        // 外部请求本系统日志
        'request_log',

        // 本系统请求第三方日志
        'api_interface_request_log',

        // 错误日志
        'error_log'
    ],

    //    /**
    //     * 默认记录的渠道: command 或 队列
    //     * command: 通过将记录塞入Redis中，然后脚本每分钟执行相应条数来消费
    //     * 队列:
    //     */
    //    'log_channel' => env('IMP_LOG_CHANNEL', 'command'),

    /**
     * redis_key Redis键名
     * read_number 从Redis每次取出的数量
     * max_write_number Redis最大的存储数量
     */
    'request_log' => [
        'redis_key'        => 'imp:request_log',
        'read_number'      => 1000,
        'max_write_number' => 100000,

        // 忽略的接口
        'ignore_api'       => [
            'admin'
        ],
    ],

    'api_interface_request_log' => [
        'redis_key'        => 'imp:api_interface_request_log',
        'read_number'      => 1000,
        'max_write_number' => 100000
    ],

    'error_log' => [
        'redis_key'        => 'imp:error_log',
        'read_number'      => 1000,
        'max_write_number' => 100000
    ],
];