# 版本发布

## v1.0.1

发布时间：2026-05-22

下载：[`dist/czzz-pc-page-sitemap-v1.0.1.zip`](dist/czzz-pc-page-sitemap-v1.0.1.zip)

SHA256：

```text
1c6fc4e5debeaa20ec5c31680ad9b7f5be37a85ff78515fe5488a08512c2c84c  czzz-pc-page-sitemap-v1.0.1.zip
```

说明：

- 前台路由改为 raw 独立输出，避免被主站布局嵌套或登录态缓冲清理影响。
- 移除前台页面的全局输出缓冲清理，仅保留构建文件服务接口的必要清理。
- Release 包内目录为 `czzz-pc-page-sitemap/`，仅包含插件源码、图标、文档和空的 `build/`、`runtime/` 占位目录。
- 不包含本站生成的 Sitemap build 文件、运行锁文件、主程序文件或敏感配置。


## v1.0.0

发布时间：2026-05-20

下载：[`dist/czzz-pc-page-sitemap-v1.0.0.tar.xz`](dist/czzz-pc-page-sitemap-v1.0.0.tar.xz)

SHA256：

```text
35e748b2813dbe611161932a8cd7a54450aa83e7058c9a80743e18d78720e51b  czzz-pc-page-sitemap-v1.0.0.tar.xz
```

说明：

- 包内目录为 `czzz-pc-page-sitemap/`。
- 包内包含插件完整源码、图标、空的 `build/` 与 `runtime/` 占位目录。
- 不包含本站生成的 Sitemap build 文件、运行锁文件、LightSNS 主程序文件或敏感配置。
- 安装与使用说明见 [`README.md`](README.md) 和 [`INSTALL-USAGE.md`](INSTALL-USAGE.md)。
