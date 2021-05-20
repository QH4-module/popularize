<?php
/**
 * File Name: HpPopularize.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/11 9:09 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\popularize;


use qh4module\popularize\external\ExtPopularize;
use QTTX;
use qttx\components\db\DbModel;
use qttx\exceptions\InvalidArgumentException;

/**
 * Class HpPopularize
 * 用户推广计算
 *
 * 关于推广层数的说明
 * 假设有推广如下  A1->B1->C1->D1->E1
 *              A1->B2->C2->D2->E2
 *              A1->B2->C3->D3->E3
 *
 * 父级为基准的用户层数: `asc_level`字段
 * 表示子级是父级的第多少层,子级所在层数越低,数字越大 则
 * user_id=E1 and parnet_id=A1  then asc_level = 4
 * user_id=D3 and parnet_id=B2  then asc_level = 2
 *
 * 子级为基准的用户层数: `desc_level` 字段
 * 表示父级是子级的第多少层,父级所在层数越高,数字越大 则
 * user_id=E1 and parnet_id=A1  then desc_level = 1
 * user_id=E3 and parnet_id=B2  then desc_level = 2
 * user_id=E2 and parnet_id=D2  then desc_level = 4
 *
 * @package qh4module\popularize
 */
class HpPopularize
{
    /**
     * 通过邀请码插入新的用户
     * @param string $user_id 用户id
     * @param string $code 邀请码,邀请码可以为空,表示用户是1级用户,没有上级
     * @param DbModel $db 开启了事务的数据库操作
     * @param ExtPopularize $external
     */
    public static function initByCode($user_id, $code, $db, ExtPopularize $external = null)
    {
        if (is_null($external)) $external = new ExtPopularize();

        if (empty($code)) {
            self::_insert($user_id, null, $db, $external);
        } else {
            $result_parent = $db->select('*')
                ->from($external->tableName())
                ->whereArray(['code' => $code])
                ->where('del_time=0')
                ->row();
            if (empty($result_parent)) {
                throw new InvalidArgumentException('邀请码无效');
            }
            self::_insert($user_id, $result_parent, $db, $external);
        }

    }

    /**
     * 通过上级用户id插入新的用户
     * @param string $user_id 用户id
     * @param string $parent_id 上级id,上级可以为空,表示用户是1级用户,没有上级
     * @param DbModel $db 开启了事务的数据库操作
     * @param ExtPopularize $external
     */
    public static function initByParent($user_id, $parent_id, $db, ExtPopularize $external = null)
    {
        if (is_null($external)) $external = new ExtPopularize();

        if (empty($parent_id)) {
            self::_insert($user_id, null, $db, $external);
        } else {
            $result_parent = $db->select('*')
                ->from($external->tableName())
                ->whereArray(['user_id' => $parent_id])
                ->where('del_time=0')
                ->row();
            if (empty($result_parent)) {
                throw new InvalidArgumentException('上级无效');
            }
            self::_insert($user_id, $result_parent, $db, $external);
        }
    }

    /**
     * 获取一个用户的所有直属下级
     * @param string $user_id
     * @param ExtPopularize|null $external
     * @return mixed
     */
    public static function getNextChildren($user_id, ExtPopularize $external = null)
    {
        if (is_null($external)) $external = new ExtPopularize();

        return $external->getDb()->select(['user_id'])
            ->from($external->tableName())
            ->whereArray(['parent_id' => $user_id])
            ->where('del_time=0')
            ->column();
    }

    /**
     * 获取用户的所有上级
     * @param string $user_id
     * @param ExtPopularize|null $external
     * @return array|null
     */
    public static function getAllParent($user_id, ExtPopularize $external = null)
    {
        if (is_null($external)) $external = new ExtPopularize();

        $result = $external->getDb()->select('*')
            ->from($external->tableName())
            ->whereArray(['user_id' => $user_id])
            ->where('del_time=0')
            ->row();
        if (empty($result)) {
            return null;
        }
        $ary_path = explode(',', $result['parent_path']);
        array_pop($ary_path);
        return $ary_path;
    }

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
    {
        if (is_null($external)) $external = new ExtPopularize();

        return $external->getDb()
            ->select(['user_id', 'asc_level', 'desc_level'])
            ->from($external->moreTableName())
            ->whereArray(['parent_id' => $user_id])
            ->where('del_time=0')
            ->query();
    }

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
    {
        if (is_null($external)) $external = new ExtPopularize();

        $sql = $external->getDb()
            ->select(['user_id', 'asc_level', 'desc_level'])
            ->from($external->moreTableName())
            ->whereArray(['parent_id' => $user_id]);
        if (is_array($level)) {
            $sql->whereIn('asc_level', $level);
        }else{
            $sql->whereArray(['asc_level' => $level]);
        }

        return $sql->where('del_time=0')
            ->query();
    }


    /**
     * @param string $user_id
     * @param array $result_parent
     * @param DbModel $db
     * @param ExtPopularize $external
     */
    protected static function _insert($user_id, $result_parent, $db, ExtPopularize $external)
    {
        $cols = [
            'user_id' => $user_id,
            'code' => $external->generateCode($db),
            'parent_id' => $result_parent ? $result_parent['user_id'] : '',
            'create_time' => time(),
            'del_time' => 0
        ];
        // 计算所有父级id, parent_path 保存的是所有父级id和自己的id,以逗号分隔
        if ($result_parent) {
            $ary_path = explode(',', $result_parent['parent_path']);
            $ary_path[] = $user_id;
            $cols['parent_path'] = implode(',', $ary_path);
        } else {
            $cols['parent_path'] = $user_id;
        }

        $db->insert($external->tableName())
            ->cols($cols)
            ->query();

        if ($result_parent) {
            $ary_path = explode(',', $result_parent['parent_path']);
            foreach ($ary_path as $index => $item) {
                $db->insert($external->moreTableName())
                    ->cols([
                        'user_id' => $user_id,
                        'parent_id' => $item,
                        'asc_level' => sizeof($ary_path) - $index,
                        'desc_level' => $index + 1,
                        'create_time' => time(),
                        'del_time' => 0
                    ])
                    ->query();
            }
        }
    }
}