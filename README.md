
<p align="center">
    <img src="./docs/logo.png" width="400" alt="YAF-PHP">    
</p>

<p align="center">
    <a href="https://img.shields.io/static/v1.svg?label=TestCase&message=86/97&color=green">
        <img src="https://img.shields.io/static/v1.svg?label=TestCase&message=86/97&color=green" alt="Test Case">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=Yaf%20version&message=3.0.8-dev&color=blueviolet">
        <img src="https://img.shields.io/static/v1.svg?label=Yaf%20version&message=3.0.8-dev&color=blueviolet" alt="Yaf version">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=version&message=1.0.0&color=blue">
        <img src="https://img.shields.io/static/v1.svg?label=version&message=1.0.0&color=blue" alt="version">
    </a>
    <a href="https://img.shields.io/static/v1.svg?label=LICENSE&message=MIT&color=green">
        <img src="https://img.shields.io/static/v1.svg?label=LICENSE&message=MIT&color=green" alt="LICENSE">
    </a>
</p>
<br>

# 目录

+ [1 关于Yaf-PHP](#1-关于yaf-php)
    + [1.1 简介](#11-简介)
    + [1.2 依赖](#12-依赖)
    + [1.3 安装](#13-安装)
    + [1.4 快速使用](#14-快速使用)
        - [1.4.1 PhpStorm引入](#141-在phpstorm引入)
        - [1.4.2 NetBeans引入](#142-在netbeans引入)
    + [1.5 最终效果图](#15-最终效果图)
+ [2 相关链接](#2-相关链接)
+ [3 待解决问题](#3待解决问题)

# 1. 关于Yaf-PHP

## 1.1 简介

```Yaf-PHP```采用原生PHP对Yaf源码进行了逻辑重写，最大程度还原其相关实现逻辑.

由于采用了原生PHP重写，可达到抹平阅读能力，降低阅读成本的目的．在平常开发时，也可以作为IDE Helper进行引入，加快开发效率.

## 1.2 依赖

| 依赖项 | 版本 | 说明 |
|--------|--------|--------|
| PHP |  7.0+   |   IDE运行的PHP环境，并非服务运行环境   |

## 1.3 安装
<!-- TODO 上传composer -->

```bash
> git clone https://github.com/yinggaozhen/yaf-php.git
```

## 1.4 快速使用

### 1.4.1 在PhpStorm引入

两种实现方法

~~~
左侧导航栏 `External Libraries` -> `Configure PHP Include Path` -> 添加`Yaf-PHP`文件路径 -> `apply`
~~~

~~~
菜单 `File` -> `Setting` -> `Languages & Frameworks` -> `PHP` -> 添加`Yaf-PHP`文件路径 -> `apply`
~~~

### 1.4.2 在NetBeans引入

~~~
右击你的项目 -> `Properties` -> `PHP Include Path` -> `Add Folder..` -> 添加`Yaf-PHP`文件路径 -> `open`
~~~

## 1.5 最终效果图

<p align="center">
    <img src="./docs/yaf_php_tip.gif" width="1500" alt="IDE识别">    
</p>

<p align="center">
    <img src="./docs/yaf_php_auto.gif" width="1500" alt="IDE自动补齐">    
</p>

# 2 相关链接

- [Yaf官方文档](http://www.laruence.com/manual/)
- [Yaf源码](https://github.com/laruence/yaf) 
- [Yaf Doc](https://github.com/elad-yosifon/php-yaf-doc)

# 3.待解决问题

- [ ] 接入travis CI/codecov/lint,内容包含单元测试/测试覆盖率/代码规范校验
- [x] 项目中TODO List清理
- [x] 测试用例完善
- [x] README文档完善
- [x] 加载方式同时支持PSR4和PSR0
- [x] 代码注释完善,包括函数入参出参参数说明,以及每个函数作用说明
