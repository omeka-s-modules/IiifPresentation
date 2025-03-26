<?php
namespace IiifPresentation\ControllerPlugin;

use Omeka\Api\Representation\MediaRepresentation;
use Doctrine\DBAL\Connection;
use IiifPresentation\v2\CanvasType\Manager as CanvasTypeManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Laminas\EventManager\Event;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Methods shared by v2 and v3 controller plugins.
 */
abstract class AbstractIiifPresentation extends AbstractPlugin
{
    protected $conn;
    protected $eventManager;

    public function setConnection(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get the controller's event manager.
     *
     * @return \Laminas\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager) {
            $this->eventManager = $this->getController()->getEventManager();
        }
        return $this->eventManager;
    }

    /**
     * Trigger an event.
     */
    public function triggerEvent(string $name, array $args)
    {
        $args = $this->getEventManager()->prepareArgs($args);
        $event = new Event($name, $this->getController(), $args);
        $this->getEventManager()->triggerEvent($event);
        return $args;
    }

    /**
     * Get the image size (width and height).
     *
     * Checks cache before using a potentially expensive call to getimagesize().
     *
     * @param MediaRepresentation $media
     * @return array An array containing image size, keyed by "width" and "height"
     */
    public function getImageSize(MediaRepresentation $media)
    {
        // Check cache first.
        $sql = 'SELECT width, height FROM iiif_presentation_image_size WHERE id = ?';
        $imageSize = $this->conn->fetchAssociative($sql, [$media->id()]);
        if ($imageSize) {
            $width = $imageSize['width'];
            $height = $imageSize['height'];
        } else {
            $imageSize = @getimagesize($media->originalUrl());
            if (false === $imageSize || 0 === $imageSize[0] || 0 === $imageSize[1]) {
                return false; // Could not get width and/or height.
            }
            // Cache the size.
            $this->conn->insert('iiif_presentation_image_size', [
                'id' => $media->id(),
                'width' => $imageSize[0],
                'height' => $imageSize[1],
            ]);
            $width = $imageSize[0];
            $height = $imageSize[1];
        }
        return ['width' => $width, 'height' => $height];
    }
}
