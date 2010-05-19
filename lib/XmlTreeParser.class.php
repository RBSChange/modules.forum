<?php
class forum_XmlTreeParser extends tree_parser_XmlTreeParser
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
		$data = parent::getAttributes($document, $treeNode, $reference);

		switch ($document->getDocumentModelName())
		{

			case 'modules_website/topic':
			    $data['block'] = 'modules_forum_topic';
				break;

			case 'modules_generic/rootfolder':
			    $data['block'] = '';
				break;

			default:
				break;
		}

		return $data;
	}

}