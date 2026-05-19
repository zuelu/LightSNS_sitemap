# 安装与使用说明

本文档说明 `czzz-pc-page-sitemap` 模块的安装、启用、配置、重建、定时任务、搜索引擎提交和常见问题处理。

## 1. 安装位置

模块必须安装到 LightSNS 的 PC 页面模块目录中：

```text
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap
```

请保持以下目录层级不变：

```text
module/
  pc/
    page/
      czzz-pc-page-sitemap/
        index.php
        api.php
        page.php
        settings.php
        sitemap_lib.php
        module.css
        logo.svg
        build/
        runtime/
```

不要把模块文件直接放到 `module/pc/page/` 下，也不要修改模块目录名。模块内接口和资源路径依赖该目录层级。

## 2. 文件权限

模块构建 Sitemap 时需要写入 `build/` 和 `runtime/`。

建议确认 Web 服务运行用户对以下目录具备读写权限：

```bash
chmod -R u+rwX,g+rwX /www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build
chmod -R u+rwX,g+rwX /www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/runtime
```

如果安装包中没有 `build/` 或 `runtime/`，可以先创建目录：

```bash
mkdir -p /www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build
mkdir -p /www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/runtime
```

## 3. 后台启用

1. 使用管理员账号登录 LightSNS 后台。
2. 进入模块管理页：

```text
/admin#page=modules
```

3. 找到“网站地图 Sitemap”模块。
4. 启用模块。
5. 点击模块卡片右上角齿轮进入“网站地图 Sitemap 设置”页。

齿轮按钮由模块目录内的 `settings.php` 提供，不需要修改 `adminx`、`src` 或其他主程序文件。

## 4. 基础配置

### 前台页面路由

默认值：

```text
/sitemap
```

保存后，前台页面可通过以下地址访问：

```text
https://czzz.ru/sitemap
```

注意事项：

- 路由必须以独立路径形式填写，例如 `/sitemap`。
- 不要填写已经被主程序或其他模块占用的路由。
- 修改路由后需要保存配置，并重新构建 Sitemap，生成结果中的链接才会同步更新。

### 页面标题与说明

页面标题默认：

```text
网站地图 Sitemap
```

页面说明默认：

```text
统一生成 HTML、XML、TXT 三类站点地图文件，并提供模块内重建与分区输出能力。
```

这两项会用于前台 Sitemap 页面展示和页面 SEO 信息。

## 5. 收录范围

模块支持以下收录开关：

| 开关 | 默认 | 说明 |
| --- | --- | --- |
| 纳入首页 | 开启 | 输出站点首页。 |
| 纳入搜索页 | 开启 | 输出 `/search`。 |
| 纳入视频流页面 | 开启 | 输出 `/video-feed`。 |
| 纳入公开板块 | 开启 | 输出公开板块链接。 |
| 纳入热门标签 | 开启 | 输出热门标签链接。 |
| 纳入公开帖子 | 开启 | 输出公开、已发布、非私密帖子链接。 |

公开帖子过滤规则：

- `post_status` 必须为 `publish`。
- `post_type` 不能为空。
- `post_power` 不能包含私密标记。
- 优先通过 LightSNS 现有帖子读取模型获取数据；必要时使用受限 SQL 作为兜底读取。

## 6. 数量上限与分区

| 配置 | 默认值 | 说明 |
| --- | --- | --- |
| 板块纳入上限 | `200` | 填 `0` 表示不限制。 |
| 标签纳入上限 | `200` | 用于限制热门标签读取数量。 |
| 帖子纳入上限 | `1000` | 用于控制公开帖子收录数量。 |
| 单分区链接阈值 | `200` | 达到阈值后自动拆分分区文件。 |

示例：如果帖子纳入 1000 条，单分区链接阈值为 200，则帖子会拆分为：

```text
posts-1
posts-2
posts-3
posts-4
posts-5
```

每个分区都会同时生成 HTML、XML、TXT 三种文件。

## 7. 首次构建

模块启用后不会因为访客访问页面而自动构建 Sitemap。首次构建建议由管理员手动完成。

步骤：

1. 管理员登录前台。
2. 访问 Sitemap 页面：

```text
https://czzz.ru/sitemap
```

3. 点击页面中的“管理员重建”。
4. 等待页面刷新。
5. 查看页面中的“当前状态”和“分区输出”。

构建成功后会生成：

```text
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/index.json
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/html/index.html
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/txt/index.txt
```

## 8. 访问生成结果

常用入口：

| 用途 | 地址 |
| --- | --- |
| 前台页面 | `https://czzz.ru/sitemap` |
| 模块直达页 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/page.php` |
| HTML 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/html/index.html` |
| XML 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml` |
| TXT 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/txt/index.txt` |

当前版本不会自动占用：

```text
https://czzz.ru/sitemap.xml
```

如果搜索引擎平台要求提交 XML Sitemap，直接提交 XML 索引地址即可：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml
```

## 9. 定时任务自动重建

### 配置密钥

进入模块设置页，找到“定时更新密钥”，点击生成并保存。

密钥要求：

- 至少 24 位。
- 同时包含字母和数字。
- 不要公开到前台页面、公开仓库或第三方不可信平台。

保存强密钥后，设置页会显示可复制的定时更新接口。

### 接口格式

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild&salt=你的密钥
```

也兼容 `key` 参数：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild&key=你的密钥
```

### Linux crontab 示例

每 30 分钟触发一次：

```cron
*/30 * * * * curl -fsS "https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild&salt=你的密钥" >/dev/null
```

### 宝塔计划任务示例

1. 进入宝塔面板“计划任务”。
2. 任务类型选择“访问 URL”。
3. 执行周期按站点规模设置，例如每 30 分钟或每 1 小时。
4. URL 填写模块设置页复制的定时更新接口。
5. 保存后手动执行一次，确认返回成功。

### 自动重建限制

模块内置最短触发间隔：

```text
300 秒
```

如果定时任务触发过于频繁，接口会返回 429，并带有等待提示。

## 10. 接口说明

### 状态摘要

```text
GET /module/pc/page/czzz-pc-page-sitemap/api.php
```

返回当前 build 摘要、运行状态和当前用户是否为管理员。

### 管理员重建

```text
POST /module/pc/page/czzz-pc-page-sitemap/api.php?action=rebuild
```

要求：

- 当前用户已登录。
- 当前用户具备管理员权限。

### 定时任务重建

```text
GET  /module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild&salt=你的密钥
POST /module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild
```

要求：

- 已配置强密钥。
- 请求携带正确 `salt` 或 `key`。
- 距离上次定时重建至少 300 秒。

### 读取指定格式文件

```text
GET /module/pc/page/czzz-pc-page-sitemap/api.php?action=html&name=index
GET /module/pc/page/czzz-pc-page-sitemap/api.php?action=xml&name=index
GET /module/pc/page/czzz-pc-page-sitemap/api.php?action=txt&name=index
```

`name` 只允许小写字母、数字和连字符，例如：

```text
pages-1
boards-1
tags-1
posts-1
```

## 11. 搜索引擎提交建议

推荐提交 XML 索引：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml
```

该索引会引用各个 XML 分区文件，例如：

```text
pages-1.xml
boards-1.xml
tags-1.xml
posts-1.xml
```

如果站点后续确实需要 `/sitemap.xml` 这种根路径入口，应作为服务器转发或主程序路由变更单独评估。当前模块安装流程不要求修改主程序或全局路由。

## 12. 运行状态说明

前台页面和设置页会显示运行状态：

| 状态 | 含义 |
| --- | --- |
| 当前 build 已就绪 | 已生成 build，且配置与当前设置一致。 |
| 当前 build 仍可访问，但配置已变更，等待重建 | 旧 build 可继续访问，但需要重建才会应用新配置。 |
| 当前尚未生成 build | 尚未生成 Sitemap，需要管理员手动重建或等待定时任务。 |

同时会显示：

- 最近成功时间。
- 最近失败时间。
- 最近重建来源。
- 最近定时任务时间。
- 当前 build 是否存在。
- 配置是否一致。
- 最近一次失败原因。

## 13. 常见问题

### 前台页面提示尚未生成 Sitemap

原因：模块启用后还没有执行首次构建。

处理：

1. 使用管理员账号访问 `/sitemap`。
2. 点击“管理员重建”。
3. 或配置定时任务密钥后执行一次自动重建接口。

### 保存配置后页面内容没有变化

原因：配置保存后不会自动刷新 build。

处理：

1. 回到 Sitemap 前台页面。
2. 点击“管理员重建”。
3. 或等待下一次定时任务重建。

### 定时任务返回 403

可能原因：

- 未配置定时更新密钥。
- 密钥为空。
- 密钥强度不足。
- URL 中的 `salt` 或 `key` 参数不正确。

处理：

1. 进入模块设置页重新生成强密钥。
2. 保存配置。
3. 重新复制定时更新接口。
4. 更新计划任务中的 URL。

### 定时任务返回 429

原因：距离上次自动重建不足 300 秒。

处理：

- 降低计划任务频率。
- 等待返回提示中的秒数后再执行。

### 重建提示正在构建中

原因：已有构建任务正在运行，锁文件仍被占用。

处理：

- 等待当前构建完成后再试。
- 如果确认没有构建任务仍在运行，可检查 `runtime/build.lock` 文件及 PHP 进程状态。

### 生成文件无法访问

可能原因：

- `build/` 不存在。
- Web 服务用户没有写入权限。
- 构建失败后没有生成对应文件。
- 站点服务器禁止访问模块目录下的静态文件。

处理：

1. 检查模块页面中的最近失败原因。
2. 检查 `build/` 目录是否存在。
3. 检查文件权限。
4. 重新执行管理员重建。

### 搜索引擎要求 sitemap.xml

当前模块不会自动接管 `/sitemap.xml`。可先直接提交：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml
```

如必须使用 `/sitemap.xml`，建议单独评估服务器转发或主程序路由方案，避免在模块安装过程中越界修改主程序。

## 14. 卸载与回滚

如需停用模块：

1. 进入后台模块管理页。
2. 停用“网站地图 Sitemap”模块。
3. 删除或停用外部计划任务。
4. 如不再需要生成结果，可删除模块目录下的 `build/`。
5. 如不再需要运行记录，可删除模块目录下的 `runtime/`。

如需完全移除模块，确认已停用模块和计划任务后，再删除：

```text
/www/wwwroot/czzz.ru/module/pc/page/czzz-pc-page-sitemap
```

## 15. 边界说明

当前模块只在自身目录内工作：

- 不修改 LightSNS 主程序入口。
- 不修改后台控制器。
- 不修改公共 JS 或公共 CSS。
- 不修改主业务表结构。
- 不接管支付、权限、状态机、Worker、Outbox、Projector 等高风险链路。
- 不在普通前台请求中夹带结构变更或自动构建。

如果未来要支持根路径 `/sitemap.xml`、自动写入 `robots.txt`、接入全局后台菜单或扩展主程序路由，应作为独立主程序变更任务评估。
