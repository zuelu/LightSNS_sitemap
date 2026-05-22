# 网站地图 Sitemap

`czzz-pc-page-sitemap` 是一个面向 LightSNS PC 端的独立页面模块，用于生成并展示站点地图。模块会按配置收集首页、搜索页、视频流、公开板块、热门标签和公开帖子，并输出 HTML、XML、TXT 三类 Sitemap 文件。

## 功能特性

- 独立 PC 页面模块，不需要修改 LightSNS 主程序代码。
- 支持自定义前台访问路由，默认路由为 `/sitemap`。
- 支持 HTML、XML、TXT 三种格式输出。
- 支持按页面、板块、标签、帖子分区生成 Sitemap。
- 支持后台管理员手动重建。
- 支持带密钥的定时任务自动重建。
- 配置变更后不会在普通前台访问中自动构建，避免访客请求触发高开销任务。
- 构建时使用锁文件防止并发重建，并在切换新 build 失败时保留旧结果。

## 模块信息

| 项目 | 内容 |
| --- | --- |
| 模块 ID | `czzz-pc-page-sitemap` |
| 模块名称 | 网站地图 Sitemap |
| 模块类型 | `page` |
| 模块平台 | `pc` |
| 当前版本 | `1.0.1` |
| Demo URI | `https://czzz.ru/sitemap` |

## 运行要求

- 已安装并可正常运行的 LightSNS 站点。
- 模块必须放置在固定目录：

```text
/path/to/lightsns/module/pc/page/czzz-pc-page-sitemap
```

- Web 服务运行用户需要对模块目录下的 `build/` 和 `runtime/` 具备写入权限。
- 需要管理员账号进入后台完成启用、配置和首次重建。

## 快速安装

1. 将模块目录放到 LightSNS 的 PC 页面模块目录下：

```text
/path/to/lightsns/module/pc/page/czzz-pc-page-sitemap
```

2. 确认模块目录结构不变，尤其不要修改模块 ID 和目录层级。

3. 确保运行时目录可写：

```bash
chmod -R u+rwX,g+rwX /path/to/lightsns/module/pc/page/czzz-pc-page-sitemap/build
chmod -R u+rwX,g+rwX /path/to/lightsns/module/pc/page/czzz-pc-page-sitemap/runtime
```

4. 登录 LightSNS 后台，进入：

```text
/admin#page=modules
```

5. 找到“网站地图 Sitemap”模块并启用。

6. 点击模块卡片右上角齿轮进入设置页，按需调整前台路由、收录范围、数量上限和定时更新密钥。

7. 保存配置后，访问前台 Sitemap 页面，并使用管理员账号点击“管理员重建”生成首次 build。

更完整的安装、配置、使用、定时任务和排障说明见 [INSTALL-USAGE.md](INSTALL-USAGE.md)。

## 访问入口

默认情况下，模块提供以下入口：

| 类型 | 地址 |
| --- | --- |
| 前台页面路由 | `https://czzz.ru/sitemap` |
| 模块直达页 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/page.php` |
| HTML Sitemap 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/html/index.html` |
| XML Sitemap 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/xml/index.xml` |
| TXT Sitemap 索引 | `https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/build/txt/index.txt` |

说明：当前版本不会接管站点根路径 `/sitemap.xml`。搜索引擎可直接提交 XML Sitemap 索引地址，或在服务器层另行配置受控转发。

## 配置项概览

| 配置 | 默认值 | 说明 |
| --- | --- | --- |
| 前台页面路由 | `/sitemap` | 注册为独立 PC 页面路由，不能与已有路由冲突。 |
| 页面标题 | `网站地图 Sitemap` | 前台页面标题和 SEO 标题。 |
| 页面说明 | 模块默认说明 | 前台页面简介和 SEO 描述。 |
| 纳入首页 | 开启 | 将站点首页加入 Sitemap。 |
| 纳入搜索页 | 开启 | 将 `/search` 加入 Sitemap。 |
| 纳入视频流页面 | 开启 | 将 `/video-feed` 加入 Sitemap。 |
| 纳入公开板块 | 开启 | 读取公开板块链接。 |
| 纳入热门标签 | 开启 | 读取热门标签链接。 |
| 纳入公开帖子 | 开启 | 读取公开、已发布、非私密帖子链接。 |
| 板块纳入上限 | `200` | 填 `0` 表示不限制。 |
| 标签纳入上限 | `200` | 默认读取热门标签。 |
| 帖子纳入上限 | `1000` | 建议按站点规模调整。 |
| 单分区链接阈值 | `200` | 达到阈值后拆分新的 HTML/XML/TXT 分区文件。 |
| 定时更新密钥 | 空 | 配置强密钥后可通过计划任务自动重建。 |

## 重建方式

### 管理员手动重建

管理员登录后访问 Sitemap 前台页面，点击“管理员重建”按钮即可生成或刷新 build。

手动重建接口为：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/api.php?action=rebuild
```

该接口要求当前登录用户具备管理员权限。

### 定时任务自动重建

在模块设置页生成并保存定时更新密钥后，可复制“定时更新接口”链接加入服务器计划任务或定时访问工具。

接口格式：

```text
https://czzz.ru/module/pc/page/czzz-pc-page-sitemap/api.php?action=cron_rebuild&salt=你的密钥
```

定时接口要求：

- 密钥不能为空。
- 密钥长度至少 24 位。
- 密钥必须同时包含字母和数字。
- 两次自动重建间隔不能小于 300 秒。

## 输出文件

构建成功后会生成：

```text
build/
  index.json
  html/
    index.html
    pages-1.html
    boards-1.html
    tags-1.html
    posts-1.html
  xml/
    index.xml
    pages-1.xml
    boards-1.xml
    tags-1.xml
    posts-1.xml
  txt/
    index.txt
    pages-1.txt
    boards-1.txt
    tags-1.txt
    posts-1.txt
```

实际分区数量会根据配置和站点数据变化。

## 安全说明

- 管理员重建必须登录且具备管理员权限。
- 定时任务重建必须携带正确强密钥。
- 前台普通访问只读取已有 build，不会自动触发重建。

## 版本更新

版本更新说明见 [RELEASES.md](RELEASES.md)。

## 许可证

本仓库当前使用 GPL-3.0 许可证，详见 `LICENSE`。
