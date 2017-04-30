# MySQL Migration

轻量，简单又好用的数据库迁移工具。支持创建、修改、数据集填充等基础功能。

### 要求

* PHP >= 5.6

### 使用

![usage](docs/2017-04-29%2016.26.07.gif)

##### Dump

从数据库已有表中迁移到 PHP 文件
 
```
php migrate dump [-p|--path]
```

##### Run

将PHP文件迁移到 MySQL 数据表

```
php migrate run [-p|--path]
```

##### Cache Clean

```
php migrate cache-clear
```

### Support

如果你在使用中遇到问题，请联系: [bboyjanhuang@gmail.com](mailto:bboyjanhuang@gmail.com). 微博: [编码侠](http://weibo.com/ecbboyjan)

## License MIT
