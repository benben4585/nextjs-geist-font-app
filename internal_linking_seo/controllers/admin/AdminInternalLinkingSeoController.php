<?php
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class AdminInternalLinkingSeoController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'internal_linking_rules';
        $this->className = 'InternalLinkingRule';
        $this->lang = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->fields_list = [
            'id_rule' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'keyword' => [
                'title' => $this->l('Keyword'),
                'width' => 'auto',
            ],
            'link' => [
                'title' => $this->l('Link URL'),
                'width' => 'auto',
            ],
        ];
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Add Internal Linking Rule'),
                'icon' => 'icon-link',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Keyword'),
                    'name' => 'keyword',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Link URL'),
                    'name' => 'link',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        return parent::renderForm();
    }
}
