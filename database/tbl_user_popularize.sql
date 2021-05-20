DROP TABLE IF EXISTS `tbl_user_popularize`;

CREATE TABLE IF NOT EXISTS `tbl_user_popularize`
(
    `user_id`     VARCHAR(64)  NOT NULL COMMENT '用户',
    `code`        VARCHAR(200) NOT NULL COMMENT '推广码',
    `parent_id`   VARCHAR(64)  NOT NULL COMMENT '上级',
    `parent_path` TEXT         NOT NULL COMMENT '所有上级',
    `create_time` BIGINT       NOT NULL COMMENT '创建时间',
    `del_time`    BIGINT       NOT NULL,
    PRIMARY KEY (`user_id`)
)
    ENGINE = InnoDB
    COMMENT = '用户推广';

CREATE INDEX `fk_tbl_user_popularize_tbl_user1_idx` ON `tbl_user_popularize` (`parent_id` ASC);
