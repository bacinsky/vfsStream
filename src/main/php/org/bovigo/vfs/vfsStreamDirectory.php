<?php
/**
 * Directory container.
 *
 * @author      Frank Kleine <mikey@bovigo.org>
 * @package     bovigo_vfs
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamAbstractContent.php';
require_once dirname(__FILE__) . '/vfsStreamContainer.php';
require_once dirname(__FILE__) . '/vfsStreamException.php';
/**
 * Directory container.
 *
 * @package     bovigo_vfs
 */
class vfsStreamDirectory extends vfsStreamAbstractContent implements vfsStreamContainer
{
    /**
     * list of directory children
     *
     * @var  array<vfsStreamContent>
     */
    protected $children = array();

    /**
     * constructor
     *
     * @param   string  $name
     * @throws  vfsStreamException
     */
    public function __construct($name)
    {
        if (strstr($name, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }
        
        $this->type = vfsStreamContent::TYPE_DIR;
        parent::__construct($name);
    }

    /**
     * returns size of directory
     *
     * The size of a directory is always 0 bytes. To calculate the summarized
     * size of all children in the directory use sizeSummarized().
     *
     * @return  int
     */
    public function size()
    {
        return 0;
    }

    /**
     * returns summarized size of directory and its children
     *
     * @return  int
     */
    public function sizeSummarized()
    {
        $size = 0;
        foreach ($this->children as $child) {
            if ($child->getType() === vfsStreamContent::TYPE_DIR) {
                $size += $child->sizeSummarized();
            } else {
                $size += $child->size();
            }
        }
        
        return $size;
    }

    /**
     * renames the content
     *
     * @param   string  $newName
     * @throws  vfsStreamException
     */
    public function rename($newName)
    {
        if (strstr($newName, '/') !== false) {
            throw new vfsStreamException('Directory name can not contain /.');
        }
        
        parent::rename($newName);
    }

    /**
     * adds child to the directory
     *
     * @param  vfsStreamContent  $child
     */
    public function addChild(vfsStreamContent $child)
    {
        $this->children[] = $child;
    }

    /**
     * removes child from the directory
     *
     * @param   string  $name
     * @return  bool
     */
    public function removeChild($name)
    {
        foreach ($this->children as $key => $child) {
            if ($child->appliesTo($name) === true) {
                unset($this->children[$key]);
                return true;
            }
        }
        
        return false;
    }

    /**
     * checks whether the container contains a child with the given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasChild($name)
    {
        return ($this->getChild($name) !== null);
    }

    /**
     * returns the child with the given name
     *
     * @param   string  $name
     * @return  vfsStreamContent
     */
    public function getChild($name)
    {
        $childName = $this->getRealChildName($name);
        foreach ($this->children as $child) {
            if ($child->getName() === $childName) {
                return $child;
            }
            
            if ($child->appliesTo($childName) === true && $child->hasChild($childName) === true) {
                return $child->getChild($childName);
            }
        }
        
        return null;
    }

    /**
     * helper method to detect the real child name
     *
     * @param   string  $name
     * @return  string
     */
    protected function getRealChildName($name)
    {
        if ($this->appliesTo($name) === true) {
            return self::getChildName($name, $this->name);
        }
        
        return $name;
    }

    /**
     * helper method to calculate the child name
     *
     * @param   string  $name
     * @return  string
     */
    protected static function getChildName($name, $ownName)
    {
        return substr($name, strlen($ownName) + 1);
    }

    /**
     * returns a list of children for this directory
     *
     * @return  array<vfsStreamContent>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * resets children pointer
     */
    public function rewind()
    {
        reset($this->children);
    }

    /**
     * returns the current child
     *
     * @return  vfsStreamContent
     */
    public function current()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child;
    }

    /**
     * returns the name of the current child
     *
     * @return  string
     */
    public function key()
    {
        $child = current($this->children);
        if (false === $child) {
            return null;
        }
        
        return $child->getName();
    }

    /**
     * iterates to next child
     */
    public function next()
    {
        next($this->children);
    }

    /**
     * checks if the current value is valid
     *
     * @return  bool
     */
    public function valid()
    {
        return (false !== current($this->children));
    }
}
?>