<?php

/**
 * Represents a node in the parse tree during XML parsing - 
 * each node in the tree can have child nodes of its own,
 * as well as character data
 * 
 * @author Greg J Preece <support@txttools.co.uk>
 * @copyright Copyright &copy; 2010 txttools Ltd. All rights reserved.
 * @version 2010062112
 * @since 2008091812
 */
 
class moodletxt_ParseNode {

    /**
     * The parent node of this node, if one exists
     * @var moodletxt_ParseNode
     */
    var $parentNode;

    /**
     * The tag name of this node within the XML structure
     * @var string
     */
    var $nodeName = '';

    /**
     * Associative array of attributes for this node (XML attributes for this tag)
     * @var array(string => string)
     */
    var $attributes = array();

    /**
     * Contains any character data found within this node
     * @var string
     */
    var $chardata = '';

    /**
     * Array of child nodes
     * @var array(moodletxt_ParseNode)
     */
    var $children = array();

    /**
     * Constructor - sets up the node with initial data
     * @param string $nodeName The name of the XML tag this parse node represents
     * @param array(string => string) $attributes Node attributes lifted from XML
     * @param moodletxt_ParseNode $parentNode Parent node for this node (Optional)
     * @version 2010062112
     * @since 2008091812
     */
    function moodletxt_ParseNode($nodeName, $attributes = array(), $parentNode = null) {
        
        $this->nodeName = $nodeName;
        $this->setAttributes($attributes);

        if ($parentNode !== null)
            $this->setParentNode($parentNode);
        
    }

    /**
     * Set the parent node for this node
     * @param moodletxt_ParseNode $parentNode This node's parent
     * @return boolean Whether or not the parameter was accepted
     * @version 2010062112
     * @since 2008091812
     */
    function setParentNode($parentNode) {

        if (is_object($parentNode) && get_class($parentNode) == 'moodletxt_ParseNode') {

            $this->parentNode = $parentNode;
            return true;

        } else
            return false;

    }

    /**
     * Add a child node to this node
     * @param moodletxt_ParseNode $child Child node to add
     * @version 2010062112
     * @since 2008091812
     */
    function addChild($child) {

        if (is_object($child) && get_class($child) == 'moodletxt_ParseNode') {

            $this->children[$child->getNodeName()] = $child;
            $child->setParentNode($this);

        }

    }

    /**
     * Clear any parent node set for this node
     * @version 2010062112
     * @since 2008091812
     */
    function clearParent() {

        $this->parentNode = null;

    }

    /**
     * Set this node's attributes (as lifted from XML)
     * @param array(string => string) $attributes Array of attributes and values
     * @version 2010062112
     * @since 2008091812
     */
    function setAttributes($attributes) {

        $this->attributes = array_merge($this->attributes, $attributes);

    }

    /**
     * Add character data to the node - new character data is appended until cleared
     * @param string $chardata Character data to append
     * @version 2010062112
     * @since 2008091812
     */
    function addCharData($chardata) {

        if (is_string($chardata))
            $this->chardata .= $chardata;

    }

    /**
     * Remove a list of attributes from the node by key
     * @param array(string) $attributeList An array of attribute names to remove
     * @version 2010062112
     * @since 2008091812
     */
    function removeAttributes($attributeList) {

        foreach($attributeList as $attribute)
            unset($this->attributes[$attribute]);

    }

    /**
     * Wipe all attributes set on the node
     * @version 2010062112
     * @since 2008091812
     */
    function removeAllAttributes() {

        $this->attributes = array();

    }

    /**
     * Get this node's parent node
     * @return moodletxt_ParseNode This node's parent node
     * @version 2010062112
     * @since 2008091812
     */
    function getParentNode() {

        if ($this->parentNode == null)
            return $this;
        else
            return $this->parentNode;

    }

    /**
     * Get this node's name (tag name from XML)
     * @return string The name of this node
     * @version 2010062112
     * @since 2008091812
     */
    function getNodeName() {
        
        return $this->nodeName;
        
    }

    /**
     * Returns character data contained within the node
     * @return string Character data from the node
     * @version 2010062112
     * @since 2008091812
     */
    function getCharData() {
        
        return $this->chardata;
        
    }

    /**
     * Wipe the character data stored in the node
     * @version 2010062112
     * @since 2008091812
     */
    function clearCharData() {
        
        $this->chardata = '';
        
    }

    /**
     * Checks to see if a given child node exists underneath this one
     * @param string $childName The name of the node to search for
     * @return boolean Whether or not the child node exists
     * @version 2010062112
     * @since 2008091812
     */
    function childExists($childName) {

        return (array_key_exists($childName, $this->children));

    }

    /**
     * Get all child nodes as an array
     * @return array(moodletxt_ParseNode) Child nodes
     * @version 2010062112
     * @since 2008091812
     */
    function getChildren() {
        
        return $this->children;
        
    }

    /**
     * Returns a specified child node
     * @param string $childName The name of the child node to fetch
     * @return moodletxt_ParseNode Child node
     * @version 2010062112
     * @since 2008091812
     */
    function getChild($childName) {

        if (array_key_exists($childName, $this->children))
            return $this->children[$childName];
        else
            return null;

    }

    /**
     * Clear all child nodes
     * @version 2010062112
     * @since 2008091812
     */
    function clearChildren() {

        $this->children = array();
        
    }

    /**
     * Wipe all sub-data of this node (not the parent)
     * @version 2010062112
     * @since 2008091812
     */
    function clearNode() {
        
        $this->clearCharData();
        $this->clearChildren();
        
    }
    
}

?>