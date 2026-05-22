<?php
if (!defined('LIGHTSNS_LOADED')) {
    die;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'sitemap_lib.php';

$czzz_sitemap_module_key = 'czzz-pc-page-sitemap';
$czzz_sitemap_option_prefix = 'czzz_sitemap_pc_page_';
$czzz_sitemap_option_route = $czzz_sitemap_option_prefix . 'route';
$czzz_sitemap_option_title = $czzz_sitemap_option_prefix . 'title';
$czzz_sitemap_option_intro = $czzz_sitemap_option_prefix . 'intro';
$czzz_sitemap_option_include_home = $czzz_sitemap_option_prefix . 'include_home';
$czzz_sitemap_option_include_search = $czzz_sitemap_option_prefix . 'include_search';
$czzz_sitemap_option_include_video_feed = $czzz_sitemap_option_prefix . 'include_video_feed';
$czzz_sitemap_option_include_boards = $czzz_sitemap_option_prefix . 'include_boards';
$czzz_sitemap_option_include_tags = $czzz_sitemap_option_prefix . 'include_tags';
$czzz_sitemap_option_include_posts = $czzz_sitemap_option_prefix . 'include_posts';
$czzz_sitemap_option_max_boards = $czzz_sitemap_option_prefix . 'max_boards';
$czzz_sitemap_option_max_tags = $czzz_sitemap_option_prefix . 'max_tags';
$czzz_sitemap_option_max_posts = $czzz_sitemap_option_prefix . 'max_posts';
$czzz_sitemap_option_part_size = $czzz_sitemap_option_prefix . 'part_size';
$czzz_sitemap_option_schedule_secret = $czzz_sitemap_option_prefix . 'schedule_secret';
$czzz_sitemap_module_dir = \LightSNS\Foundation\Site::moduleDir() . '/pc/page/' . $czzz_sitemap_module_key;

$route_path = \LightSNS\Shared\Options::module($czzz_sitemap_option_route);
$route_path = '/' . ltrim((string) ($route_path ?: '/sitemap'), '/');
$module_page_url = czzz_sitemap_module_page_url();
$route_url = \LightSNS\Foundation\Site::homeUrl($route_path);
$saved_schedule_secret = trim((string) \LightSNS\Shared\Options::module($czzz_sitemap_option_schedule_secret, ''));
$schedule_secret_strong = czzz_sitemap_schedule_secret_is_strong($saved_schedule_secret);
$schedule_url = czzz_sitemap_schedule_url(true);
$schedule_endpoint = czzz_sitemap_schedule_url(false);
$schedule_interval = czzz_sitemap_cron_min_interval();
$runtime = czzz_sitemap_load_runtime_status();
$last_success_at = !empty($runtime['last_success_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_success_at']) : '暂无';
$last_failure_at = !empty($runtime['last_failure_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_failure_at']) : '暂无';
$last_cron_at = !empty($runtime['last_cron_at']) ? date('Y-m-d H:i:s', (int) $runtime['last_cron_at']) : '暂无';
$last_source_text = match ((string) ($runtime['last_rebuild_source'] ?? '')) {
    'admin' => '管理员手动重建',
    'cron' => '定时任务重建',
    default => '暂无记录',
};
$runtime_state_text = match ((string) ($runtime['state'] ?? 'missing')) {
    'ready' => '当前 build 已就绪',
    'stale' => '当前 build 仍可访问，但配置已变更，等待重建',
    default => '当前尚未生成 build',
};

$module_page_url_html = htmlspecialchars($module_page_url, ENT_QUOTES, 'UTF-8');
$route_url_html = htmlspecialchars($route_url, ENT_QUOTES, 'UTF-8');
$schedule_endpoint_html = htmlspecialchars($schedule_endpoint, ENT_QUOTES, 'UTF-8');
$masked_secret_html = htmlspecialchars(czzz_sitemap_mask_secret($saved_schedule_secret), ENT_QUOTES, 'UTF-8');
$schedule_desc = '自动更新接口最短触发间隔为 ' . $schedule_interval . ' 秒。访问时必须带正确密钥，未带密钥或密钥错误都会被拦截。';
$schedule_actions_html = '';
if ($saved_schedule_secret !== '' && $schedule_secret_strong) {
    $schedule_url_js = htmlspecialchars(json_encode($schedule_url, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
    $schedule_actions_html =
        '<button type="button" class="adminx-btn adminx-btn-primary adminx-btn-sm" onclick="(function(url){if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(url).then(function(){if(window.LS&&LS.ui&&LS.ui.toast){LS.ui.toast.success(\'已复制定时链接\');}}).catch(function(){window.prompt(\'复制以下定时链接\', url);});}else{window.prompt(\'复制以下定时链接\', url);}})(' . $schedule_url_js . ')">复制定时链接</button>'
        . '<form method="post" action="' . $schedule_endpoint_html . '" target="_blank" style="display:inline-block;margin-left:8px;">'
        . '<input type="hidden" name="action" value="cron_rebuild">'
        . '<input type="hidden" name="salt" value="' . htmlspecialchars($saved_schedule_secret, ENT_QUOTES, 'UTF-8') . '">'
        . '<button type="submit" class="adminx-btn adminx-btn-secondary adminx-btn-sm">跳转</button>'
        . '</form>';
}
$schedule_template_js = htmlspecialchars(json_encode($schedule_endpoint . '&salt=你的密钥', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$schedule_actions_html .= '<button type="button" class="adminx-btn adminx-btn-secondary adminx-btn-sm" style="margin-left:8px;" onclick="(function(url){if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(url).then(function(){if(window.LS&&LS.ui&&LS.ui.toast){LS.ui.toast.success(\'已复制接口模板\');}}).catch(function(){window.prompt(\'复制以下接口模板\', url);});}else{window.prompt(\'复制以下接口模板\', url);}})(' . $schedule_template_js . ')">复制接口模板</button>';

$link_panel_html =
    '<div style="display:grid;gap:14px;">'
    . '<div style="padding:14px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;">'
    . '<div style="font-weight:700;margin-bottom:6px;">运行状态</div>'
    . '<div style="display:grid;gap:6px;color:#334155;line-height:1.7;">'
    . '<div>当前状态：' . htmlspecialchars($runtime_state_text, ENT_QUOTES, 'UTF-8') . '</div>'
    . '<div>最近成功：' . htmlspecialchars($last_success_at, ENT_QUOTES, 'UTF-8') . '</div>'
    . '<div>最近失败：' . htmlspecialchars($last_failure_at, ENT_QUOTES, 'UTF-8') . '</div>'
    . '<div>最近来源：' . htmlspecialchars($last_source_text, ENT_QUOTES, 'UTF-8') . '</div>'
    . '<div>最近定时：' . htmlspecialchars($last_cron_at, ENT_QUOTES, 'UTF-8') . '</div>'
    . '<div>当前 build：' . (!empty($runtime['build_exists']) ? '已存在' : '不存在') . ' / 配置一致：' . (!empty($runtime['config_current']) ? '是' : '否') . '</div>'
    . '</div>'
    . '</div>'
    . '<div style="padding:14px 16px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;">'
    . '<div style="font-weight:700;margin-bottom:6px;">模块主页</div>'
    . '<div style="word-break:break-all;color:#475569;margin-bottom:10px;">' . $module_page_url_html . '</div>'
    . '<a href="' . $module_page_url_html . '" target="_blank" rel="noopener noreferrer" class="adminx-btn adminx-btn-secondary adminx-btn-sm">跳转</a>'
    . '</div>'
    . '<div style="padding:14px 16px;border:1px solid #dbeafe;border-radius:12px;background:#f8fbff;">'
    . '<div style="font-weight:700;margin-bottom:6px;">当前页面路由</div>'
    . '<div style="word-break:break-all;color:#475569;margin-bottom:10px;">' . $route_url_html . '</div>'
    . '<a href="' . $route_url_html . '" target="_blank" rel="noopener noreferrer" class="adminx-btn adminx-btn-secondary adminx-btn-sm">跳转</a>'
    . '</div>'
    . '<div style="padding:14px 16px;border:1px solid #dcfce7;border-radius:12px;background:#f0fdf4;">'
    . '<div style="font-weight:700;margin-bottom:6px;">定时更新接口</div>'
    . '<div style="color:#166534;margin-bottom:6px;">接口地址：' . $schedule_endpoint_html . '</div>'
    . '<div style="color:#166534;margin-bottom:6px;">当前密钥：' . $masked_secret_html . '</div>'
    . '<div style="color:#166534;line-height:1.7;margin-bottom:10px;">' . htmlspecialchars($schedule_desc, ENT_QUOTES, 'UTF-8') . '</div>'
    . $schedule_actions_html
    . '</div>'
    . '</div>';

\LightSNS\Foundation\PageRouter::add('module_' . $czzz_sitemap_module_key, [
    'path' => $route_path,
    'file' => $czzz_sitemap_module_dir . '/page.php',
    'layout' => 'raw',
    'seo'  => [
        'title'       => (string) (\LightSNS\Shared\Options::module($czzz_sitemap_option_title) ?: '网站地图 Sitemap'),
        'description' => (string) (\LightSNS\Shared\Options::module($czzz_sitemap_option_intro) ?: '统一输出 HTML、XML、TXT 三类站点地图，并支持后台重建与分区访问。'),
    ],
]);

$fields = [
    [
        'type'    => 'notice',
        'style'   => 'info',
        'content' => '启用本模块后，后台模块管理卡片右上角会自动显示齿轮（配置）按钮；点击后进入本设置页。当前模块不会接管根路径 /sitemap.xml，只提供模块页与自定义页面路由。',
    ],
    [
        'id'      => $czzz_sitemap_option_route,
        'type'    => 'text',
        'title'   => '前台页面路由',
        'default' => '/sitemap',
        'desc'    => '填写后注册为独立 PC 页面路由，例如 /sitemap。不能与现有页面路由冲突。',
    ],
    [
        'id'      => $czzz_sitemap_option_title,
        'type'    => 'text',
        'title'   => '页面标题',
        'default' => '网站地图 Sitemap',
    ],
    [
        'id'      => $czzz_sitemap_option_intro,
        'type'    => 'textarea',
        'title'   => '页面说明',
        'default' => '统一生成 HTML、XML、TXT 三类站点地图文件，并提供模块内重建与分区输出能力。',
    ],
    [
        'type'    => 'notice',
        'style'   => 'info',
        'content' => '以下配置用于控制 Sitemap 纳入范围与分区策略。修改后不会由前台访问自动重建；需要管理员点击重建按钮，或由带密钥的定时任务接口执行重建后才会生效。',
    ],
    [
        'id'      => $czzz_sitemap_option_include_home,
        'type'    => 'switcher',
        'title'   => '纳入首页',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_include_search,
        'type'    => 'switcher',
        'title'   => '纳入搜索页',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_include_video_feed,
        'type'    => 'switcher',
        'title'   => '纳入视频流页面',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_include_boards,
        'type'    => 'switcher',
        'title'   => '纳入公开板块',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_include_tags,
        'type'    => 'switcher',
        'title'   => '纳入热门标签',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_include_posts,
        'type'    => 'switcher',
        'title'   => '纳入公开帖子',
        'default' => true,
    ],
    [
        'id'      => $czzz_sitemap_option_max_boards,
        'type'    => 'text',
        'title'   => '板块纳入上限',
        'default' => '200',
        'desc'    => '填写 0 表示不限制；用于控制板块分区规模。',
    ],
    [
        'id'      => $czzz_sitemap_option_max_tags,
        'type'    => 'text',
        'title'   => '标签纳入上限',
        'default' => '200',
        'desc'    => '默认读取热门标签；数值越大，构建耗时越高。',
    ],
    [
        'id'      => $czzz_sitemap_option_max_posts,
        'type'    => 'text',
        'title'   => '帖子纳入上限',
        'default' => '1000',
        'desc'    => '只纳入公开、可见、允许收录的帖子；建议按站点规模控制数量。',
    ],
    [
        'id'      => $czzz_sitemap_option_part_size,
        'type'    => 'text',
        'title'   => '单分区链接阈值',
        'default' => '200',
        'desc'    => '达到该数量后自动拆分新的 HTML/XML/TXT 分区文件，建议 50-5000 之间。',
    ],
    [
        'id'      => $czzz_sitemap_option_schedule_secret,
        'type'    => 'md5_text',
        'title'   => '定时更新密钥',
        'default' => '',
        'desc'    => '点击“生成”可一键随机生成密钥。建议使用 24 位以上且同时包含字母与数字的强密钥；保存后，带该密钥的定时更新接口可在未登录状态下自动重建 Sitemap。',
    ],
];

if ($saved_schedule_secret === '') {
    $fields[] = [
        'type'    => 'notice',
        'style'   => 'warning',
        'content' => '当前尚未配置定时更新密钥，因此自动更新接口处于禁用状态。请先生成并保存密钥后再添加计划任务。',
    ];
} elseif (!$schedule_secret_strong) {
    $fields[] = [
        'type'    => 'notice',
        'style'   => 'warning',
        'content' => '当前定时更新密钥强度不足，自动更新接口将继续拦截请求。请重新生成并保存新的强密钥后再使用。',
    ];
}

if (!empty($runtime['last_error'])) {
    $fields[] = [
        'type'    => 'notice',
        'style'   => 'warning',
        'content' => '最近一次重建失败原因：' . htmlspecialchars((string) $runtime['last_error'], ENT_QUOTES, 'UTF-8'),
    ];
}

$fields[] = [
    'type'    => 'notice',
    'style'   => 'warning',
    'content' => '将“定时更新接口”链接添加到定时访问类型的计划任务中，即可实现无需管理员登录的定时更新 Sitemap。建议妥善保管密钥，仅在可信环境中使用。',
];
$fields[] = [
    'type'    => 'notice',
    'style'   => 'success',
    'content' => $link_panel_html,
];

return [
    'id'    => $czzz_sitemap_module_key,
    'title' => '网站地图 Sitemap 设置',
    'fields' => $fields,
];
