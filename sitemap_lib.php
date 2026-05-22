<?php

use LightSNS\BBS\BBSRepository;
use LightSNS\Contexts\Bbs\Read\BbsReadModel;
use LightSNS\Contexts\Post\Read\PostListReadModel;
use LightSNS\Contexts\Post\Read\PostReadModel;
use LightSNS\Contexts\Tag\Read\TagReadModel;
use LightSNS\Foundation\DB;
use LightSNS\Foundation\Site;
use LightSNS\Shared\Auth;
use LightSNS\Shared\Options;
use LightSNS\Shared\Power;

function czzz_sitemap_module_id(): string
{
    return 'czzz-pc-page-sitemap';
}

function czzz_sitemap_module_dir(): string
{
    return __DIR__;
}

function czzz_sitemap_runtime_dir(): string
{
    return czzz_sitemap_module_dir() . DIRECTORY_SEPARATOR . 'runtime';
}

function czzz_sitemap_runtime_file(string $name): string
{
    return czzz_sitemap_runtime_dir() . DIRECTORY_SEPARATOR . ltrim($name, '/');
}

function czzz_sitemap_module_url(string $path = ''): string
{
    $base = Site::moduleUrl() . '/pc/page/' . czzz_sitemap_module_id();
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function czzz_sitemap_page_route(): string
{
    return '/' . ltrim((string) (Options::module('czzz_sitemap_pc_page_route') ?: '/sitemap'), '/');
}

function czzz_sitemap_module_page_url(): string
{
    return Site::homeUrl('/module/pc/page/' . czzz_sitemap_module_id() . '/page.php');
}

function czzz_sitemap_demo_url(): string
{
    return Site::homeUrl(czzz_sitemap_page_route());
}

function czzz_sitemap_build_dir(string $path = ''): string
{
    $base = czzz_sitemap_module_dir() . DIRECTORY_SEPARATOR . 'build';
    return $path === '' ? $base : $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
}

function czzz_sitemap_build_url(string $path = ''): string
{
    $base = czzz_sitemap_module_url('build');
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function czzz_sitemap_is_admin(): bool
{
    $userId = (int) Auth::userId();
    return $userId > 0 && Power::isAdmin($userId);
}

function czzz_sitemap_bool_option(string $key, bool $default): bool
{
    $value = Options::module($key, $default);
    if (is_bool($value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return (int) $value === 1;
    }

    $value = strtolower(trim((string) $value));
    if ($value === '') {
        return $default;
    }

    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function czzz_sitemap_int_option(string $key, int $default, int $min = 0, int $max = 100000): int
{
    $value = (int) Options::module($key, $default);
    if ($value < $min) {
        return $default;
    }

    return min($value, $max);
}

function czzz_sitemap_config(): array
{
    return [
        'route' => czzz_sitemap_page_route(),
        'part_size' => czzz_sitemap_int_option('czzz_sitemap_pc_page_part_size', 200, 20, 5000),
        'limits' => [
            'boards' => czzz_sitemap_int_option('czzz_sitemap_pc_page_max_boards', 200, 0, 5000),
            'tags' => czzz_sitemap_int_option('czzz_sitemap_pc_page_max_tags', 200, 0, 5000),
            'posts' => czzz_sitemap_int_option('czzz_sitemap_pc_page_max_posts', 1000, 1, 20000),
        ],
        'includes' => [
            'home' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_home', true),
            'search' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_search', true),
            'video_feed' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_video_feed', true),
            'boards' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_boards', true),
            'tags' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_tags', true),
            'posts' => czzz_sitemap_bool_option('czzz_sitemap_pc_page_include_posts', true),
        ],
    ];
}

function czzz_sitemap_schedule_secret(): string
{
    return trim((string) Options::module('czzz_sitemap_pc_page_schedule_secret', ''));
}

function czzz_sitemap_schedule_secret_is_strong(?string $secret = null): bool
{
    $secret = trim((string) ($secret ?? czzz_sitemap_schedule_secret()));
    if ($secret === '') {
        return false;
    }

    return strlen($secret) >= 24
        && preg_match('/[a-z]/i', $secret)
        && preg_match('/\d/', $secret);
}

function czzz_sitemap_mask_secret(?string $secret = null): string
{
    $secret = trim((string) ($secret ?? czzz_sitemap_schedule_secret()));
    if ($secret === '') {
        return '未配置';
    }

    if (strlen($secret) <= 8) {
        return str_repeat('*', strlen($secret));
    }

    return substr($secret, 0, 4) . str_repeat('*', max(4, strlen($secret) - 8)) . substr($secret, -4);
}

function czzz_sitemap_schedule_url(bool $withSecret = true): string
{
    $url = czzz_sitemap_module_url('api.php?action=cron_rebuild');
    $secret = czzz_sitemap_schedule_secret();
    if ($withSecret && $secret !== '' && czzz_sitemap_schedule_secret_is_strong($secret)) {
        $url .= '&salt=' . rawurlencode($secret);
    }

    return $url;
}

function czzz_sitemap_is_valid_schedule_secret(?string $provided): bool
{
    $secret = czzz_sitemap_schedule_secret();
    $provided = trim((string) $provided);
    if ($secret === '' || $provided === '' || !czzz_sitemap_schedule_secret_is_strong($secret)) {
        return false;
    }

    return hash_equals($secret, $provided);
}

function czzz_sitemap_cron_min_interval(): int
{
    return 300;
}

function czzz_sitemap_state_key(string $name): string
{
    return 'czzz_sitemap_pc_page_runtime_' . $name;
}

function czzz_sitemap_state_get(string $name, $default = null)
{
    return Options::module(czzz_sitemap_state_key($name), $default);
}

function czzz_sitemap_state_set(string $name, $value): void
{
    Options::moduleSave([
        czzz_sitemap_state_key($name) => $value,
    ]);
}

function czzz_sitemap_can_run_cron_rebuild(): array
{
    $secret = czzz_sitemap_schedule_secret();
    if ($secret === '') {
        return [
            'allowed' => false,
            'status' => 403,
            'msg' => '尚未配置定时更新密钥，已拦截本次自动更新请求',
        ];
    }

    if (!czzz_sitemap_schedule_secret_is_strong($secret)) {
        return [
            'allowed' => false,
            'status' => 403,
            'msg' => '定时更新密钥强度不足，请先在模块设置页重新生成并保存',
        ];
    }

    $lastCronAt = (int) czzz_sitemap_state_get('last_cron_at', 0);
    $interval = czzz_sitemap_cron_min_interval();
    $wait = ($lastCronAt + $interval) - time();
    if ($lastCronAt > 0 && $wait > 0) {
        return [
            'allowed' => false,
            'status' => 429,
            'msg' => '自动更新触发过于频繁，请 ' . $wait . ' 秒后再试',
            'retry_after' => $wait,
        ];
    }

    return [
        'allowed' => true,
        'status' => 200,
    ];
}

function czzz_sitemap_mark_cron_rebuild_run(): void
{
    Options::moduleSave([
        czzz_sitemap_state_key('last_cron_at') => time(),
        czzz_sitemap_state_key('last_rebuild_source') => 'cron',
    ]);
}

function czzz_sitemap_mark_admin_rebuild_run(): void
{
    Options::moduleSave([
        czzz_sitemap_state_key('last_rebuild_source') => 'admin',
    ]);
}

function czzz_sitemap_record_rebuild_start(string $source): void
{
    Options::moduleSave([
        czzz_sitemap_state_key('last_attempt_at') => time(),
        czzz_sitemap_state_key('last_rebuild_source') => $source,
    ]);
}

function czzz_sitemap_record_rebuild_success(string $source, array $summary): void
{
    $payload = [
        czzz_sitemap_state_key('last_attempt_at') => time(),
        czzz_sitemap_state_key('last_success_at') => time(),
        czzz_sitemap_state_key('last_rebuild_source') => $source,
        czzz_sitemap_state_key('last_status') => 'success',
        czzz_sitemap_state_key('last_error') => '',
        czzz_sitemap_state_key('last_generated_at') => (string) ($summary['generated_at'] ?? ''),
        czzz_sitemap_state_key('last_summary_total') => (int) ($summary['counts']['total'] ?? 0),
    ];

    if ($source === 'cron') {
        $payload[czzz_sitemap_state_key('last_cron_at')] = time();
    }

    Options::moduleSave($payload);
}

function czzz_sitemap_record_rebuild_failure(string $source, Throwable $e): void
{
    Options::moduleSave([
        czzz_sitemap_state_key('last_attempt_at') => time(),
        czzz_sitemap_state_key('last_failure_at') => time(),
        czzz_sitemap_state_key('last_rebuild_source') => $source,
        czzz_sitemap_state_key('last_status') => 'failed',
        czzz_sitemap_state_key('last_error') => trim($e->getMessage()),
    ]);
}

function czzz_sitemap_run_rebuild(string $source, bool $force = true): array
{
    czzz_sitemap_record_rebuild_start($source);

    try {
        $summary = czzz_sitemap_build($force);
        czzz_sitemap_record_rebuild_success($source, $summary);
        return $summary;
    } catch (Throwable $e) {
        czzz_sitemap_record_rebuild_failure($source, $e);
        throw $e;
    }
}

function czzz_sitemap_load_runtime_status(): array
{
    $summary = czzz_sitemap_load_summary();
    $configHash = czzz_sitemap_config_hash(czzz_sitemap_config());
    $buildExists = is_array($summary);
    $configCurrent = $buildExists && (($summary['config_hash'] ?? '') === $configHash);
    $fallbackGeneratedTs = 0;
    if ($buildExists && !empty($summary['generated_at'])) {
        $fallbackGeneratedTs = (int) strtotime((string) $summary['generated_at']);
    }
    $lastSuccessAt = (int) czzz_sitemap_state_get('last_success_at', 0);
    if ($lastSuccessAt <= 0 && $fallbackGeneratedTs > 0) {
        $lastSuccessAt = $fallbackGeneratedTs;
    }

    $status = [
        'build_exists' => $buildExists,
        'config_current' => $configCurrent,
        'state' => 'missing',
        'public_hint' => '当前尚未生成 Sitemap，请由管理员手动重建或等待定时任务执行。',
        'last_attempt_at' => (int) czzz_sitemap_state_get('last_attempt_at', 0),
        'last_success_at' => $lastSuccessAt,
        'last_failure_at' => (int) czzz_sitemap_state_get('last_failure_at', 0),
        'last_generated_at' => trim((string) czzz_sitemap_state_get('last_generated_at', (string) ($summary['generated_at'] ?? ''))),
        'last_rebuild_source' => trim((string) czzz_sitemap_state_get('last_rebuild_source', '')),
        'last_status' => trim((string) czzz_sitemap_state_get('last_status', '')),
        'last_error' => trim((string) czzz_sitemap_state_get('last_error', '')),
        'last_summary_total' => (int) czzz_sitemap_state_get('last_summary_total', (int) ($summary['counts']['total'] ?? 0)),
        'last_cron_at' => (int) czzz_sitemap_state_get('last_cron_at', 0),
        'cron_interval' => czzz_sitemap_cron_min_interval(),
        'secret_configured' => czzz_sitemap_schedule_secret() !== '',
        'secret_strong' => czzz_sitemap_schedule_secret_is_strong(),
    ];

    if ($buildExists && $configCurrent) {
        $status['state'] = 'ready';
        $status['public_hint'] = '当前 Sitemap build 已就绪，前台访问只读取现有输出文件。';
    } elseif ($buildExists) {
        $status['state'] = 'stale';
        $status['public_hint'] = '当前 Sitemap build 仍可访问，但配置已变更，需要管理员重建后才会使用新规则。';
    }

    return $status;
}

function czzz_sitemap_load_public_context(): array
{
    return [
        'summary' => czzz_sitemap_load_summary(),
        'runtime' => czzz_sitemap_load_runtime_status(),
    ];
}

function czzz_sitemap_config_hash(array $config): string
{
    return md5(json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
}

function czzz_sitemap_clear_output_buffers(): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

function czzz_sitemap_ensure_dir(string $dir): void
{
    if (is_dir($dir)) {
        return;
    }

    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('无法创建目录：' . $dir);
    }
}

function czzz_sitemap_rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = scandir($dir);
    if (!is_array($items)) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            czzz_sitemap_rrmdir($path);
        } elseif (file_exists($path)) {
            @unlink($path);
        }
    }

    @rmdir($dir);
}

function czzz_sitemap_write(string $file, string $content): void
{
    $dir = dirname($file);
    czzz_sitemap_ensure_dir($dir);
    if (file_put_contents($file, $content) === false) {
        throw new RuntimeException('无法写入文件：' . $file);
    }
}

function czzz_sitemap_acquire_build_lock()
{
    $lockFile = czzz_sitemap_runtime_file('build.lock');
    czzz_sitemap_ensure_dir(dirname($lockFile));
    $handle = fopen($lockFile, 'c+');
    if ($handle === false) {
        throw new RuntimeException('无法创建 Sitemap 构建锁');
    }

    if (!flock($handle, LOCK_EX | LOCK_NB)) {
        fclose($handle);
        throw new RuntimeException('网站地图正在构建中，请稍后再试');
    }

    return $handle;
}

function czzz_sitemap_release_build_lock($handle): void
{
    if (!is_resource($handle)) {
        return;
    }

    flock($handle, LOCK_UN);
    fclose($handle);
}

function czzz_sitemap_absolute_url(string $path): string
{
    if ($path === '') {
        return Site::homeUrl('/');
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    return Site::homeUrl($path);
}

function czzz_sitemap_normalize_lastmod($value): ?string
{
    if (is_numeric($value)) {
        $value = date('c', (int) $value);
    }

    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return null;
    }

    return date('c', $timestamp);
}

function czzz_sitemap_add_entry(array &$bucket, string $section, string $loc, string $title = '', ?string $lastmod = null): void
{
    $loc = trim($loc);
    if ($loc === '') {
        return;
    }

    $bucket[$section][$loc] = [
        'loc' => $loc,
        'title' => $title,
        'lastmod' => $lastmod,
    ];
}

function czzz_sitemap_collect_sections(): array
{
    $config = czzz_sitemap_config();
    $sections = [
        'pages' => [],
        'boards' => [],
        'tags' => [],
        'posts' => [],
    ];

    if ($config['includes']['home']) {
        czzz_sitemap_add_entry($sections, 'pages', Site::homeUrl('/'), '首页');
    }
    if ($config['includes']['search']) {
        czzz_sitemap_add_entry($sections, 'pages', Site::homeUrl('/search'), '搜索');
    }
    if ($config['includes']['video_feed']) {
        czzz_sitemap_add_entry($sections, 'pages', Site::homeUrl('/video-feed'), '视频流');
    }
    czzz_sitemap_add_entry($sections, 'pages', Site::homeUrl($config['route']), '网站地图');

    if ($config['includes']['boards']) {
        $boardCount = 0;
        foreach (BbsReadModel::all() as $bbs) {
            $bbsId = (int) ($bbs['id'] ?? 0);
            if ($bbsId <= 0) {
                continue;
            }

            czzz_sitemap_add_entry(
                $sections,
                'boards',
                czzz_sitemap_absolute_url(BBSRepository::url($bbsId)),
                (string) ($bbs['name'] ?? ('板块 ' . $bbsId)),
                czzz_sitemap_normalize_lastmod($bbs['update_time'] ?? $bbs['create_time'] ?? null)
            );

            $boardCount++;
            if ($config['limits']['boards'] > 0 && $boardCount >= $config['limits']['boards']) {
                break;
            }
        }
    }

    if ($config['includes']['tags']) {
        foreach (TagReadModel::hot($config['limits']['tags'] > 0 ? $config['limits']['tags'] : 200) as $tag) {
            $tagId = (int) ($tag['id'] ?? 0);
            if ($tagId <= 0) {
                continue;
            }

            czzz_sitemap_add_entry(
                $sections,
                'tags',
                czzz_sitemap_absolute_url((string) ($tag['link'] ?? ('/tag/' . $tagId))),
                (string) ($tag['name'] ?? ('标签 ' . $tagId)),
                null
            );
        }
    }

    $perPage = 100;
    $maxPosts = $config['limits']['posts'];
    $collected = 0;

    if ($config['includes']['posts']) {
        for ($page = 1; $page <= 20; $page++) {
            $ids = PostListReadModel::global($page, $perPage);
            if (empty($ids) || !is_array($ids)) {
                break;
            }

            $posts = PostReadModel::findMany($ids);
            foreach ($ids as $postId) {
                $postId = (int) $postId;
                $post = $posts[$postId] ?? null;
                if (!is_array($post)) {
                    continue;
                }

                $status = strtolower((string) ($post['post_status'] ?? ''));
                $power = strtolower((string) ($post['post_power'] ?? ''));
                $postType = trim((string) ($post['post_type'] ?? ''));

                if ($status !== 'publish' || $postType === '' || str_contains($power, 'private')) {
                    continue;
                }

                $title = trim((string) (($post['title'] ?? '') !== '' ? $post['title'] : ($post['post_title'] ?? '')));
                if ($title === '') {
                    $title = '帖子 ' . $postId;
                }

                czzz_sitemap_add_entry(
                    $sections,
                    'posts',
                    czzz_sitemap_absolute_url('/' . rawurlencode($postType) . '/' . $postId),
                    $title,
                    czzz_sitemap_normalize_lastmod($post['update_time'] ?? $post['post_update_time'] ?? $post['post_date'] ?? null)
                );

                $collected++;
                if ($collected >= $maxPosts) {
                    break 2;
                }
            }
        }

        if (empty($sections['posts'])) {
            $result = DB::raw(
                'SELECT id, post_type, post_title, post_content, post_date FROM ' . DB::fullTable('posts')
                . " WHERE post_status = 'publish' AND (post_power IS NULL OR post_power = '' OR post_power <> 'private')"
                . ' ORDER BY post_date DESC LIMIT ?',
                [$maxPosts]
            );

            if ($result instanceof mysqli_result) {
                while ($row = $result->fetch_assoc()) {
                    $postId = (int) ($row['id'] ?? 0);
                    $postType = trim((string) ($row['post_type'] ?? ''));
                    if ($postId <= 0 || $postType === '') {
                        continue;
                    }

                    czzz_sitemap_add_entry(
                        $sections,
                        'posts',
                        czzz_sitemap_absolute_url('/' . rawurlencode($postType) . '/' . $postId),
                        strip_tags((string) (($row['post_title'] ?? '') !== '' ? $row['post_title'] : ($row['post_content'] ?? ('帖子 ' . $postId)))),
                        czzz_sitemap_normalize_lastmod($row['post_date'] ?? null)
                    );
                }
                $result->free();
            }
        }
    }

    foreach ($sections as $section => $entries) {
        $sections[$section] = array_values($entries);
    }

    return $sections;
}

function czzz_sitemap_chunk_entries(array $entries, int $size): array
{
    if (empty($entries)) {
        return [];
    }

    return array_chunk($entries, max(1, $size));
}

function czzz_sitemap_render_html_part(string $title, array $entries): string
{
    $titleEsc = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $rows = '';

    foreach ($entries as $entry) {
        $loc = htmlspecialchars((string) $entry['loc'], ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars((string) ($entry['title'] ?: $entry['loc']), ENT_QUOTES, 'UTF-8');
        $lastmod = htmlspecialchars((string) ($entry['lastmod'] ?? ''), ENT_QUOTES, 'UTF-8');
        $meta = $lastmod !== '' ? '<span class="czzz-sitemap-lastmod">更新于 ' . $lastmod . '</span>' : '';
        $rows .= '<li><a href="' . $loc . '" target="_blank" rel="noopener noreferrer">' . $name . '</a>' . $meta . "</li>\n";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$titleEsc}</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;margin:0;padding:32px;background:#f6f8fb;color:#1f2937}
.wrap{max-width:1080px;margin:0 auto;background:#fff;border-radius:20px;padding:28px;box-shadow:0 20px 50px rgba(15,23,42,.08)}
h1{margin:0 0 20px;font-size:28px}
ul{margin:0;padding-left:20px}
li{margin:0 0 12px}
a{color:#2563eb;text-decoration:none}
a:hover{text-decoration:underline}
.czzz-sitemap-lastmod{display:inline-block;margin-left:10px;color:#6b7280;font-size:13px}
</style>
</head>
<body>
<div class="wrap">
<h1>{$titleEsc}</h1>
<ul>
{$rows}</ul>
</div>
</body>
</html>
HTML;
}

function czzz_sitemap_render_html_index(array $summary): string
{
    $items = '';
    foreach ($summary['parts'] as $part) {
        $label = htmlspecialchars((string) $part['label'], ENT_QUOTES, 'UTF-8');
        $count = (int) $part['count'];
        $htmlUrl = htmlspecialchars((string) $part['html_url'], ENT_QUOTES, 'UTF-8');
        $xmlUrl = htmlspecialchars((string) $part['xml_url'], ENT_QUOTES, 'UTF-8');
        $txtUrl = htmlspecialchars((string) $part['txt_url'], ENT_QUOTES, 'UTF-8');
        $items .= '<tr><td>' . $label . '</td><td>' . $count . '</td><td><a href="' . $htmlUrl . '">HTML</a></td><td><a href="' . $xmlUrl . '">XML</a></td><td><a href="' . $txtUrl . '">TXT</a></td></tr>' . "\n";
    }

    $generatedAt = htmlspecialchars((string) $summary['generated_at'], ENT_QUOTES, 'UTF-8');
    $demoUrl = htmlspecialchars((string) $summary['demo_url'], ENT_QUOTES, 'UTF-8');

    return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>网站地图索引</title>
<style>
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;margin:0;padding:32px;background:#f6f8fb;color:#111827}
.wrap{max-width:1120px;margin:0 auto;background:#fff;border-radius:20px;padding:28px;box-shadow:0 20px 50px rgba(15,23,42,.08)}
h1{margin:0 0 8px;font-size:30px}
p{margin:0 0 18px;color:#6b7280}
table{width:100%;border-collapse:collapse}
th,td{padding:12px 14px;border-bottom:1px solid #e5e7eb;text-align:left}
th{background:#f8fafc}
a{color:#2563eb;text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">
<h1>网站地图索引</h1>
<p>生成时间：{$generatedAt}</p>
<p><a href="{$demoUrl}">返回模块主页</a></p>
<table>
<thead>
<tr><th>分区</th><th>链接数</th><th>HTML</th><th>XML</th><th>TXT</th></tr>
</thead>
<tbody>
{$items}</tbody>
</table>
</div>
</body>
</html>
HTML;
}

function czzz_sitemap_render_xml_part(array $entries): string
{
    $rows = [];
    foreach ($entries as $entry) {
        $row = '  <url><loc>' . htmlspecialchars((string) $entry['loc'], ENT_XML1, 'UTF-8') . '</loc>';
        if (!empty($entry['lastmod'])) {
            $row .= '<lastmod>' . htmlspecialchars((string) $entry['lastmod'], ENT_XML1, 'UTF-8') . '</lastmod>';
        }
        $row .= '</url>';
        $rows[] = $row;
    }

    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
        . implode("\n", $rows)
        . "\n</urlset>\n";
}

function czzz_sitemap_render_xml_index(array $summary): string
{
    $rows = [];
    foreach ($summary['parts'] as $part) {
        $rows[] = '  <sitemap><loc>' . htmlspecialchars((string) $part['xml_url'], ENT_XML1, 'UTF-8') . '</loc><lastmod>'
            . htmlspecialchars((string) $summary['generated_at'], ENT_XML1, 'UTF-8') . '</lastmod></sitemap>';
    }

    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
        . implode("\n", $rows)
        . "\n</sitemapindex>\n";
}

function czzz_sitemap_render_txt(array $entries): string
{
    $rows = [];
    foreach ($entries as $entry) {
        $rows[] = (string) $entry['loc'];
    }

    return implode("\n", $rows) . "\n";
}

function czzz_sitemap_build(bool $force = false): array
{
    $config = czzz_sitemap_config();
    if (!$force) {
        $cached = czzz_sitemap_load_summary();
        if (is_array($cached) && (($cached['config_hash'] ?? '') === czzz_sitemap_config_hash($config))) {
            return $cached;
        }
    }

    $lockHandle = czzz_sitemap_acquire_build_lock();
    try {
        if (!$force) {
            $cached = czzz_sitemap_load_summary();
            if (is_array($cached) && (($cached['config_hash'] ?? '') === czzz_sitemap_config_hash($config))) {
                return $cached;
            }
        }

        $sections = czzz_sitemap_collect_sections();
        $partSize = $config['part_size'];
        $generatedAt = date('c');
        $allEntries = [];
        $summary = [
            'module_id' => czzz_sitemap_module_id(),
            'generated_at' => $generatedAt,
            'demo_url' => czzz_sitemap_demo_url(),
            'module_page_url' => czzz_sitemap_module_page_url(),
            'route_url' => Site::homeUrl($config['route']),
            'build_root_url' => czzz_sitemap_build_url(),
            'rebuild_api' => czzz_sitemap_module_url('api.php?action=rebuild'),
            'config' => $config,
            'config_hash' => czzz_sitemap_config_hash($config),
            'counts' => [
                'pages' => count($sections['pages']),
                'boards' => count($sections['boards']),
                'tags' => count($sections['tags']),
                'posts' => count($sections['posts']),
                'total' => 0,
            ],
            'part_size' => $partSize,
            'parts' => [],
            'section_meta' => [],
            'index_urls' => [
                'html' => czzz_sitemap_build_url('html/index.html'),
                'xml' => czzz_sitemap_build_url('xml/index.xml'),
                'txt' => czzz_sitemap_build_url('txt/index.txt'),
            ],
        ];

        $tmpDir = czzz_sitemap_module_dir() . DIRECTORY_SEPARATOR . 'build_tmp_' . uniqid('', true);
        czzz_sitemap_ensure_dir($tmpDir . DIRECTORY_SEPARATOR . 'html');
        czzz_sitemap_ensure_dir($tmpDir . DIRECTORY_SEPARATOR . 'xml');
        czzz_sitemap_ensure_dir($tmpDir . DIRECTORY_SEPARATOR . 'txt');

        foreach ($sections as $section => $entries) {
            $chunks = czzz_sitemap_chunk_entries($entries, $partSize);
            $summary['section_meta'][$section] = [
                'label' => match ($section) {
                    'pages' => '页面',
                    'boards' => '板块',
                    'tags' => '标签',
                    'posts' => '帖子',
                    default => $section,
                },
                'count' => count($entries),
                'part_count' => count($chunks),
                'included' => count($entries) > 0,
            ];
            foreach ($chunks as $index => $chunk) {
                $name = $section . '-' . ($index + 1);
                $label = strtoupper($section) . ' / Part ' . ($index + 1);
                $summary['parts'][] = [
                    'name' => $name,
                    'section' => $section,
                    'label' => $label,
                    'count' => count($chunk),
                    'html_url' => czzz_sitemap_build_url('html/' . $name . '.html'),
                    'xml_url' => czzz_sitemap_build_url('xml/' . $name . '.xml'),
                    'txt_url' => czzz_sitemap_build_url('txt/' . $name . '.txt'),
                ];

                $allEntries = array_merge($allEntries, $chunk);
                czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . $name . '.html', czzz_sitemap_render_html_part($label, $chunk));
                czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . $name . '.xml', czzz_sitemap_render_xml_part($chunk));
                czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'txt' . DIRECTORY_SEPARATOR . $name . '.txt', czzz_sitemap_render_txt($chunk));
            }
        }

        $summary['counts']['total'] = count($allEntries);
        czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'index.html', czzz_sitemap_render_html_index($summary));
        czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'index.xml', czzz_sitemap_render_xml_index($summary));
        czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'txt' . DIRECTORY_SEPARATOR . 'index.txt', czzz_sitemap_render_txt($allEntries));
        czzz_sitemap_write($tmpDir . DIRECTORY_SEPARATOR . 'index.json', json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $buildDir = czzz_sitemap_build_dir();
        $backupDir = '';
        if (is_dir($buildDir)) {
            $backupDir = czzz_sitemap_module_dir() . DIRECTORY_SEPARATOR . 'build_backup_' . uniqid('', true);
            if (!@rename($buildDir, $backupDir)) {
                czzz_sitemap_rrmdir($tmpDir);
                throw new RuntimeException('无法切换旧的 Sitemap 构建目录');
            }
        }

        if (!@rename($tmpDir, $buildDir)) {
            if ($backupDir !== '' && is_dir($backupDir)) {
                @rename($backupDir, $buildDir);
            }
            czzz_sitemap_rrmdir($tmpDir);
            throw new RuntimeException('无法启用新的 Sitemap 构建结果');
        }

        if ($backupDir !== '' && is_dir($backupDir)) {
            czzz_sitemap_rrmdir($backupDir);
        }

        return $summary;
    } finally {
        czzz_sitemap_release_build_lock($lockHandle);
    }
}

function czzz_sitemap_load_summary(): ?array
{
    $file = czzz_sitemap_build_dir('index.json');
    if (!is_file($file)) {
        return null;
    }

    $json = file_get_contents($file);
    if (!is_string($json) || $json === '') {
        return null;
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

function czzz_sitemap_ensure_build(): array
{
    $summary = czzz_sitemap_load_summary();
    if (!is_array($summary)) {
        return czzz_sitemap_build(true);
    }

    if (($summary['config_hash'] ?? '') !== czzz_sitemap_config_hash(czzz_sitemap_config())) {
        return czzz_sitemap_build(true);
    }

    return $summary;
}

function czzz_sitemap_serve(string $format, string $name = 'index'): void
{
    $format = strtolower(trim($format));
    $name = strtolower(trim($name));
    if (!in_array($format, ['html', 'xml', 'txt'], true) || !preg_match('/^[a-z0-9\-]+$/', $name)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Bad Request';
        exit;
    }

    $file = czzz_sitemap_build_dir($format . '/' . $name . '.' . $format);
    if (!is_file($file)) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Sitemap build not found. Please rebuild it from the module page or cron task.';
        exit;
    }

    $types = [
        'html' => 'text/html; charset=utf-8',
        'xml' => 'application/xml; charset=utf-8',
        'txt' => 'text/plain; charset=utf-8',
    ];

    czzz_sitemap_clear_output_buffers();
    header('Content-Type: ' . $types[$format]);
    header('Cache-Control: public, max-age=300');
    readfile($file);
    exit;
}
