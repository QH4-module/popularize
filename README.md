QH4框架扩展模块-推广模块

该模块是一个多层单上级模型

该功能一般不会单独使用,所以该模块没有提供controller

使用该功能应该在用户注册的时候,初始化用户

### 方法列表

#### 初始化方法
```php
/**
 * 通过邀请码插入新的用户
 * @param string $user_id 用户id
 * @param string $code 邀请码,邀请码可以为空,表示用户是1级用户,没有上级
 * @param DbModel $db 开启了事务的数据库操作
 * @param ExtPopularize $external
 */
public static function initByCode($user_id, $code, $db, ExtPopularize $external = null)
```

```php
/**
 * 通过上级用户id插入新的用户
 * @param string $user_id 用户id
 * @param string $parent_id 上级id,上级可以为空,表示用户是1级用户,没有上级
 * @param DbModel $db 开启了事务的数据库操作
 * @param ExtPopularize $external
 */
public static function initByParent($user_id, $parent_id, $db, ExtPopularize $external = null)
```

#### 其它方法
```php
/**
 * 获取一个用户的所有直属下级
 * @param string $user_id
 * @param ExtPopularize|null $external
 * @return mixed
 */
public static function getNextChildren($user_id, ExtPopularize $external = null)
```

```php
/**
 * 获取用户的所有上级
 * @param string $user_id
 * @param ExtPopularize|null $external
 * @return array|null
 */
public static function getAllParent($user_id, ExtPopularize $external = null)
```

```php
/**
 * 返回所有的下级用户
 * 注意返回值是二维数组,包括
 * [
 *      [user_id,asc_level,desc_level],
 *      [user_id,asc_level,desc_level]
 *      ...
 * ]
 * 如果只需要用户id,可以使用 `array_column` 格式化一次
 * @param $user_id
 * @param ExtPopularize|null $external
 * @return array|null
 */
public static function getAllChildren($user_id, ExtPopularize $external = null)
```

```php
/**
 * 获取指定层数的所有下级
 * @param string $user_id
 * @param int|int[] $level 指定的层级,可以是数字或数组
 * @param ExtPopularize|null $external
 * @return array|null
 * 注意返回值是二维数组,包括
 * [
 *      [user_id,asc_level,desc_level],
 *      [user_id,asc_level,desc_level]
 *      ...
 * ]
 * 如果只需要用户id,可以使用 `array_column` 格式化一次
 */
public static function getLevelChildren($user_id, $level, ExtPopularize $external = null)
```
