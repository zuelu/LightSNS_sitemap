<?php

require_once dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'sitemap_lib.php';

use LightSNS\Foundation\Site;
use LightSNS\Shared\Options;

$context = czzz_sitemap_load_public_context();
$summary = is_array($context['summary'] ?? null) ? $context['summary'] : null;
$runtime = is_array($context['runtime'] ?? null) ? $context['runtime'] : [];

$isAdmin = czzz_sitemap_is_admin();
$styleUrl = czzz_sitemap_module_url('module.css');
$apiUrl = czzz_sitemap_module_url('api.php');
$title = trim((string) Options::module('czzz_sitemap_pc_page_title')) ?: '网站地图 Sitemap';
$intro = trim((string) Options::module('czzz_sitemap_pc_page_intro')) ?: '统一生成 HTML、XML、TXT 三类站点地图文件，并提供模块内重建与分区输出能力。';
$routePath = '/' . ltrim((string) (Options::module('czzz_sitemap_pc_page_route') ?: '/sitemap'), '/');
$config = is_array($summary['config'] ?? null) ? $summary['config'] : czzz_sitemap_config();
$includes = is_array($config['includes'] ?? null) ? $config['includes'] : [];
$limits = is_array($config['limits'] ?? null) ? $config['limits'] : [];
$sectionMeta = is_array($summary['section_meta'] ?? null) ? $summary['section_meta'] : [];
$statusTextMap = [
    'ready' => '当前 build 已就绪',
    'stale' => '当前 build 待按新配置重建',
    'missing' => '当前尚未生成 build',
];
$statusClassMap = [
    'ready' => 'is-ready',
    'stale' => 'is-stale',
    'missing' => 'is-missing',
];
$runtimeState = (string) ($runtime['state'] ?? 'missing');
$runtimeStatusText = $statusTextMap[$runtimeState] ?? '状态未知';
$runtimeStatusClass = $statusClassMap[$runtimeState] ?? 'is-missing';
$lastSourceMap = [
    'admin' => '管理员手动重建',
    'cron' => '定时任务重建',
];
$lastSourceText = $lastSourceMap[(string) ($runtime['last_rebuild_source'] ?? '')] ?? '暂无记录';
$lastSuccessAt = !empty($runtime['last_success_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_success_at']) : '暂无';
$lastFailureAt = !empty($runtime['last_failure_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_failure_at']) : '暂无';
$lastCronAt = !empty($runtime['last_cron_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_cron_at']) : '暂无';
$rangeLabels = [
    'home' => '首页',
    'search' => '搜索页',
    'video_feed' => '视频流',
    'boards' => '公开板块',
    'tags' => '热门标签',
    'posts' => '公开帖子',
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo Site::escHtml($title); ?></title>
    <link rel="stylesheet" href="<?php echo Site::escAttr($styleUrl); ?>">
</head>
<body>
<main class="czzz-sitemap-page">
    <div id="czzz-sitemap-copy-status" class="czzz-sitemap-copy-status" aria-live="polite"></div>
    <section class="czzz-sitemap-hero">
        <div class="czzz-sitemap-hero__inner">
            <div>
                <span class="czzz-sitemap-badge">CZZZ SITEMAP MODULE</span>
                <h1><?php echo Site::escHtml($title); ?></h1>
                <p><?php echo Site::escHtml($intro); ?></p>
                <div class="czzz-sitemap-runtime-badges">
                    <span class="czzz-sitemap-runtime-pill <?php echo Site::escAttr($runtimeStatusClass); ?>"><?php echo Site::escHtml($runtimeStatusText); ?></span>
                    <span class="czzz-sitemap-runtime-pill"><?php echo Site::escHtml((string) ($runtime['public_hint'] ?? '')); ?></span>
                </div>
            </div>
            <div class="czzz-sitemap-actions">
                <?php if (is_array($summary)): ?>
                <a class="czzz-sitemap-btn primary" href="<?php echo Site::escAttr($summary['index_urls']['html'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">查看 HTML 索引</a>
                <a class="czzz-sitemap-btn" href="<?php echo Site::escAttr($summary['index_urls']['xml'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">查看 XML 索引</a>
                <a class="czzz-sitemap-btn" href="<?php echo Site::escAttr($summary['index_urls']['txt'] ?? '#'); ?>" target="_blank" rel="noopener noreferrer">查看 TXT 索引</a>
                <?php else: ?>
                <span class="czzz-sitemap-btn is-disabled">暂无 HTML 索引</span>
                <span class="czzz-sitemap-btn is-disabled">暂无 XML 索引</span>
                <span class="czzz-sitemap-btn is-disabled">暂无 TXT 索引</span>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                <button id="czzz-sitemap-rebuild" class="czzz-sitemap-btn dark" type="button" data-api="<?php echo Site::escAttr($apiUrl); ?>">管理员重建</button>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="czzz-sitemap-panel">
        <div class="czzz-sitemap-panel__head">
            <h2>当前状态</h2>
            <p>前台页面现在只读取现有 build 结果，不会因为访问页面而自动触发重建。</p>
        </div>
        <div class="czzz-sitemap-info-grid">
            <div class="czzz-sitemap-info-card">
                <h3>公开访问状态</h3>
                <ul class="czzz-sitemap-list">
                    <li><span>当前状态</span><strong><?php echo Site::escHtml($runtimeStatusText); ?></strong></li>
                    <li><span>最近成功</span><strong><?php echo Site::escHtml($lastSuccessAt); ?></strong></li>
                    <li><span>最近失败</span><strong><?php echo Site::escHtml($lastFailureAt); ?></strong></li>
                    <li><span>最近来源</span><strong><?php echo Site::escHtml($lastSourceText); ?></strong></li>
                </ul>
            </div>
            <div class="czzz-sitemap-info-card">
                <h3>定时与构建</h3>
                <ul class="czzz-sitemap-list">
                    <li><span>最近定时</span><strong><?php echo Site::escHtml($lastCronAt); ?></strong></li>
                    <li><span>最短间隔</span><strong><?php echo (int) ($runtime['cron_interval'] ?? 0); ?> 秒</strong></li>
                    <li><span>当前 build</span><strong><?php echo !empty($runtime['build_exists']) ? '已存在' : '不存在'; ?></strong></li>
                    <li><span>配置一致</span><strong><?php echo !empty($runtime['config_current']) ? '是' : '否'; ?></strong></li>
                </ul>
            </div>
        </div>
        <?php if ($runtimeState !== 'ready'): ?>
        <div class="czzz-sitemap-inline-note <?php echo $runtimeState === 'missing' ? 'is-warning' : 'is-info'; ?>">
            <?php echo Site::escHtml((string) ($runtime['public_hint'] ?? '')); ?>
            <?php if ($isAdmin): ?>
            当前可直接点击上方“管理员重建”生成新的 Sitemap。
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($isAdmin && !empty($runtime['last_error'])): ?>
        <div class="czzz-sitemap-inline-note is-error">
            最近一次失败原因：<?php echo Site::escHtml((string) $runtime['last_error']); ?>
        </div>
        <?php endif; ?>
    </section>

    <?php if (is_array($summary)): ?>
    <section class="czzz-sitemap-panel czzz-sitemap-summary">
        <div class="czzz-sitemap-grid">
            <div class="czzz-sitemap-card"><span>页面</span><strong><?php echo (int) ($summary['counts']['pages'] ?? 0); ?></strong></div>
            <div class="czzz-sitemap-card"><span>板块</span><strong><?php echo (int) ($summary['counts']['boards'] ?? 0); ?></strong></div>
            <div class="czzz-sitemap-card"><span>标签</span><strong><?php echo (int) ($summary['counts']['tags'] ?? 0); ?></strong></div>
            <div class="czzz-sitemap-card"><span>帖子</span><strong><?php echo (int) ($summary['counts']['posts'] ?? 0); ?></strong></div>
            <div class="czzz-sitemap-card"><span>总链接数</span><strong><?php echo (int) ($summary['counts']['total'] ?? 0); ?></strong></div>
            <div class="czzz-sitemap-card"><span>分区阈值</span><strong><?php echo (int) ($summary['part_size'] ?? 0); ?></strong></div>
        </div>
        <p class="czzz-sitemap-meta">最近生成时间：<?php echo Site::escHtml((string) ($summary['generated_at'] ?? '')); ?></p>
        <p class="czzz-sitemap-meta">Demo URI：<?php echo Site::escHtml(czzz_sitemap_demo_url()); ?></p>
        <p class="czzz-sitemap-meta">当前前台路由：<?php echo Site::escHtml(Site::homeUrl($routePath)); ?></p>
    </section>

    <section class="czzz-sitemap-panel">
        <div class="czzz-sitemap-panel__head">
            <h2>访问入口与构建策略</h2>
            <p>这里汇总当前生效的访问路径、build 输出位置和分区阈值，方便管理员核对。</p>
        </div>
        <div class="czzz-sitemap-info-grid">
            <div class="czzz-sitemap-info-card">
                <h3>访问入口</h3>
                <ul class="czzz-sitemap-list">
                    <li><span>模块页</span><a href="<?php echo Site::escAttr((string) ($summary['module_page_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer"><?php echo Site::escHtml((string) ($summary['module_page_url'] ?? '')); ?></a></li>
                    <li><span>当前路由</span><a href="<?php echo Site::escAttr((string) ($summary['route_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer"><?php echo Site::escHtml((string) ($summary['route_url'] ?? '')); ?></a></li>
                    <li><span>重建接口</span><a href="<?php echo Site::escAttr((string) ($summary['rebuild_api'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer"><?php echo Site::escHtml((string) ($summary['rebuild_api'] ?? '')); ?></a></li>
                </ul>
            </div>
            <div class="czzz-sitemap-info-card">
                <h3>Build 策略</h3>
                <ul class="czzz-sitemap-list">
                    <li><span>单分区阈值</span><strong><?php echo (int) ($summary['part_size'] ?? 0); ?> 条</strong></li>
                    <li><span>Build 根目录</span><a href="<?php echo Site::escAttr((string) ($summary['build_root_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer"><?php echo Site::escHtml((string) ($summary['build_root_url'] ?? '')); ?></a></li>
                    <li><span>失败保护</span><strong>保留上一份可用结果</strong></li>
                </ul>
            </div>
        </div>
    </section>

    <section class="czzz-sitemap-panel">
        <div class="czzz-sitemap-panel__head">
            <h2>纳入范围</h2>
            <p>以下开关和数量上限来自模块设置页，改动后会按最新配置重新生成 Sitemap。</p>
        </div>
        <div class="czzz-sitemap-chip-group">
            <?php foreach ($rangeLabels as $key => $label): ?>
            <span class="czzz-sitemap-chip <?php echo !empty($includes[$key]) ? 'is-on' : 'is-off'; ?>">
                <?php echo Site::escHtml($label); ?> · <?php echo !empty($includes[$key]) ? '已纳入' : '未纳入'; ?>
            </span>
            <?php endforeach; ?>
        </div>
        <div class="czzz-sitemap-info-grid">
            <div class="czzz-sitemap-info-card">
                <h3>数量上限</h3>
                <ul class="czzz-sitemap-list">
                    <li><span>板块上限</span><strong><?php echo (int) ($limits['boards'] ?? 0); ?><?php echo ((int) ($limits['boards'] ?? 0) === 0) ? '（不限）' : ''; ?></strong></li>
                    <li><span>标签上限</span><strong><?php echo (int) ($limits['tags'] ?? 0); ?><?php echo ((int) ($limits['tags'] ?? 0) === 0) ? '（不限）' : ''; ?></strong></li>
                    <li><span>帖子上限</span><strong><?php echo (int) ($limits['posts'] ?? 0); ?></strong></li>
                </ul>
            </div>
            <div class="czzz-sitemap-info-card">
                <h3>实际纳入结果</h3>
                <ul class="czzz-sitemap-list">
                    <?php foreach ($sectionMeta as $meta): ?>
                    <li>
                        <span><?php echo Site::escHtml((string) ($meta['label'] ?? '')); ?></span>
                        <strong><?php echo (int) ($meta['count'] ?? 0); ?> 条 / <?php echo (int) ($meta['part_count'] ?? 0); ?> 个分区</strong>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <section class="czzz-sitemap-panel">
        <div class="czzz-sitemap-panel__head">
            <h2>分区输出</h2>
            <p>每个分区都提供 HTML、XML、TXT 三个独立输出文件，并按页面 / 板块 / 标签 / 帖子分类管理。</p>
        </div>
        <div class="czzz-sitemap-table-wrap">
            <table class="czzz-sitemap-table">
                <thead>
                <tr>
                    <th>分类</th>
                    <th>分区</th>
                    <th>数量</th>
                    <th>HTML</th>
                    <th>XML</th>
                    <th>TXT</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (($summary['parts'] ?? []) as $part): ?>
                <tr>
                    <td><?php echo Site::escHtml((string) ($sectionMeta[$part['section']]['label'] ?? $part['section'] ?? '')); ?></td>
                    <td><?php echo Site::escHtml((string) ($part['label'] ?? '')); ?></td>
                    <td><?php echo (int) ($part['count'] ?? 0); ?></td>
                    <td>
                        <div class="czzz-sitemap-link-actions">
                            <a href="<?php echo Site::escAttr((string) ($part['html_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer">HTML</a>
                            <button class="czzz-sitemap-copy-btn" type="button" data-copy-url="<?php echo Site::escAttr((string) ($part['html_url'] ?? '')); ?>">复制</button>
                        </div>
                    </td>
                    <td>
                        <div class="czzz-sitemap-link-actions">
                            <a href="<?php echo Site::escAttr((string) ($part['xml_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer">XML</a>
                            <button class="czzz-sitemap-copy-btn" type="button" data-copy-url="<?php echo Site::escAttr((string) ($part['xml_url'] ?? '')); ?>">复制</button>
                        </div>
                    </td>
                    <td>
                        <div class="czzz-sitemap-link-actions">
                            <a href="<?php echo Site::escAttr((string) ($part['txt_url'] ?? '#')); ?>" target="_blank" rel="noopener noreferrer">TXT</a>
                            <button class="czzz-sitemap-copy-btn" type="button" data-copy-url="<?php echo Site::escAttr((string) ($part['txt_url'] ?? '')); ?>">复制</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>
</main>

<script>
(function () {
    var button = document.getElementById('czzz-sitemap-rebuild');
    var copyStatus = document.getElementById('czzz-sitemap-copy-status');
    if (window.__czzz_sitemap_page_init__) return;
    window.__czzz_sitemap_page_init__ = true;

    function showCopyStatus(text, isError) {
        if (!copyStatus) return;
        copyStatus.textContent = text;
        copyStatus.className = 'czzz-sitemap-copy-status is-visible' + (isError ? ' is-error' : '');
        window.clearTimeout(showCopyStatus.timer);
        showCopyStatus.timer = window.setTimeout(function () {
            copyStatus.className = 'czzz-sitemap-copy-status';
        }, 1800);
    }

    if (button) {
        button.addEventListener('click', function () {
            var api = button.getAttribute('data-api');
            if (!api) return;

            button.disabled = true;
            button.textContent = '重建中...';

            fetch(api + '?action=rebuild', {
                method: 'POST',
                credentials: 'same-origin'
            }).then(function (res) {
                return res.json();
            }).then(function (res) {
                if (!res || res.code !== 1) {
                    throw new Error((res && res.msg) || '重建失败');
                }
                window.location.reload();
            }).catch(function (err) {
                alert((err && err.message) || '重建失败');
                button.disabled = false;
                button.textContent = '管理员重建';
            });
        });
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || !target.classList || !target.classList.contains('czzz-sitemap-copy-btn')) {
            return;
        }

        var url = target.getAttribute('data-copy-url');
        if (!url) {
            showCopyStatus('复制失败，链接为空', true);
            return;
        }

        if (!navigator.clipboard || !navigator.clipboard.writeText) {
            showCopyStatus('当前浏览器不支持一键复制', true);
            return;
        }

        navigator.clipboard.writeText(url).then(function () {
            showCopyStatus('已复制：' + url, false);
        }).catch(function () {
            showCopyStatus('复制失败，请手动复制', true);
        });
    });
})();
</script>
</body>
</html>
