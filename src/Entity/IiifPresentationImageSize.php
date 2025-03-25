<?php
namespace IiifPresentation\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class IiifPresentationImageSize extends AbstractEntity
{
    /**
     * @Id
     * @Column(
     *     type="integer",
     *     options={
     *         "unsigned"=true
     *     }
     * )
     */
    protected $id;

    /**
     * @Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $width;

    /**
     * @Column(
     *     type="bigint",
     *     nullable=true
     * )
     */
    protected $height;

    public function getId()
    {
        return $this->id;
    }

    public function setWidth()
    {
        $this->width = $width;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function setHeight()
    {
        $this->height = $height;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
