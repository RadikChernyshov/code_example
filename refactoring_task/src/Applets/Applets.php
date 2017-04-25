<?php
/**
 *
 * Candidate Practical Homework Refactoring
 *
 * @author Rodion Chernyshov <radik.chernyshov@gmail.com>
 *
 */

namespace Applets;

use Interfaces\AppletsInterface;

/**
 * Class Applets
 * @package Language
 */
class Applets implements AppletsInterface
{
    /**
     * @var array
     */
    protected $list;

    /**
     * @inheritdoc
     */
    public function getTranslatedAppletsList()
    {
        if (0 === count($this->list)) {
            $this->list = [new Applet('memberapplet', 'JSM2_MemberApplet')];
        }
        return $this->list;
    }
}
