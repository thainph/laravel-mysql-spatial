<?php

namespace Thainph\LaravelMysqlSpatial;

use Doctrine\DBAL\Types\Type as DoctrineType;
use Thainph\LaravelMysqlSpatial\Connectors\ConnectionFactory;
use Thainph\LaravelMysqlSpatial\Doctrine\Geometry;
use Thainph\LaravelMysqlSpatial\Doctrine\GeometryCollection;
use Thainph\LaravelMysqlSpatial\Doctrine\LineString;
use Thainph\LaravelMysqlSpatial\Doctrine\MultiLineString;
use Thainph\LaravelMysqlSpatial\Doctrine\MultiPoint;
use Thainph\LaravelMysqlSpatial\Doctrine\MultiPolygon;
use Thainph\LaravelMysqlSpatial\Doctrine\Point;
use Thainph\LaravelMysqlSpatial\Doctrine\Polygon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\DatabaseServiceProvider;

/**
 * Class DatabaseServiceProvider.
 */
class SpatialServiceProvider extends DatabaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // The connection factory is used to create the actual connection instances on
        // the database. We will inject the factory into the manager so that it may
        // make the connections while they are actually needed and not of before.
        $this->app->singleton('db.factory', function ($app) {
            return new ConnectionFactory($app);
        });

        // The database manager is used to resolve various connections, since multiple
        // connections might be managed. It also implements the connection resolver
        // interface which may be used by other components requiring connections.
        $this->app->singleton('db', function ($app) {
            return new DatabaseManager($app, $app['db.factory']);
        });

        if (class_exists(DoctrineType::class)) {
            // Prevent geometry type fields from throwing a 'type not found' error when changing them
            $geometries = [
                'geometry'           => Geometry::class,
                'point'              => Point::class,
                'linestring'         => LineString::class,
                'polygon'            => Polygon::class,
                'multipoint'         => MultiPoint::class,
                'multilinestring'    => MultiLineString::class,
                'multipolygon'       => MultiPolygon::class,
                'geometrycollection' => GeometryCollection::class,
            ];
            $typeNames = array_keys(DoctrineType::getTypesMap());
            foreach ($geometries as $type => $class) {
                if (!in_array($type, $typeNames)) {
                    DoctrineType::addType($type, $class);
                }
            }
        }
    }
}
