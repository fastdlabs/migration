# MySQL Migration

轻量，简单又好用的数据库迁移工具。支持创建、修改、数据集填充等基础功能。

### 要求

* PHP >= 5.6

### 使用

![usage](docs/2017-04-30%2014.20.26.gif)

##### 导出表结构(dump)

从数据库已有表中迁移到 PHP 文件
 
```
php migrate dump [table name] [-p|--path]
```

##### 执行迁移(run)

将PHP文件迁移到 MySQL 数据表

```
php migrate run [-p|--path]
```

##### 清除缓存(cache-clear)

```
php migrate cache-clear
```

##### 创建结构文件(create)

```
php migrate create [table]
```

##### 查看表结构(info)

```
php migrate info [table]
```

### Support

如果你在使用中遇到问题，请联系: [bboyjanhuang@gmail.com](mailto:bboyjanhuang@gmail.com). 微博: [编码侠](http://weibo.com/ecbboyjan)

## License MIT
