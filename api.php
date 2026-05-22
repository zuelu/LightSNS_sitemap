<?php

ob_start();

require_once dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'sitemap_lib.php';

use LightSNS\Foundation\Response;

$action = strtolower((string) ($_REQUEST['action'] ?? 'summary'));

try {
    if ($action === 'rebuild') {
        if (!czzz_sitemap_is_admin()) {
            Response::json([
                'code' => 0,
                'msg' => '只有管理员可以重建网站地图',
            ]);
        }

        $summary = czzz_sitemap_run_rebuild('admin', true);
        Response::json([
            'code' => 1,
            'msg' => '网站地图重建成功',
            'data' => $summary,
            'runtime' => czzz_sitemap_load_runtime_status(),
        ]);
    }

    if ($action === 'cron_rebuild') {
        $providedSalt = (string) ($_REQUEST['salt'] ?? $_REQUEST['key'] ?? '');
        if (!czzz_sitemap_is_valid_schedule_secret($providedSalt)) {
            http_response_code(403);
            Response::json([
                'code' => 0,
                'msg' => '密钥无效，已拦截本次自动更新请求',
            ]);
        }

        $cronCheck = czzz_sitemap_can_run_cron_rebuild();
        if (empty($cronCheck['allowed'])) {
            http_response_code((int) ($cronCheck['status'] ?? 403));
            if (!empty($cronCheck['retry_after'])) {
                header('Retry-After: ' . (int) $cronCheck['retry_after']);
            }
            Response::json([
                'code' => 0,
                'msg' => (string) ($cronCheck['msg'] ?? '当前不允许自动更新'),
            ]);
        }

        $summary = czzz_sitemap_run_rebuild('cron', true);
        Response::json([
            'code' => 1,
            'msg' => '网站地图自动重建成功',
            'data' => $summary,
            'runtime' => czzz_sitemap_load_runtime_status(),
            'mode' => 'cron',
        ]);
    }

    if (in_array($action, ['html', 'xml', 'txt'], true)) {
        czzz_sitemap_serve($action, (string) ($_GET['name'] ?? 'index'));
    }

    $context = czzz_sitemap_load_public_context();
    Response::json([
        'code' => 1,
        'msg' => 'ok',
        'data' => $context['summary'],
        'runtime' => $context['runtime'],
        'is_admin' => czzz_sitemap_is_admin(),
    ]);
} catch (Throwable $e) {
    Response::json([
        'code' => 0,
        'msg' => $e->getMessage(),
    ]);
}
