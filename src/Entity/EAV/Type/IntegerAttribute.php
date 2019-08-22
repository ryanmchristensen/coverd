<?php


namespace App\Entity\EAV\Type;

use App\Entity\EAV\Attribute;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IntegerAttribute
 *
 * @ORM\Entity()
 */
class IntegerAttribute extends Attribute
{
    /**
     * @var int
     *
     * @ORM\Column(name="integer_value", type="integer", nullable=true)
     */
    private $value;

    /**
     * @param int $value
     *
     * @return Attribute
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    public function fixtureData()
    {
        return rand(0,100);
    }
}