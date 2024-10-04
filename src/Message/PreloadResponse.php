<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;

use RuntimeException;

/**
 * Moneris Purchase Response
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return true;
    }

    public function getRedirectUrl()
    {
        return $this->getRequest()->getEndpoint();
    }

    public function getRedirectMethod()
    {
        return 'POST';
    }

    public function getRedirectData()
    {
        return $this->data;
    }
    
    /**
     * Slightly customised version of parent method to allow setting of Referrer-Policy with meta tag
     * @refer \Omnipay\Common\Message\AbstractResponse::getRedirectResponse()
     * @return HttpRedirectResponse
     */
    public function getRedirectResponse()
    {
        if (!$this instanceof RedirectResponseInterface || !$this->isRedirect()) {
            throw new RuntimeException('This response does not support redirection.');
        }

        if ('GET' === $this->getRedirectMethod()) {
            return HttpRedirectResponse::create($this->getRedirectUrl());
        } elseif ('POST' === $this->getRedirectMethod()) {
            $hiddenFields = '';
            foreach ($this->getRedirectData() as $key => $value) {
                $hiddenFields .= sprintf(
                    '<input type="hidden" name="%1$s" value="%2$s" />',
                    htmlentities($key, ENT_QUOTES, 'UTF-8', false),
                    htmlentities($value, ENT_QUOTES, 'UTF-8', false)
                )."\n";
            }

            $output = '<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="referrer" content="no-referrer-when-downgrade">
        <title>Redirecting...</title>
    </head>
    <body onload="document.forms[0].submit();">
        <form action="%1$s" method="post">
            <p>Redirecting to payment page...</p>
            <p>
                %2$s
                <input type="submit" value="Continue" />
            </p>
        </form>
    </body>
</html>';
            $output = sprintf(
                $output,
                htmlentities($this->getRedirectUrl(), ENT_QUOTES, 'UTF-8', false),
                $hiddenFields
            );

            return HttpResponse::create($output);
        }

        throw new RuntimeException('Invalid redirect method "'.$this->getRedirectMethod().'".');
    }
}
