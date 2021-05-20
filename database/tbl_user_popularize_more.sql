DROP TABLE IF EXISTS `tbl_user_popularize_more`;

# 用户推广的冗余表,详细记录了每个用户推广关系
# 理论最大数据量为: 用户数量*最大推广层级

CREATE TABLE IF NOT EXISTS `tbl_user_popularize_more`
(
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     VARCHAR(64)  NOT NULL COMMENT '用户',
    `parent_id`   VARCHAR(64)  NOT NULL COMMENT '上级',
    `asc_level`   INT          NOT NULL COMMENT '以父级为基准,用户层数',
    `desc_level`  INT          NOT NULL COMMENT '以用户为基准,父级层数',
    `create_time` BIGINT       NOT NULL COMMENT '',
    `del_time`    BIGINT       NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COMMENT = '用户推广-冗余表';

CREATE INDEX `fk_tbl_user_popularize_more_tbl_user1_idx` ON `tbl_user_popularize_more` (`user_id` ASC);

CREATE INDEX `fk_tbl_user_popularize_more_tbl_user2_idx` ON `tbl_user_popularize_more` (`parent_id` ASC);
