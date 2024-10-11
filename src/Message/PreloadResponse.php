<?php
namespace Omnipay\Moneris\Message;

use Omnipay\Moneris\Helper;

use Omnipay\Moneris\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as HttpRedirectResponse;

use RuntimeException;

/**
 * Moneris Purchase Response
 */
class PreloadResponse extends AbstractResponse
{

    public function isPending()
    {
        return true;
    }

    
}
