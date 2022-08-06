<?php

namespace Be\App\Doc\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Es
{

    public function getIndexes()
    {
        $configEs = Be::getConfig('App.Doc.Es');
        if (!$configEs->enable) {
            return false;
        }

        $indexes = [];

        $es = Be::getEs();
        foreach ([
                     [
                         'name' => 'chapter',
                         'label' => '文档索引',
                         'value' => $configEs->indexChapter,
                     ],
                 ] as $index) {
            $params = [
                'index' => $index['value'],
            ];
            if ($es->indices()->exists($params)) {
                $index['exists'] = true;

                $mapping = $es->indices()->getMapping($params);
                $index['mapping'] = $mapping[$configEs->indexChapter]['mappings'] ?? [];

                $settings = $es->indices()->getSettings($params);
                $index['settings'] = $settings[$configEs->indexChapter]['settings'] ?? [];

                $count = $es->count($params);
                $index['count'] = $count['count'] ?? 0;
            } else {
                $index['exists'] = false;
            }
            $indexes[] = $index;
        }

        return $indexes;
    }

    /**
     * 创建索引
     *
     * @param string $indexName 索引名
     * @param array $options 参数
     * @return void
     */
    public function createIndex(string $indexName, array $options = [])
    {
        $number_of_shards = $options['number_of_shards'] ?? 2;
        $number_of_replicas = $options['number_of_replicas'] ?? 1;

        $configEs = Be::getConfig('App.Doc.Es');
        if ($configEs->enable) {
            $es = Be::getEs();

            $configField = 'index' . ucfirst($indexName);

            $params = [
                'index' => $configEs->$configField,
            ];

            if ($es->indices()->exists($params)) {
                throw new ServiceException('索引（' . $configEs->$configField . '）已存在');
            }

            switch ($indexName) {
                case 'chapter':
                    $mapping = [
                        'properties' => [
                            'id' => [
                                'type' => 'keyword',
                            ],
                            'project-id' => [
                                'type' => 'keyword',
                            ],
                            'title' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'description' => [
                                'type' => 'text',
                                'analyzer' => 'ik_max_word',
                            ],
                            'url' => [
                                'type' => 'keyword',
                            ],
                            'ordering' => [
                                'type' => 'integer'
                            ],
                            'hits' => [
                                'type' => 'integer'
                            ],
                            'is_enable' => [
                                'type' => 'boolean'
                            ],
                            'is_delete' => [
                                'type' => 'boolean'
                            ],
                            'create_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                            'update_time' => [
                                'type' => 'date',
                                'format' => 'yyyy-MM-dd HH:mm:ss',
                            ],
                        ]
                    ];
                    break;
            }

            $params = [
                'index' => $configEs->$configField,
                'body' => [
                    'settings' => [
                        'number_of_shards' => $number_of_shards,
                        'number_of_replicas' => $number_of_replicas
                    ],
                    'mappings' => $mapping,
                ]
            ];

            $es->indices()->create($params);
        }
    }

    /**
     * 删除索引
     *
     * @param string $indexName 索引名
     * @return void
     */
    public function deleteIndex(string $indexName)
    {
        $configEs = Be::getConfig('App.Doc.Es');
        if ($configEs->enable) {
            $es = Be::getEs();

            $configField = 'index' . ucfirst($indexName);

            $params = [
                'index' => $configEs->$configField,
            ];

            if ($es->indices()->exists($params)) {
                $es->indices()->delete($params);
            }
        }
    }

}
