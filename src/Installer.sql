CREATE TABLE `doc_chapter` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`project_id` varchar(36) NOT NULL DEFAULT '' COMMENT '项目ID',
`parent_id` varchar(36) NOT NULL DEFAULT '' COMMENT '父ID',
`title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
`description` mediumtext NOT NULL COMMENT '描述',
`description_markdown` mediumtext NOT NULL COMMENT '描述（MD）',
`editor` varchar(30) NOT NULL DEFAULT '' COMMENT '编辑器',
`url` varchar(200) NOT NULL DEFAULT '' COMMENT '网址',
`url_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT '网址是否启用自定义',
`seo_title` varchar(200) NOT NULL DEFAULT '' COMMENT 'SEO标题',
`seo_title_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'SEO标题是否启用自定义',
`seo_description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
`seo_description_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'SEO描述是否启用自定义',
`seo_keywords` varchar(60) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
`ordering` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
`hits` int(11) NOT NULL DEFAULT '0' COMMENT '点击量	',
`is_enable` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用',
`is_delete` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='章节';

CREATE TABLE `doc_project` (
`id` varchar(36) NOT NULL DEFAULT 'uuid()' COMMENT 'UUID',
`title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
`description` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
`url` varchar(200) NOT NULL DEFAULT '' COMMENT '网址',
`url_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT '网址是否启用自定义',
`seo_title` varchar(120) NOT NULL DEFAULT '' COMMENT 'SEO标题',
`seo_title_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'SEO标题是否启用自定义',
`seo_description` varchar(500) NOT NULL DEFAULT '' COMMENT 'SEO描述',
`seo_description_custom` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'SEO描述是否启用自定义',
`seo_keywords` varchar(60) NOT NULL DEFAULT '' COMMENT 'SEO关键词',
`chapter_default_editor` varchar(30) NOT NULL DEFAULT 'markdown' COMMENT '文档默认编辑器',
`chapter_toggle_editor` tinyint(4) NOT NULL DEFAULT '1' COMMENT '文档是否可切换编辑器',
`ordering` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
`is_delete` int(11) NOT NULL DEFAULT '0' COMMENT '是否已删除',
`create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
`update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='分类';


ALTER TABLE `doc_chapter`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url` (`project_id`,`url`) USING BTREE,
ADD KEY `update_time` (`update_time`),
ADD KEY `parent_id` (`parent_id`,`project_id`) USING BTREE;

ALTER TABLE `doc_project`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `url` (`url`) USING BTREE;
