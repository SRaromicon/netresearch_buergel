<?php
include_once("Mage/Checkout/controllers/OnepageController.php");

/**
 * @category   Scoring
 * @package    Netresearch_Scoring
 * @author     Thomas Kappel <thomas.kappel@netresearch.de>
 * @copyright  Copyright (c) 2011 Netresearch GmbH & Co.KG <http://www.netresearch.de/>
 */

class Netresearch_Scoring_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all Terms and Conditions before placing the order.');
                    $result['failing_field'] = 'agreements';
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            Mage::dispatchEvent('netresearch_scoring_checkout_type_onepage_save_order_before', array('quote'=>$this->getOnepage()->getQuote()));
            $result['success'] = false;
            //$result['goto_section'] = 'billing';
            $result['error'] = true;
            $this->getOnepage()->saveOrder();
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Netresearch_Scoring_Model_Validation_Address_Exception $e ) {
            $result['success'] = false;
            //$result['goto_section'] = 'billing';
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
            Mage::dispatchEvent('netresearch_scoring_address_validation_failed', array('quote'=>$this->getOnepage()->getQuote()));
            $result = array_merge($result, Mage::getModel('scoring/session')->getAddressValidationResultArray());
        } catch (Netresearch_Scoring_Model_Validation_Solvency_Exception $e ) {
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
            $result['scoring_value'] = null;
            Mage::dispatchEvent('netresearch_scoring_solvency_validation_failed', array('quote'=>$this->getOnepage()->getQuote()));
            $result = array_merge($result, Mage::getModel('scoring/session')->getSolvencyValidationResultArray());
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();

            if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }

            $this->getOnepage()->getQuote()->save();
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            $result['error_messages'] = $e->getMessage();
        }
        $this->getOnepage()->getQuote()->save();

        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
