<?php
namespace IiifPresentation;

use Laminas\Router\Http;

return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'controllers' => [
        'invokables' => [
            'IiifPresentation\Controller\Index' => Controller\IndexController::class,
            'IiifPresentation\Controller\Presentation' => Controller\PresentationController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'iiif-presentation' => [
                'type' => Http\Literal::class,
                'options' => [
                    'route' => '/iiif/presentation',
                    'defaults' => [
                        '__NAMESPACE__' => 'IiifPresentation\Controller',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'manifest' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/:item-id/manifest',
                            'defaults' => [
                                'controller' => 'presentation',
                                'action' => 'manifest',
                            ],
                            'constraints' => [
                                'item-id' => '\d+',
                            ],
                        ],
                    ],
                    'canvas' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/:item-id/canvas/:media-id',
                            'defaults' => [
                                'controller' => 'presentation',
                                'action' => 'canvas',
                            ],
                            'constraints' => [
                                'item-id' => '\d+',
                                'media-id' => '\d+',
                            ],
                        ],
                    ],
                    'annotation-page' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/:item-id/annotation-page/:media-id',
                            'defaults' => [
                                'controller' => 'presentation',
                                'action' => 'annotation-page',
                            ],
                            'constraints' => [
                                'item-id' => '\d+',
                                'media-id' => '\d+',
                            ],
                        ],
                    ],
                    'annotation' => [
                        'type' => Http\Segment::class,
                        'options' => [
                            'route' => '/:item-id/annotation/:media-id',
                            'defaults' => [
                                'controller' => 'presentation',
                                'action' => 'annotation',
                            ],
                            'constraints' => [
                                'item-id' => '\d+',
                                'media-id' => '\d+',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
