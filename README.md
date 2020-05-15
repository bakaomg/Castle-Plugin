# Castle 插件
---

## 注意
**本插件为 [Castle](https://github.com/ohmyga233/castle-Typecho-Theme) 主题配套插件（无法单独使用）**

## 常见问题
<details><summary>插件设置在何处？</summary><br>
插件设置位于 `控制台` → `外观` → `外观设置` → `后台/插件设置` ，设置放于此处是为了方便备份设置（可以在备份主题设置时顺带备份）
</details>

<details><summary>无法获取追番列表</summary><br>
如果 API 状态码为 `HTTP 403` 的话，即为 Auth 计算有误，一般为服务器时区问题，找到主题目录下的 `functions.php` 和插件目录下的 `Plugin.php` ，并注解掉时区设置或统一时区。
</details>

<details><summary>主题设置/追番列表缓存失败</summary><br>
请检查 `插件目录/Castle/cache` 目录有无足够的读写权限。
</details>