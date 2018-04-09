# 项目
喀客项目单点登录系统

## 安装步骤

```shell
$ git clone https://github.com/maiqi2016/passport.git
$ chmod a+x passport/install.sh
```

### 本机环境

```shell
$ cd passport
$ composer install
$ ./install.sh
```

### `Docker` 环境

```
$ sudo docker-compose up -d     # 并确保已经安装(执行)了 `/web/docker/script/` 目录下的所有脚本
$ mq-composer install --ignore-platform-reqs
$ mq-bash passport/install.sh
```