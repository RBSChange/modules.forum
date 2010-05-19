<?php
class forum_XmlListTreeParser extends tree_parser_XmlListTreeParser
{

	/**
     * Returns the document's specific and/or overridden attributes.
     *
	 * @param f_persistentdocument_PersistentDocument $document
	 * @param XmlElement $treeNode
	 * @param f_persistentdocument_PersistentDocument $reference
	 * @return array<mixed>
	 */
	protected function getAttributes($document, $treeNode, $reference = null)
	{
		return parent::getAttributes($document, $treeNode, $reference);
	}

}