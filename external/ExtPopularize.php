<?php
/**
 * File Name: ExtPopularize.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/11 9:10 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\popularize\external;


use QTTX;
use qttx\components\db\DbModel;
use qttx\web\External;

class ExtPopularize extends External
{
    /**
     * @return string 返回 `user_popularize` 表名称
     */
    public function tableName()
    {
        return '{{%user_popularize}}';
    }

    /**
     * @return string 返回 `user_popularize_more` 表名称
     */
    public function moreTableName()
    {
        return '{{%user_popularize_more}}';
    }

    /**
     * 生成新用户的推广码
     * 默认利用一张数据表获取唯一编号.模块自带的 `tbl_init_popularize_code` 表中存了大概50W的唯一码
     * @param $db DbModel
     * @return string|int
     */
    public function generateCode($db)
    {
        $result = $db->select(['id', 'code'])
            ->from('tbl_init_popularize_code')
            ->where('is_used=0')
            ->row();
        $db->update('tbl_init_popularize_code')
            ->cols([
                'is_used' => 1,
                'used_time' => time()
            ])
            ->whereArray(['id' => $result['id']])
            ->query();

        return $result['code'];
    }
}