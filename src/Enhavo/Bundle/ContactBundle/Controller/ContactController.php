<?php

namespace Enhavo\Bundle\ContactBundle\Controller;

use Enhavo\Bundle\AppBundle\Template\TemplateResolverTrait;
use Enhavo\Bundle\ContactBundle\Contact\ContactManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    use TemplateResolverTrait;

    public function __construct(private readonly ContactManager $contactManager)
    {

    }

    public function submitAction(Request $request, $key)
    {

    }

}
