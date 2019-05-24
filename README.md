
<p align="center">
    <img src="./docs/logo.png" width="400" alt="YAF-PHP">    
</p>

<p align="center">
    <a href="https://img.shields.io/static/v1.svg?label=TestCase&message=50/97&color=yellowgreen">
        <img src="https://img.shields.io/static/v1.svg?label=TestCase&message=50/97&color=yellowgreen" alt="Test Case">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=Yaf%20version&message=3.0.8-dev&color=blueviolet">
        <img src="https://img.shields.io/static/v1.svg?label=Yaf%20version&message=3.0.8-dev&color=blueviolet" alt="Yaf version">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=version&message=0.1.0-dev.1&color=important">
        <img src="https://img.shields.io/static/v1.svg?label=version&message=0.1.0-dev.1&color=important" alt="version">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=LICENSE&message=MIT&color=green">
        <img src="https://img.shields.io/static/v1.svg?label=LICENSE&message=MIT&color=green" alt="LICENSE">
    </a>
</p>
<br>

# 目录

+ [1 关于Yaf-PHP](#1-关于yaf-php)
    + [1.1 简介](#11-简介)
    + [1.2 安装](#12-安装)
    + [1.3 快速使用](#13-快速使用)
        - [1.3.1 phpstorm引入](#131-在phpstorm引入)
+ [2 相关链接](#2-相关链接)
+ [3 待解决问题](#3待解决问题)

# 1. 关于Yaf-PHP

## 1.1 简介

```Yaf-PHP```采用原生PHP对Yaf源码进行了逻辑重写，最大程度还原其相关实现逻辑.

由于采用了原生PHP重写，可达到抹平阅读能力，降低阅读成本的目的．在平常开发时，也可以作为IDE Helper进行引入，加快开发效率.

:heart::heart::heart:另外，欢迎各位同学的star和fork~~:heart::heart::heart:


## 1.2 安装
<!-- TODO 上传composer -->

```bash
> composer install YafPHP
```

## 1.3 快速使用

### 1.3.1 在phpstorm引入

~~~
打开phpstorm -> 右击`External Libraries` -> 点击`Configure PHP Include Path` -> 加入`Yaf-PHP` -> click apply -> done
~~~

# 2 相关链接

- [Yaf官方文档](http://www.laruence.com/manual/)
- [Yaf源码](https://github.com/laruence/yaf) 
- [Yaf Doc](https://github.com/elad-yosifon/php-yaf-doc)

# 3.待解决问题

- [ ] 项目中TODO List清理
- [ ] 加载方式同时支持PSR4和PSR0
- [ ] 测试用例完善
- [ ] 代码注释完善,包括函数入参出参参数说明,以及每个函数作用说明
- [ ] 接入travis CI/codecov/lint,内容包含单元测试/测试覆盖率/代码规范校验
- [ ] README文档完善
