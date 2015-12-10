<?php
/**
 * Netresearch_Scoring_Model_Mailer
 * 
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */
class Netresearch_Scoring_Model_Mailer
{
    /**
     * @var Netresearch_Scoring_Model_Config
     */
    protected $config;
    
    /**
     * constructor
     * 
     * @param Netresearch_Scoring_Model_Config $config Configuration model
     * 
     * @return Netresearch_Scoring_Model_Service
     */
    public function __construct($config=null)
    {
        $this->config = $config;
        if (is_null($this->config)) {
            $this->config = Mage::getModel('scoring/config');
        }
    }
    
    /**
     * send a mail
     * 
     * @param string $subject
     * @param string $templateId
     * @param mixed  $vars
     * 
     * @return void
     */
    public function sendMail($subject, $templateId=null, $vars=null)
    {
        $sender = array(
            'email' => $this->config->get('scoring/mail/sender_mail_address'),
            'name'  => $this->config->get('scoring/mail/sender_name')
        );

        $email = $this->config->get('scoring/mail/receiver_mail_address');
        $name = $this->config->get('scoring/mail/receiver_name');

        $translate = Mage::getSingleton('core/translate');
        $mail = Mage::getModel('core/email_template')
            ->setTemplateSubject($subject)
            ->sendTransactional($templateId, $sender, $email, $name, $vars);

        $translate->setTranslateInline(true);
    }
}