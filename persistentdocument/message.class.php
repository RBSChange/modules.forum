<?php
/**
 * forum_persistentdocument_message
 * @package modules.forum
 */
class forum_persistentdocument_message extends forum_persistentdocument_messagebase 
{
	/**
	 * @return String
	 */
	public function getTextAsHtml()
	{
		return website_BBCodeService::getInstance()->toHtml(parent::getText());
	}
	
	/**
	 * @return String
	 */
	public function getTextAsInput()
	{
		return htmlspecialchars(parent::getText());
	}
	
	/**
	 * @retun string
	 */
	public function getSignAsHtml()
	{
		return website_BBCodeService::getInstance()->toHtml(parent::getSign());
	}	
	
	/**
	 * @return String
	 */
	public function getPseudonymAsHtml()
	{
		$forummember = $this->getForummember();
		if ($forummember !== null)
		{
			return $forummember->getPseudonymAsHtml();
		}
		return 'Anonymous';
	}
		
	/**
	 * @retun string
	 */
	public function getCreationdateAsHtml()
	{
		return date_DateFormat::format($this->getUICreationdate(), '\\l\\e d/m/Y \\à H:i:s');
	}

	/**
	 * @retun string
	 */
	public function getModificationdateAsHtml()
	{
		return date_DateFormat::format($this->getUIModificationdate(), '\\l\\e d/m/Y \\à H:i:s');
	}
	
	/**
	 * @return forum_persistentdocument_forummember
	 */
	public function getForummember()
	{
		$forummemberId = intval($this->getForummemberid());
		if ($forummemberId > 0)
		{
			return DocumentHelper::getDocumentInstance($forummemberId);
		}
		return null;
	}
	
	/**
	 * @return forum_persistentdocument_forummember
	 */
	public function getModifiedby()
	{
		$forummemberId = intval($this->getModifiedbyid());
		if ($forummemberId > 0)
		{
			return DocumentHelper::getDocumentInstance($forummemberId);
		}
		return null;
	}
	
	/**
	 * @return String
	 */
	public function getModifiedClassName()
	{
		if ($this->getForummemberid() != $this->getModifiedbyid())
		{
			return 'modified modertor';
		}
		return 'modified';
	}
}