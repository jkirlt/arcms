# arcms项目说明
- 本项目适合后台管理等项目。
- 本项目所有源码仅供学习交流，请不要用于商业
- 源码遵循apache开源协议
- 后台支持自定义模型菜单，支持RBAC权限管理

操作说明
- 第一次使用先进入安装页面，以后要进入安装页面手动删除文件/cfg/install.lock
- 安装过程设置数据库可以新建数据库，也可以选择已存在的数据库
- 安装过程中设置的管理员账号默认为超级管理员
- 添加菜单在系统设置/数据库表里面根据数据库生成模型，在模型里面生成通用的菜单
- 通过数据库表模型生成的菜单每个字段可手动设置名称、字段类型等信息
- 通过数据库表生成的模型每个模型只允许一个字段类型为图片或文章
- 通过数据库表模型生成的菜单支持自定义功能，具体功能在后端手动编写相关功能
- 如果有特殊需求，模型生成通用的菜单满足不了的，可以在菜单列表手动添加菜单，在后端手动定制功能
- 一级菜单需要在菜单列表手动添加
- 不同权限的用户登陆后只显示该权限的用户能访问的菜单
- 添加的新菜单后超级管理员需要给相关的用户组分配相关的权限

相关链接
- [arphp(高性能组件化PHP框架)](https://github.com/assnr/arphp)
- *arphp*开发交流群*259956472*
